<?php

namespace Database\Seeders;

use Database\Seeders\Static\AccessRoles\EmployeeRolePermissionSeeder;
use Database\Seeders\Static\AccessRoles\ModuleSeeder;
use Database\Seeders\Static\AccessRoles\PermissionSeeder;
use Database\Seeders\Static\BalanceLimitSeeder;
use Database\Seeders\Static\ChannelProviderSeeder;
use Database\Seeders\Static\EmployeeRoleSeeder;
use Database\Seeders\Static\Merchant\SalaryTypeSeeder;
use Database\Seeders\Static\Merchants\MerchantCategorySeeder;
use Database\Seeders\Static\Notifications\NotificationModuleSeeder;
use Database\Seeders\Static\Products\Orders\OrderCancelReasonSeeder;
use Database\Seeders\Static\Products\Orders\PaymentOptionSeeder;
use Database\Seeders\Static\Products\Orders\ReturnCancelReasonSeeder;
use Database\Seeders\Static\Products\Orders\ReturnOrderStatusSeeder;
use Database\Seeders\Static\Products\Orders\ReturnReasonSeeder;
use Database\Seeders\Static\Products\Orders\ReturnRejectionReasonSeeder;
use Database\Seeders\Static\Products\Orders\ShippingStatusSeeder;
use Database\Seeders\Static\Products\ProductCategorySeeder;
use Database\Seeders\Static\Products\ProductConditionSeeder;
use Database\Seeders\Static\RoleSeeder;
use Database\Seeders\Static\Services\BookingStatusSeeder;
use Database\Seeders\Static\Services\ServiceCategorySeeder;
use Database\Seeders\Static\SystemServicesSeeder;
use Database\Seeders\Static\Transactions\TransactionChannelSeeder;
use Database\Seeders\Static\Transactions\TransactionDisputeReasonSeeder;
use Database\Seeders\Static\Transactions\TransactionLimitSeeder;
use Database\Seeders\Static\Transactions\TransactionProviderSeeder;
use Database\Seeders\Static\Transactions\TransactionStatusSeeder;
use Database\Seeders\Static\Transactions\TransactionTypeSeeder;
use Illuminate\Database\Seeder;

class StaticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,

            SystemServicesSeeder::class,
            CashInFacilitiesSeeder::class,

            BalanceLimitSeeder::class,

            PermissionSeeder::class,
            ModuleSeeder::class,
            EmployeeRoleSeeder::class,
            EmployeeRolePermissionSeeder::class,

            TransactionStatusSeeder::class,
            TransactionProviderSeeder::class,
            TransactionTypeSeeder::class,
            TransactionLimitSeeder::class,
            TransactionChannelSeeder::class,
            ChannelProviderSeeder::class,

            MerchantCategorySeeder::class,

            SalaryTypeSeeder::class,

            ServiceCategorySeeder::class,
            BookingStatusSeeder::class,

            ProductCategorySeeder::class,
            ProductConditionSeeder::class,

            ShippingStatusSeeder::class,
            PaymentOptionSeeder::class,

            TransactionDisputeReasonSeeder::class,

            ReturnOrderStatusSeeder::class,
            ReturnReasonSeeder::class,
            ReturnRejectionReasonSeeder::class,
            ReturnCancelReasonSeeder::class,

            NotificationModuleSeeder::class,

            OrderCancelReasonSeeder::class,

            ShippingOptionSeeder::class,
        ]);
    }
}
