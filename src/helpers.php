<?php

use Calema\MultiTenancy\Models\Tenant;
use Calema\MultiTenancy\Services\TenantManager;

if (!function_exists('tenant')) {
    /**
     * 現在のテナントを取得
     */
    function tenant(): ?Tenant
    {
        return app(TenantManager::class)->current();
    }
}

if (!function_exists('tenant_id')) {
    /**
     * 現在のテナントIDを取得
     */
    function tenant_id(): ?int
    {
        return app(TenantManager::class)->id();
    }
}

if (!function_exists('is_tenant')) {
    /**
     * 指定のテナントかチェック
     */
    function is_tenant(int $tenantId): bool
    {
        return tenant_id() === $tenantId;
    }
}

if (!function_exists('tenant_check')) {
    /**
     * テナントが設定されているかチェック
     */
    function tenant_check(): bool
    {
        return app(TenantManager::class)->check();
    }
}

if (!function_exists('tenant_switch')) {
    /**
     * 一時的にテナントを切り替えて処理を実行
     */
    function tenant_switch(?int $tenantId, callable $callback)
    {
        return app(TenantManager::class)->switch($tenantId, $callback);
    }
}

if (!function_exists('without_tenant')) {
    /**
     * テナントスコープなしで処理を実行
     */
    function without_tenant(callable $callback)
    {
        return app(TenantManager::class)->withoutTenant($callback);
    }
}
