<?php

namespace Calema\MultiTenancy\Traits;

use Calema\MultiTenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    /**
     * Boot the trait
     */
    protected static function bootBelongsToTenant(): void
    {
        // 作成時に自動的にtenant_idを設定
        static::creating(function (Model $model) {
            if (!$model->tenant_id && tenant()) {
                $model->tenant_id = tenant()->id;
            }
        });

        // 常にtenant_idでフィルタ（Global Scope）
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (tenant()) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', tenant()->id);
            }
        });
    }

    /**
     * テナントとのリレーション
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * 特定のテナントに所属しているか
     */
    public function belongsToTenant(int $tenantId): bool
    {
        return $this->tenant_id === $tenantId;
    }

    /**
     * 現在のテナントに所属しているか
     */
    public function belongsToCurrentTenant(): bool
    {
        return tenant() && $this->tenant_id === tenant()->id;
    }
}
