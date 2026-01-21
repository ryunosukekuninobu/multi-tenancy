<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>テナント編集: {{ $tenant->name }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <div>
                        <nav class="text-sm mb-2">
                            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">
                                ダッシュボード
                            </a>
                            @if(Auth::user()->hasRole('system_admin'))
                                <span class="text-gray-400 mx-2">/</span>
                                <a href="{{ route('tenants.index') }}" class="text-blue-600 hover:text-blue-800">
                                    全テナント管理
                                </a>
                            @endif
                            <span class="text-gray-400 mx-2">/</span>
                            <a href="{{ route('tenants.show', $tenant) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $tenant->name }}
                            </a>
                            <span class="text-gray-400 mx-2">/</span>
                            <span class="text-gray-500">編集</span>
                        </nav>
                        <h1 class="text-3xl font-bold text-gray-900">
                            テナント編集
                        </h1>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-gray-800">
                            ログアウト
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main>
            <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
                <!-- Error Messages -->
                @if($errors->any())
                    <div class="mb-6 rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">入力エラーがあります</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Edit Form -->
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <form method="POST" action="{{ route('tenants.update', $tenant) }}" class="space-y-6 p-8">
                        @csrf
                        @method('PUT')

                        <!-- Tenant Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                テナント名 <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    value="{{ old('name', $tenant->name) }}"
                                    required
                                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-300 @enderror"
                                >
                            </div>
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Domain (read-only) -->
                        <div>
                            <label for="domain" class="block text-sm font-medium text-gray-700">
                                ドメイン識別子
                            </label>
                            <div class="mt-1">
                                <input
                                    type="text"
                                    name="domain"
                                    id="domain"
                                    value="{{ $tenant->domain }}"
                                    disabled
                                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-500 sm:text-sm cursor-not-allowed"
                                >
                            </div>
                            <p class="mt-2 text-sm text-gray-500">ドメイン識別子は変更できません</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                ステータス
                            </label>
                            <div class="space-y-3">
                                <div class="relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input
                                            id="status_active"
                                            name="status"
                                            type="radio"
                                            value="active"
                                            {{ old('status', $tenant->status) === 'active' ? 'checked' : '' }}
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                                        >
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="status_active" class="font-medium text-gray-700">
                                            アクティブ
                                        </label>
                                        <p class="text-gray-500">テナントは通常通り動作します</p>
                                    </div>
                                </div>

                                <div class="relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input
                                            id="status_suspended"
                                            name="status"
                                            type="radio"
                                            value="suspended"
                                            {{ old('status', $tenant->status) === 'suspended' ? 'checked' : '' }}
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                                        >
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="status_suspended" class="font-medium text-gray-700">
                                            停止中
                                        </label>
                                        <p class="text-gray-500">テナントの利用が一時停止されます</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Settings -->
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">テナント設定</h3>

                            @php
                                $settings = old('settings', $tenant->settings ?? []);
                            @endphp

                            <!-- Makeup Deadline Days -->
                            <div class="mb-4">
                                <label for="makeup_deadline_days" class="block text-sm font-medium text-gray-700">
                                    振替期限（日数）
                                </label>
                                <div class="mt-1">
                                    <input
                                        type="number"
                                        name="settings[makeup_deadline_days]"
                                        id="makeup_deadline_days"
                                        value="{{ $settings['makeup_deadline_days'] ?? 30 }}"
                                        min="1"
                                        max="365"
                                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                </div>
                                <p class="mt-1 text-sm text-gray-500">レッスン振替可能な期限（日数）</p>
                            </div>

                            <!-- Cancellation Hours -->
                            <div class="mb-4">
                                <label for="cancellation_hours" class="block text-sm font-medium text-gray-700">
                                    キャンセル猶予時間
                                </label>
                                <div class="mt-1">
                                    <input
                                        type="number"
                                        name="settings[cancellation_hours]"
                                        id="cancellation_hours"
                                        value="{{ $settings['cancellation_hours'] ?? 24 }}"
                                        min="1"
                                        max="168"
                                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                </div>
                                <p class="mt-1 text-sm text-gray-500">レッスン何時間前までキャンセル可能か</p>
                            </div>

                            <!-- Max Reservations -->
                            <div class="mb-4">
                                <label for="max_reservations_per_student" class="block text-sm font-medium text-gray-700">
                                    最大予約数（生徒1人あたり）
                                </label>
                                <div class="mt-1">
                                    <input
                                        type="number"
                                        name="settings[max_reservations_per_student]"
                                        id="max_reservations_per_student"
                                        value="{{ $settings['max_reservations_per_student'] ?? 10 }}"
                                        min="1"
                                        max="50"
                                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                </div>
                                <p class="mt-1 text-sm text-gray-500">生徒が同時に予約できる最大数</p>
                            </div>

                            <!-- Timezone -->
                            <div class="mb-4">
                                <label for="timezone" class="block text-sm font-medium text-gray-700">
                                    タイムゾーン
                                </label>
                                <div class="mt-1">
                                    <select
                                        name="settings[timezone]"
                                        id="timezone"
                                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="Asia/Tokyo" {{ ($settings['timezone'] ?? 'Asia/Tokyo') === 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo (日本)</option>
                                        <option value="America/New_York" {{ ($settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' }}>America/New_York (米国東部)</option>
                                        <option value="America/Los_Angeles" {{ ($settings['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles (米国西部)</option>
                                        <option value="Europe/London" {{ ($settings['timezone'] ?? '') === 'Europe/London' ? 'selected' : '' }}>Europe/London (英国)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <a href="{{ route('tenants.show', $tenant) }}" class="text-sm font-medium text-gray-600 hover:text-gray-500">
                                キャンセル
                            </a>

                            <button
                                type="submit"
                                class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                変更を保存
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
