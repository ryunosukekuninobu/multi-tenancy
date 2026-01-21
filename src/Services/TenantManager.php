<?php

namespace Calema\MultiTenancy\Services;

use Calema\MultiTenancy\Models\Tenant;
use Illuminate\Support\Facades\Session;

class TenantManager
{
    protected ?Tenant $currentTenant = null;
    protected bool $initialized = false;

    /**
     * 現在のテナントを取得
     */
    public function current(): ?Tenant
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->currentTenant;
    }

    /**
     * 現在のテナントを設定
     */
    public function setCurrent(?Tenant $tenant): void
    {
        $this->currentTenant = $tenant;
        $this->initialized = true;

        if ($tenant) {
            Session::put('tenant_id', $tenant->id);
        } else {
            Session::forget('tenant_id');
        }
    }

    /**
     * テナントIDから設定
     */
    public function setCurrentById(?int $tenantId): void
    {
        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            $this->setCurrent($tenant);
        } else {
            $this->setCurrent(null);
        }
    }

    /**
     * 初期化
     */
    protected function initialize(): void
    {
        $this->initialized = true;

        // セッションから取得
        $tenantId = Session::get('tenant_id');

        if ($tenantId) {
            $this->currentTenant = Tenant::find($tenantId);
        }
    }

    /**
     * テナントが設定されているか
     */
    public function check(): bool
    {
        return $this->current() !== null;
    }

    /**
     * テナントIDを取得
     */
    public function id(): ?int
    {
        return $this->current()?->id;
    }

    /**
     * 一時的にテナントを切り替えて処理を実行
     */
    public function switch(?int $tenantId, callable $callback)
    {
        $originalTenant = $this->current();

        try {
            $this->setCurrentById($tenantId);
            return $callback();
        } finally {
            $this->setCurrent($originalTenant);
        }
    }

    /**
     * テナントスコープなしで処理を実行
     */
    public function withoutTenant(callable $callback)
    {
        return $this->switch(null, $callback);
    }
}
