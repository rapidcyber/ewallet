<?php

namespace Database\Seeders\Static\AccessRoles;

use App\Models\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'Merchant - Financial Transactions - Dashboard',
            'Merchant - Financial Transactions - Cash Inflow',
            'Merchant - Financial Transactions - Cash Outflow',
            'Merchant - Financial Transactions - Invoices',
            'Merchant - Financial Transactions - Bills',
            'Merchant - Financial Transactions - Employees',
            'Merchant - Financial Transactions - Payroll',
            'Merchant - Seller Center - Dashboard',
            'Merchant - Seller Center - Store Management',
            'Merchant - Seller Center - Products',
            'Merchant - Seller Center - Services',
            'Merchant - Seller Center - Orders',
            'Merchant - Seller Center - Return Orders',
            'Merchant - Seller Center - Warehouse',
            'Merchant - Seller Center - Disputes',
            'Merchant - Account Settings',
        ];

        foreach ($modules as $module) {
            Module::firstOrCreate([
                'name' => $module
            ], [
                'slug' => str($module)->slug('_')
            ]);
        }
    }
}
