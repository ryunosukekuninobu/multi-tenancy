<?php

namespace Calema\MultiTenancy\Http\Controllers;

use Calema\MultiTenancy\Models\Tenant;
use Calema\MultiTenancy\Services\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    protected TenantManager $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    /**
     * Display a listing of all tenants (admin only)
     */
    public function index()
    {
        // スーパーアドミンのみアクセス可能
        if (!Auth::check() || !Auth::user()->hasRole('system_admin')) {
            abort(403, 'Unauthorized access.');
        }

        $tenants = Tenant::orderBy('created_at', 'desc')->paginate(20);

        return view('multi-tenancy::tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant
     */
    public function create()
    {
        return view('multi-tenancy::tenants.create');
    }

    /**
     * Store a newly created tenant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants,domain|alpha_dash',
            'email' => 'required_if:auth_method,email|nullable|email|max:255|unique:users,email',
            'password' => 'required_if:auth_method,email|nullable|string|min:8|confirmed',
            'auth_method' => 'required|in:sso,email',
            'settings' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            // テナント作成
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'domain' => Str::slug($validated['domain']),
                'settings' => $validated['settings'] ?? config('multi-tenancy.default_settings', []),
                'status' => 'active',
            ]);

            // 認証方法によって処理を分岐
            if ($validated['auth_method'] === 'email' && $validated['email']) {
                // メールアドレス・パスワード認証の場合
                $userModel = config('multi-tenancy.tenant_model', \App\Models\User::class);
                $userModel = str_replace('\\Models\\Tenant', '\\Models\\User', $userModel);

                // 新規ユーザー作成
                $user = $userModel::create([
                    'name' => $validated['name'] . ' Admin',
                    'email' => $validated['email'],
                    'password' => bcrypt($validated['password']),
                    'tenant_id' => $tenant->id,
                    'is_active' => true,
                ]);

                // 管理者ロールを付与
                $user->assignRole('company_admin');

                // ユーザーにログイン
                Auth::login($user);
            } elseif ($validated['auth_method'] === 'sso') {
                // SSO認証の場合、現在のユーザーにテナントを紐付け
                if (Auth::check()) {
                    $user = Auth::user();
                    $user->tenant_id = $tenant->id;
                    $user->save();

                    // 管理者ロールを付与
                    if (!$user->hasRole('company_admin')) {
                        $user->assignRole('company_admin');
                    }
                }
            }

            // テナントコンテキストを設定
            $this->tenantManager->setCurrent($tenant);

            // セッションにテナントIDを保存
            session()->put('tenant_id', $tenant->id);

            DB::commit();

            return redirect()->route('tenant.dashboard')
                ->with('success', "テナント「{$tenant->name}」を作成しました。");

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Tenant creation failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'テナントの作成に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified tenant
     */
    public function show(Tenant $tenant)
    {
        // 自分のテナント、または管理者のみ閲覧可能
        if (!$this->canAccessTenant($tenant)) {
            abort(403, 'Unauthorized access.');
        }

        return view('multi-tenancy::tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified tenant
     */
    public function edit(Tenant $tenant)
    {
        $user = Auth::user();

        // 生徒はテナント設定にアクセス不可
        if ($user->hasRole('student')) {
            abort(403, '生徒ユーザはテナント設定にアクセスできません。');
        }

        // 自分のテナント、または管理者のみ編集可能
        if (!$this->canAccessTenant($tenant)) {
            abort(403, 'Unauthorized access.');
        }

        return view('multi-tenancy::tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified tenant
     */
    public function update(Request $request, Tenant $tenant)
    {
        $user = Auth::user();

        // 生徒はテナント設定にアクセス不可
        if ($user->hasRole('student')) {
            abort(403, '生徒ユーザはテナント設定にアクセスできません。');
        }

        // 自分のテナント、または管理者のみ更新可能
        if (!$this->canAccessTenant($tenant)) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'settings' => 'nullable|array',
            'status' => 'nullable|in:active,suspended',
        ]);

        $tenant->update($validated);

        return redirect()->route('tenants.show', $tenant)
            ->with('success', 'テナント情報を更新しました。');
    }

    /**
     * Switch to a different tenant
     */
    public function switch(Request $request, Tenant $tenant)
    {
        // 管理者のみテナント切り替え可能
        if (!Auth::check() || !Auth::user()->hasRole('system_admin')) {
            abort(403, 'Unauthorized access.');
        }

        $this->tenantManager->setCurrent($tenant);
        session()->put('tenant_id', $tenant->id);

        return redirect()->route('tenant.dashboard')
            ->with('success', "テナント「{$tenant->name}」に切り替えました。");
    }

    /**
     * Tenant dashboard
     */
    public function dashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $currentTenant = tenant();

        if (!$currentTenant) {
            // テナントが設定されていない場合、テナント作成画面へ
            return redirect()->route('tenants.create')
                ->with('info', 'テナントを作成してください。');
        }

        return view('multi-tenancy::tenants.dashboard', [
            'tenant' => $currentTenant,
        ]);
    }

    /**
     * Check if the current user can access the tenant
     */
    protected function canAccessTenant(Tenant $tenant): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // システム管理者は全テナントにアクセス可能
        if ($user->hasRole('system_admin')) {
            return true;
        }

        // 自分のテナントにはアクセス可能
        return $user->tenant_id === $tenant->id;
    }
}
