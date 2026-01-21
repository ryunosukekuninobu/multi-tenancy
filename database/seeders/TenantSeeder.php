<?php

namespace Calema\MultiTenancy\Database\Seeders;

use Calema\MultiTenancy\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // デフォルトテナント（開発用）
        Tenant::create([
            'name' => 'Default Tenant',
            'domain' => 'default',
            'settings' => [
                'makeup_deadline_days' => 30,
                'cancellation_hours' => 24,
                'max_reservations_per_student' => 10,
                'timezone' => 'Asia/Tokyo',
                'locale' => 'ja',
                'features' => [
                    'makeup_enabled' => true,
                    'payment_enabled' => true,
                    'sso_enabled' => true,
                ],
            ],
            'status' => 'active',
        ]);

        // サンプルテナント1: 英会話スクール
        Tenant::create([
            'name' => 'A英会話スクール',
            'domain' => 'a-eikaiwa',
            'settings' => [
                'makeup_deadline_days' => 30,
                'cancellation_hours' => 24,
                'max_reservations_per_student' => 5,
                'brand_color' => '#3490dc',
                'timezone' => 'Asia/Tokyo',
                'locale' => 'ja',
                'features' => [
                    'makeup_enabled' => true,
                    'payment_enabled' => true,
                    'sso_enabled' => true,
                ],
            ],
            'status' => 'active',
        ]);

        // サンプルテナント2: ダンススタジオ
        Tenant::create([
            'name' => 'Bダンススタジオ',
            'domain' => 'b-dance',
            'settings' => [
                'makeup_deadline_days' => 14,
                'cancellation_hours' => 48,
                'max_reservations_per_student' => 8,
                'brand_color' => '#e3342f',
                'timezone' => 'Asia/Tokyo',
                'locale' => 'ja',
                'features' => [
                    'makeup_enabled' => true,
                    'payment_enabled' => true,
                    'sso_enabled' => false,
                ],
            ],
            'status' => 'active',
        ]);

        $this->command->info('✅ Created ' . Tenant::count() . ' tenants');
    }
}
