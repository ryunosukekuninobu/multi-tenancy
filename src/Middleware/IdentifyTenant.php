<?php

namespace Calema\MultiTenancy\Middleware;

use Calema\MultiTenancy\Models\Tenant;
use Calema\MultiTenancy\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;

class IdentifyTenant
{
    public function __construct(
        protected TenantManager $tenantManager
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $tenant = $this->identifyTenant($request);

        if ($tenant) {
            $this->tenantManager->setCurrent($tenant);
        }

        return $next($request);
    }

    /**
     * リクエストからテナントを識別
     */
    protected function identifyTenant(Request $request): ?Tenant
    {
        $method = config('multi-tenancy.identification.method', 'session');

        return match ($method) {
            'subdomain' => $this->identifyBySubdomain($request),
            'path' => $this->identifyByPath($request),
            'session' => $this->identifyBySession($request),
            'user' => $this->identifyByUser($request),
            default => null,
        };
    }

    /**
     * サブドメインから識別
     */
    protected function identifyBySubdomain(Request $request): ?Tenant
    {
        $host = $request->getHost();
        $domain = config('multi-tenancy.identification.domain');

        // サブドメインを抽出
        $subdomain = str_replace('.' . $domain, '', $host);

        if ($subdomain === $host || $subdomain === 'www') {
            return null;
        }

        return Tenant::where('domain', $subdomain)
            ->where('status', 'active')
            ->first();
    }

    /**
     * パスから識別
     */
    protected function identifyByPath(Request $request): ?Tenant
    {
        $segments = $request->segments();

        if (isset($segments[0]) && $segments[0] === 'tenants' && isset($segments[1])) {
            return Tenant::where('domain', $segments[1])
                ->where('status', 'active')
                ->first();
        }

        return null;
    }

    /**
     * セッションから識別
     */
    protected function identifyBySession(Request $request): ?Tenant
    {
        $tenantId = $request->session()->get('tenant_id');

        if ($tenantId) {
            return Tenant::where('id', $tenantId)
                ->where('status', 'active')
                ->first();
        }

        return null;
    }

    /**
     * ログインユーザーから識別
     */
    protected function identifyByUser(Request $request): ?Tenant
    {
        $user = $request->user();

        if ($user && isset($user->tenant_id)) {
            return Tenant::where('id', $user->tenant_id)
                ->where('status', 'active')
                ->first();
        }

        return null;
    }
}
