<?php

namespace Calema\MultiTenancy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'settings',
        'status',
    ];

    protected $casts = [
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * テナントがアクティブかどうか
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * テナントを有効化
     */
    public function activate(): bool
    {
        return $this->update(['status' => 'active']);
    }

    /**
     * テナントを無効化
     */
    public function suspend(): bool
    {
        return $this->update(['status' => 'suspended']);
    }

    /**
     * 設定値を取得
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * 設定値を更新
     */
    public function updateSetting(string $key, $value): bool
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);

        return $this->update(['settings' => $settings]);
    }

    /**
     * このテナントのユーザー
     */
    public function users()
    {
        return $this->hasMany(config('auth.providers.users.model'))->where('tenant_id', $this->id);
    }
}
