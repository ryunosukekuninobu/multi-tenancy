<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>テナントダッシュボード - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                {{ $tenant->name }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-500">
                                ドメイン: <code class="bg-gray-100 px-2 py-1 rounded">{{ $tenant->domain }}</code>
                            </p>
                        </div>

                        @php
                            $userTenants = Auth::user()->tenants;
                            $hasMultipleTenants = $userTenants->count() > 1;
                        @endphp

                        @if($hasMultipleTenants)
                            <!-- Tenant Switcher Dropdown -->
                            <div class="relative ml-4" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium transition-colors border border-gray-300 hover:border-purple-400">
                                    🔄 教室を切り替え
                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition class="absolute left-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50" style="display: none;">
                                    <div class="px-4 py-2 border-b border-gray-200">
                                        <p class="text-xs font-semibold text-gray-500 uppercase">教室を切り替え</p>
                                    </div>
                                    @foreach($userTenants as $tenant)
                                        <form method="POST" action="{{ route('tenant.switch') }}" class="px-2 py-1">
                                            @csrf
                                            <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                                            <button type="submit" class="w-full text-left px-3 py-2 text-sm rounded-md transition-colors {{ $tenant->id === Auth::user()->getCurrentTenantId() ? 'bg-purple-100 text-purple-700 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">
                                                <div class="flex items-center justify-between">
                                                    <span>🏫 {{ $tenant->name }}</span>
                                                    @if($tenant->id === Auth::user()->getCurrentTenantId())
                                                        <span class="text-purple-600">✓</span>
                                                    @endif
                                                </div>
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center space-x-4">
                        @if(Auth::user()->hasRole('system_admin'))
                            <a href="{{ route('tenants.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                                全テナント管理
                            </a>
                        @endif

                        <!-- User Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.away="open = false" class="flex items-center space-x-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
                                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
                                 style="display: none;">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500 mt-1 break-all">{{ Auth::user()->email }}</p>
                                    @if(Auth::user()->getRoleNames()->isNotEmpty())
                                        <p class="text-xs text-blue-600 mt-1">
                                            🏷️ {{ Auth::user()->getRoleNames()->first() }}
                                        </p>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        🚪 ログアウト
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <!-- Success/Info Messages -->
                @if(session('success'))
                    <div class="mb-6 rounded-md bg-green-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-6 rounded-md bg-blue-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Quick Actions -->
                <div class="px-4 py-6 sm:px-0">
                    <div class="mb-6 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-lg shadow-lg overflow-hidden">
                        <div class="px-6 py-8">
                            <h2 class="text-2xl font-bold text-white mb-4">クイックアクション</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                @if(Route::has('class.notes.index'))
                                <a href="{{ route('class.notes.index') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 transition-all rounded-lg p-4 text-white relative">
                                    <div class="flex items-center">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="ml-3 font-medium">ノート管理</span>
                                        @if(isset($unreadNotesCount) && $unreadNotesCount > 0)
                                            <span class="ml-2 inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 rounded-full animate-pulse">
                                                {{ $unreadNotesCount }}
                                            </span>
                                        @endif
                                    </div>
                                </a>
                                @endif
                                <a href="{{ route('tenants.show', $tenant) }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 transition-all rounded-lg p-4 text-white">
                                    <div class="flex items-center">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span class="ml-3 font-medium">教室詳細</span>
                                    </div>
                                </a>
                                <a href="{{ route('tenants.edit', $tenant) }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 transition-all rounded-lg p-4 text-white">
                                    <div class="flex items-center">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="ml-3 font-medium">設定変更</span>
                                    </div>
                                </a>
                                <a href="#" class="bg-white bg-opacity-20 hover:bg-opacity-30 transition-all rounded-lg p-4 text-white">
                                    <div class="flex items-center">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <span class="ml-3 font-medium">ユーザー管理</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Unread Notes Card -->
                        @if(isset($unreadNotesCount))
                        <div class="bg-white overflow-hidden shadow rounded-lg border-2 {{ $unreadNotesCount > 0 ? 'border-red-400' : 'border-gray-200' }}">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 {{ $unreadNotesCount > 0 ? 'text-red-400 animate-pulse' : 'text-gray-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                先生からの未読コメント
                                            </dt>
                                            <dd class="text-lg font-medium {{ $unreadNotesCount > 0 ? 'text-red-600' : 'text-gray-900' }}">
                                                @if($unreadNotesCount > 0)
                                                    <span class="inline-flex items-center">
                                                        🔴 {{ $unreadNotesCount }} 件
                                                    </span>
                                                @else
                                                    なし
                                                @endif
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-5 py-3">
                                <div class="text-sm">
                                    <a href="{{ route('class.notes.index') }}" class="font-medium {{ $unreadNotesCount > 0 ? 'text-red-600 hover:text-red-900' : 'text-blue-600 hover:text-blue-900' }}">
                                        @if($unreadNotesCount > 0)
                                            📝 未読を確認する
                                        @else
                                            📝 ノート一覧を見る
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Tenant Info Card -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                テナント名
                                            </dt>
                                            <dd class="text-lg font-medium text-gray-900">
                                                {{ $tenant->name }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-5 py-3">
                                <div class="text-sm flex gap-4">
                                    <a href="{{ route('tenants.show', $tenant) }}" class="font-medium text-blue-600 hover:text-blue-900">
                                        詳細を見る
                                    </a>
                                    <a href="{{ route('tenants.edit', $tenant) }}" class="font-medium text-indigo-600 hover:text-indigo-900">
                                        設定を編集
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Users Card -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                ユーザー数
                                            </dt>
                                            <dd class="text-lg font-medium text-gray-900">
                                                {{ \App\Models\User::count() }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-5 py-3">
                                <div class="text-sm">
                                    <a href="#" class="font-medium text-blue-600 hover:text-blue-900">
                                        ユーザー管理
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Status Card -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                ステータス
                                            </dt>
                                            <dd class="text-lg font-medium text-gray-900">
                                                @if($tenant->status === 'active')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        アクティブ
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        停止中
                                                    </span>
                                                @endif
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-5 py-3">
                                <div class="text-sm">
                                    <span class="font-medium text-gray-500">
                                        作成日: {{ $tenant->created_at->format('Y年m月d日') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Overview -->
                    <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                テナント設定
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                テナントの詳細設定と情報
                            </p>
                        </div>
                        <div class="border-t border-gray-200">
                            <dl>
                                @php
                                    $settings = $tenant->settings ?? [];
                                @endphp

                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">
                                        振替期限
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ $settings['makeup_deadline_days'] ?? 30 }} 日
                                    </dd>
                                </div>

                                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">
                                        キャンセル猶予時間
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ $settings['cancellation_hours'] ?? 24 }} 時間前
                                    </dd>
                                </div>

                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">
                                        最大予約数
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        生徒1人あたり {{ $settings['max_reservations_per_student'] ?? 10 }} 件
                                    </dd>
                                </div>

                                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">
                                        タイムゾーン
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ $settings['timezone'] ?? 'Asia/Tokyo' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
