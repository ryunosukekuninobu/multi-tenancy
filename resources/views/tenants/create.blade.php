<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>テナント作成 - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <a href="{{ url('/') }}" class="inline-block bg-white rounded-full p-4 shadow-lg mb-4 hover:shadow-xl transition-shadow">
                    <span class="text-6xl">🏫</span>
                </a>
                <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ config('app.name') }}</h1>
                <p class="text-gray-600">新しいテナントを作成</p>
            </div>

            <!-- Create Form -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 px-8 py-6">
                    <h2 class="text-2xl font-bold text-white">テナント作成</h2>
                    <p class="text-blue-100 text-sm mt-1">組織・スクール用の独立した環境を作成</p>
                </div>

                <div class="p-8">
                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                            <p class="text-sm font-medium text-red-700 mb-2">入力エラーがあります</p>
                            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- SSO Create Button (Primary) -->
                    <div class="mb-6">
                        <form method="POST" action="{{ route('tenants.store') }}">
                            @csrf
                            <input type="hidden" name="auth_method" value="sso">
                            <input type="hidden" name="name" value="" id="sso_name">
                            <input type="hidden" name="domain" value="" id="sso_domain">

                            <button type="button" onclick="showSSOForm()" class="w-full flex items-center justify-center gap-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-4 px-6 rounded-lg font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                <span>CALEMA SSO でテナント作成</span>
                            </button>
                        </form>
                        <p class="text-xs text-gray-500 text-center mt-3">
                            Keycloak SSOでログイン後、テナントを作成します
                        </p>
                    </div>

                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-gray-500">またはメールアドレスとパスワードで作成</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('tenants.store') }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="auth_method" value="email">

                        <!-- Tenant Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                テナント名 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name') }}"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="例: A英会話スクール"
                            >
                        </div>

                        <!-- Domain -->
                        <div>
                            <label for="domain" class="block text-sm font-medium text-gray-700 mb-2">
                                ドメイン識別子 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="domain"
                                id="domain"
                                value="{{ old('domain') }}"
                                required
                                pattern="[a-z0-9-]+"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="例: a-eikaiwa"
                            >
                            <p class="mt-2 text-xs text-gray-500">英数字とハイフンのみ使用可能</p>
                        </div>

                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                管理者メールアドレス <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email') }}"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="admin@example.com"
                            >
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                パスワード <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                minlength="8"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="8文字以上のパスワード"
                            >
                            <p class="mt-2 text-xs text-gray-500">8文字以上で設定してください</p>
                        </div>

                        <!-- Password Confirmation -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                パスワード（確認） <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                required
                                minlength="8"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="パスワードを再入力"
                            >
                        </div>

                        <button type="submit" class="w-full bg-white text-blue-600 border-2 border-blue-600 py-3 px-6 rounded-lg font-semibold hover:bg-blue-50 transition-all">
                            メールアドレスでテナント作成
                        </button>

                        <div class="text-center mt-6">
                            <p class="text-sm text-gray-600">
                                既にアカウントをお持ちの方は
                                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                    ログイン
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mt-6 bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-900">テナントとは？</h3>
                        <div class="mt-2 text-sm text-gray-600 space-y-2">
                            <p>テナントは、組織・スクール専用の独立した環境です。</p>
                            <ul class="list-disc list-inside space-y-1 ml-2">
                                <li>データは完全に分離され、他のテナントからは見えません</li>
                                <li>独自の設定やカスタマイズが可能です</li>
                                <li>生徒情報、予約、売上などすべてテナント別に管理されます</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back to Home -->
            <div class="mt-6 text-center">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>トップページに戻る</span>
                </a>
            </div>
        </div>
    </div>

    <!-- SSO Form Modal -->
    <div id="ssoModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-4">テナント情報を入力</h3>
            <p class="text-sm text-gray-600 mb-6">SSOログイン前にテナント情報を入力してください</p>

            <div class="space-y-4">
                <div>
                    <label for="modal_name" class="block text-sm font-medium text-gray-700 mb-2">
                        テナント名 <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="modal_name"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="例: A英会話スクール"
                    >
                </div>

                <div>
                    <label for="modal_domain" class="block text-sm font-medium text-gray-700 mb-2">
                        ドメイン識別子 <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="modal_domain"
                        required
                        pattern="[a-z0-9-]+"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="例: a-eikaiwa"
                    >
                    <p class="mt-2 text-xs text-gray-500">英数字とハイフンのみ使用可能</p>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button onclick="closeSSOModal()" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-all">
                    キャンセル
                </button>
                <button onclick="submitSSOForm()" class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all">
                    SSOでログイン
                </button>
            </div>
        </div>
    </div>

    <script>
        function showSSOForm() {
            document.getElementById('ssoModal').classList.remove('hidden');
        }

        function closeSSOModal() {
            document.getElementById('ssoModal').classList.add('hidden');
        }

        function submitSSOForm() {
            const name = document.getElementById('modal_name').value;
            const domain = document.getElementById('modal_domain').value;

            if (!name || !domain) {
                alert('テナント名とドメイン識別子を入力してください');
                return;
            }

            // 英数字とハイフンのみチェック
            if (!/^[a-z0-9-]+$/.test(domain)) {
                alert('ドメイン識別子は英数字とハイフンのみ使用可能です');
                return;
            }

            // セッションストレージに保存
            sessionStorage.setItem('tenant_name', name);
            sessionStorage.setItem('tenant_domain', domain);

            // SSOログインへリダイレクト（テナント作成フラグ付き）
            window.location.href = '{{ route('keycloak.redirect') }}?tenant_create=1&name=' + encodeURIComponent(name) + '&domain=' + encodeURIComponent(domain);
        }

        // モーダル外クリックで閉じる
        document.getElementById('ssoModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeSSOModal();
            }
        });
    </script>
</body>
</html>
