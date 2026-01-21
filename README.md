# Calema Multi-Tenancy Module

1つのアプリケーション・データベースで複数の組織（テナント）のデータを安全に分離管理するモジュールです。

## 機能

- **自動データ分離**: tenant_idによる自動フィルタリング
- **テナント識別**: サブドメイン/パス/セッション/ユーザーから識別
- **セキュリティ保護**: 他テナントのデータへのアクセスをブロック
- **テナント管理**: テナントの作成・更新・有効化/無効化
- **テナント固有設定**: テナントごとのカスタマイズ設定
- **テナント切り替え**: 管理者用の一時的なテナント切り替え

## インストール

### 1. Composer でインストール

```bash
composer require calema/multi-tenancy
```

### 2. 設定ファイルを公開

```bash
php artisan vendor:publish --tag=multi-tenancy-config
php artisan vendor:publish --tag=multi-tenancy-migrations
```

### 3. マイグレーション実行

```bash
php artisan migrate
```

### 4. シーダー実行（サンプルテナント作成）

```bash
php artisan db:seed --class=Calema\\MultiTenancy\\Database\\Seeders\\TenantSeeder
```

### 5. 環境変数を設定

`.env` ファイルに以下を追加:

```env
TENANCY_IDENTIFICATION_METHOD=session
APP_DOMAIN=calema.jp
```

## 使用方法

### 基本的な使い方

#### 1. Modelにトレイトを追加

```php
use Calema\MultiTenancy\Traits\BelongsToTenant;

class Student extends Model
{
    use BelongsToTenant;  // この1行を追加
}
```

これだけで以下が自動化されます：
- 作成時にtenant_idが自動設定
- 取得時にtenant_idで自動フィルタ
- 他テナントのデータへのアクセスをブロック

#### 2. テナント情報を取得

```php
// 現在のテナント
$tenant = tenant();

// テナント情報
$tenant->name;       // "A英会話スクール"
$tenant->domain;     // "a-eikaiwa"
$tenant->settings;   // テナント固有の設定

// テナントID
$tenantId = tenant_id();

// テナントチェック
if (tenant_check()) {
    // テナントが設定されている
}
```

### テナント識別方法

#### 方法A: サブドメイン（推奨）

```env
TENANCY_IDENTIFICATION_METHOD=subdomain
APP_DOMAIN=calema.jp
```

```
https://a-eikaiwa.calema.jp    → テナント: A英会話スクール
https://b-dance.calema.jp      → テナント: Bダンススタジオ
```

#### 方法B: パス

```env
TENANCY_IDENTIFICATION_METHOD=path
```

```
https://calema.jp/tenants/a-eikaiwa
https://calema.jp/tenants/b-dance
```

#### 方法C: セッション（デフォルト）

```env
TENANCY_IDENTIFICATION_METHOD=session
```

ログイン後にセッションからテナントを識別。

#### 方法D: ユーザー

```env
TENANCY_IDENTIFICATION_METHOD=user
```

ログインユーザーの`tenant_id`から識別。

### Middleware登録

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ...
        \Calema\MultiTenancy\Middleware\IdentifyTenant::class,
    ],
];
```

または特定のルートグループのみ:

```php
Route::middleware(['tenant'])->group(function () {
    // このグループ内でテナント識別
});
```

### テナント切り替え（管理者用）

```php
// 一時的にテナントを切り替えて処理
tenant_switch(2, function() {
    // テナントID=2のデータにアクセス
    $students = Student::all();
});

// またはTenantManagerを使用
use Calema\MultiTenancy\Services\TenantManager;

$tenantManager = app(TenantManager::class);
$tenantManager->switch(2, function() {
    // テナントID=2のデータにアクセス
});
```

### テナントスコープなしで処理

```php
// 全テナントのデータを取得（スーパーアドミン用）
without_tenant(function() {
    $allStudents = Student::all();  // 全テナントの生徒
});
```

### テナント設定の取得・更新

```php
// 設定値を取得
$makeupDeadline = tenant()->getSetting('makeup_deadline_days', 30);

// 設定値を更新
tenant()->updateSetting('brand_color', '#ff6b6b');
```

## データベース設計

### テナントテーブル

```sql
CREATE TABLE tenants (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),           -- テナント名
    domain VARCHAR(255) UNIQUE,  -- ドメイン/識別子
    settings JSON,               -- テナント固有設定
    status VARCHAR(50),          -- active/suspended
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 既存テーブルへの追加

すべてのテーブルに`tenant_id`カラムを追加:

```sql
ALTER TABLE students ADD COLUMN tenant_id BIGINT;
ALTER TABLE reservations ADD COLUMN tenant_id BIGINT;
-- etc...
```

マイグレーション例:

```php
Schema::table('students', function (Blueprint $table) {
    $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
    $table->index('tenant_id');
});
```

## ヘルパー関数

| 関数 | 説明 | 例 |
|------|------|---|
| `tenant()` | 現在のテナントを取得 | `tenant()->name` |
| `tenant_id()` | 現在のテナントIDを取得 | `tenant_id()` |
| `tenant_check()` | テナントが設定されているか | `if (tenant_check()) {}` |
| `is_tenant($id)` | 指定のテナントか判定 | `is_tenant(1)` |
| `tenant_switch($id, $callback)` | 一時的にテナント切り替え | `tenant_switch(2, fn() => ...)` |
| `without_tenant($callback)` | テナントスコープなしで実行 | `without_tenant(fn() => ...)` |

## 設定

`config/multi-tenancy.php` で以下をカスタマイズ可能:

```php
return [
    // テナント識別方法
    'identification' => [
        'method' => 'session',  // subdomain/path/session/user
        'domain' => 'calema.jp',
    ],

    // テナントモデル
    'tenant_model' => \Calema\MultiTenancy\Models\Tenant::class,

    // 自動スコープを適用しないテーブル
    'except_tables' => [
        'tenants',
        'migrations',
        // ...
    ],

    // スーパーアドミン
    'super_admin_emails' => [
        'admin@calema.com',
    ],

    // デフォルトテナント設定
    'default_settings' => [
        'makeup_deadline_days' => 30,
        'cancellation_hours' => 24,
        // ...
    ],
];
```

## 実例

### シナリオ: 生徒管理

```php
// A英会話スクールでログイン中

// 生徒作成（tenant_idは自動設定）
$student = Student::create([
    'name' => '田中太郎',
    'email' => 'tanaka@example.com',
]);
// → tenant_id = 1 が自動設定

// 生徒一覧（A英会話の生徒のみ）
$students = Student::all();
// → WHERE tenant_id = 1 が自動追加

// 別テナントの生徒にはアクセス不可
$otherStudent = Student::find(999);  // Bダンスの生徒ID
// → null（取得できない）
```

### シナリオ: システム管理者

```php
// 全テナント一覧
$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    // 各テナントのダッシュボードデータを取得
    tenant_switch($tenant->id, function() use ($tenant) {
        echo "{$tenant->name}: ";
        echo Student::count() . "名の生徒\n";
    });
}
```

## トラブルシューティング

### tenant_idが自動設定されない

Modelに`BelongsToTenant`トレイトを追加してください:

```php
use Calema\MultiTenancy\Traits\BelongsToTenant;

class YourModel extends Model
{
    use BelongsToTenant;
}
```

### 他テナントのデータが見える

Middlewareが登録されているか確認してください:

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        \Calema\MultiTenancy\Middleware\IdentifyTenant::class,
    ],
];
```

### テナントが識別されない

環境変数と設定ファイルを確認:

```bash
# .env
TENANCY_IDENTIFICATION_METHOD=session

# config/multi-tenancy.php を確認
php artisan config:clear
```

## 依存関係

- `yourcompany/calema-sso`: 認証でユーザーのテナントを識別

## ライセンス

Proprietary

## サポート

問題が発生した場合は、プロジェクトのIssuesセクションで報告してください。
