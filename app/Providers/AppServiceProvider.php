<?php

namespace App\Providers;

use App\Models\Merchant;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use URL;

class AppServiceProvider extends ServiceProvider
{
    private $employeeData = [];
    private $modules;

    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment(['production', 'beta', 'alpha'])) {
            URL::forceScheme(scheme: 'https');
        }

        Model::shouldBeStrict(!app()->isProduction());

        Passport::tokensExpireIn(now()->addDays(30));
        Passport::refreshTokensExpireIn(now()->addDays(45));
        Passport::personalAccessTokensExpireIn(now()->addDays(7));

        Passport::tokensCan([
            'auth-pin' => 'Repay app pin authentication api route',
            'repay-app' => 'Scope for repay mobile app access.',
            'qr-generate' => 'Scope for generating QR.',
        ]);

        $this->defineGates();
    }
    
    private function defineGates()
    {
        // Define all permissions with a map for Gates and corresponding module names
        $permissions = [
            'merchant-ft-dashboard' => 'Merchant - Financial Transactions - Dashboard',
            'merchant-cash-inflow' => 'Merchant - Financial Transactions - Cash Inflow',
            'merchant-cash-outflow' => 'Merchant - Financial Transactions - Cash Outflow',
            'merchant-invoices' => 'Merchant - Financial Transactions - Invoices',
            'merchant-bills' => 'Merchant - Financial Transactions - Bills',
            'merchant-employees' => 'Merchant - Financial Transactions - Employees',
            'merchant-payroll' => 'Merchant - Financial Transactions - Payroll',
            'merchant-sc-dashboard' => 'Merchant - Seller Center - Dashboard',
            'merchant-store-management' => 'Merchant - Seller Center - Store Management',
            'merchant-products' => 'Merchant - Seller Center - Products',
            'merchant-services' => 'Merchant - Seller Center - Services',
            'merchant-orders' => 'Merchant - Seller Center - Orders',
            'merchant-return-orders' => 'Merchant - Seller Center - Return Orders',
            'merchant-warehouse' => 'Merchant - Seller Center - Warehouse',
            'merchant-disputes' => 'Merchant - Seller Center - Disputes',
        ];
    
        foreach ($permissions as $gate => $module_name) {
            Gate::define($gate, function (User $user, Merchant $merchant, $action) use ($module_name) {
                $employee_data = $this->get_employee_data($user, $merchant);
                if (!$employee_data) {
                    return false;
                }
                $module = $this->get_module_by_name($module_name);
                return $module ? $employee_data->role->hasPermission($module->id, $action) : false;
            });
        }
    }
    
    private function get_employee_data(User $user, Merchant $merchant)
    {
        $key = $merchant->id . '-' . $user->id;

        // Check if employee data is already loaded for this request
        if (isset($this->employeeData[$key])) {
            return $this->employeeData[$key];
        }

        // Retrieve and cache data if not loaded already
        $this->employeeData[$key] = Cache::remember("merchant-employee-data-$key", 3600, function () use ($user, $merchant) {
            return $merchant->employees()->where('user_id', $user->id)->with('role')->first();
        });

        return $this->employeeData[$key];
    }
    
    private function get_module_by_name($name)
    {
        // Load all modules once per request
        $modules = $this->get_modules();
        return $modules->get($name);  // Retrieve by name from cached collection
    }

    private function get_modules()
    {
        if ($this->modules !== null) {
            return $this->modules;
        }

        // Cache modules for the first request and reuse for subsequent calls
        $this->modules = Cache::remember('merchant-modules', 3600, function () {
            return Module::select('id', 'name')->get()->keyBy('name');
        });

        return $this->modules;
    }
}
