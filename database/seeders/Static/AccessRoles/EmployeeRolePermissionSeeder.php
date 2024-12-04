<?php

namespace Database\Seeders\Static\AccessRoles;

use App\Models\EmployeeRole;
use App\Models\EmployeeRolePermission;
use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeRolePermissionSeeder extends Seeder
{
    public $permissions;
    public $employee_roles;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (EmployeeRolePermission::all() as $permission) {
            $permission->delete();
        }

        $modules = Module::get();
        $this->permissions = Permission::toBase()->get();
        $this->employee_roles = EmployeeRole::toBase()->get();

        $ft_dashboard = $modules->where('name', 'Merchant - Financial Transactions - Dashboard')->firstOrFail();
        $this->ft_dashboard($ft_dashboard);

        $cash_inflow = $modules->where('name', 'Merchant - Financial Transactions - Cash Inflow')->firstOrFail();
        $this->ft_cash_inflow($cash_inflow);

        $cash_outflow = $modules->where('name', 'Merchant - Financial Transactions - Cash Outflow')->firstOrFail();
        $this->ft_cash_outflow($cash_outflow);

        $invoices = $modules->where('name', 'Merchant - Financial Transactions - Invoices')->firstOrFail();
        $this->ft_invoices($invoices);

        $bills = $modules->where('name', 'Merchant - Financial Transactions - Bills')->firstOrFail();
        $this->ft_bills($bills);

        $employees = $modules->where('name', 'Merchant - Financial Transactions - Employees')->firstOrFail();
        $this->ft_employees($employees);

        $payroll = $modules->where('name', 'Merchant - Financial Transactions - Payroll')->firstOrFail();
        $this->ft_payroll($payroll);

        $sc_dashboard = $modules->where('name', 'Merchant - Seller Center - Dashboard')->firstOrFail();
        $this->sc_dashboard($sc_dashboard);

        $sc_store_management = $modules->where('name', 'Merchant - Seller Center - Store Management')->firstOrFail();
        $this->sc_store_management($sc_store_management);

        $sc_products = $modules->where('name', 'Merchant - Seller Center - Products')->firstOrFail();
        $this->sc_products($sc_products);

        $sc_services = $modules->where('name', 'Merchant - Seller Center - Services')->firstOrFail();
        $this->sc_services($sc_services);

        $sc_orders = $modules->where('name', 'Merchant - Seller Center - Orders')->firstOrFail();
        $this->sc_orders($sc_orders);

        $sc_return_orders = $modules->where('name', 'Merchant - Seller Center - Return Orders')->firstOrFail();
        $this->sc_return_orders($sc_return_orders);

        $sc_warehouse = $modules->where('name', 'Merchant - Seller Center - Warehouse')->firstOrFail();
        $this->sc_warehouse($sc_warehouse);

        $sc_disputes = $modules->where('name', 'Merchant - Seller Center - Disputes')->firstOrFail();
        $this->sc_disputes($sc_disputes);

        $account_settings = $modules->where('name', 'Merchant - Account Settings')->firstOrFail();
        $this->account_settings($account_settings);
    }

    private function ft_dashboard(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
            'update'
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
            'admin',
            'human_resource',
            'accounting',
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }
    }

    private function ft_cash_inflow(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
            'admin',
            'accounting',
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }
    }

    private function ft_cash_outflow(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
            'create',
            'approve'
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
            'admin',
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }

        $permissions = $this->permissions->whereIn('action', [
            'view',
            'create',
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'accounting',
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }
    }

    private function ft_invoices(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
            'create',
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
            'admin',
            'accounting',
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }
    }

    private function ft_bills(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
            'create',
            'approve',
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
            'admin',
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }

        $permissions = $this->permissions->whereIn('action', [
            'view',
            'create',
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'accounting',
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }
    }

    private function ft_employees(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
            'create',
            'update',
            'delete'
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
            'admin',
            'human_resource',
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }
    }

    private function ft_payroll(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
            'create',
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
            'admin',
            'human_resource',
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }
    }

    private function sc_dashboard(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
            'admin',
            'accounting',
            'employee'
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }
    }

    private function sc_store_management(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
            'update',
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
            'admin',
            'accounting',
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }

        $permissions = $this->permissions->whereIn('action', [
            'view',
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'employee'
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }
    }

    private function sc_products(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
            'create',
            'update',
            'delete'
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
            'admin',
            'accounting',
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }

        $permissions = $this->permissions->whereIn('action', [
            'view',
            'create',
            'update',
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'employee'
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }
    }

    private function sc_services(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
            'create',
            'update',
            'delete'
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
            'admin',
            'accounting',
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }

        $permissions = $this->permissions->whereIn('action', [
            'view',
            'create',
            'update',
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'employee'
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }
    }

    private function sc_orders(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
            'update',
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
            'admin',
            'accounting',
            'employee'
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }
    }

    private function sc_return_orders(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
            'update',
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
            'admin',
            'accounting',
            'employee'
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }
    }

    private function sc_warehouse(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
            'create',
            'update',
            'delete'
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
            'admin',
            'accounting',
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }

        // $permissions = $this->permissions->whereIn('action', [
        //     'view',
        // ]);

        // $employee_roles = $this->employee_roles->whereIn('slug', [
        //     'employee'
        // ]);

        // foreach ($permissions as $permission) {
        //     foreach ($employee_roles as $employee_role) {
        //         EmployeeRolePermission::firstOrCreate([
        //             'employee_role_id' => $employee_role->id,
        //             'permission_id' => $permission->id,
        //             'module_id' => $module->id,
        //         ]);
        //     }
        // }
    }

    private function sc_disputes(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
            'create',
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
            'admin',
            'accounting',
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }

        // $permissions = $this->permissions->whereIn('action', [
        //     'view',
        // ]);

        // $employee_roles = $this->employee_roles->whereIn('slug', [
        //     'employee'
        // ]);

        // foreach ($permissions as $permission) {
        //     foreach ($employee_roles as $employee_role) {
        //         EmployeeRolePermission::firstOrCreate([
        //             'employee_role_id' => $employee_role->id,
        //             'permission_id' => $permission->id,
        //             'module_id' => $module->id,
        //         ]);
        //     }
        // }
    }

    private function account_settings(Module $module)
    {
        $permissions = $this->permissions->whereIn('action', [
            'view',
        ]);

        $employee_roles = $this->employee_roles->whereIn('slug', [
            'owner',
        ]);

        foreach ($permissions as $permission) {
            foreach ($employee_roles as $employee_role) {
                EmployeeRolePermission::firstOrCreate([
                    'employee_role_id' => $employee_role->id,
                    'permission_id' => $permission->id,
                    'module_id' => $module->id,
                ]);
            }
        }
    }
}
