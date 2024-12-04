<?php

namespace App\Jobs;

use App\Models\BookingStatus;
use App\Models\EmployeeRole;
use App\Models\MerchantCategory;
use App\Models\ProductCategory;
use App\Models\ProductCondition;
use App\Models\ReturnOrderStatus;
use App\Models\ReturnReason;
use App\Models\Role;
use App\Models\SalaryType;
use App\Models\ServiceCategory;
use App\Models\ShippingStatus;
use App\Models\TransactionDisputeReason;
use App\Models\TransactionType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;

class UpdateSlugsToSnakecase implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->run(Role::getModel()); //
        $this->run(EmployeeRole::getModel()); //
        $this->run(TransactionType::getModel()); //
        $this->run(MerchantCategory::getModel()); //
        $this->run(SalaryType::getModel()); //
        $this->run(ServiceCategory::getModel()); //
        $this->run(BookingStatus::getModel()); //
        $this->run(ProductCategory::getModel()); //
        $this->run(ProductCondition::getModel()); //
        $this->run(ShippingStatus::getModel()); //
        $this->run(TransactionDisputeReason::getModel()); //
        $this->run(ReturnOrderStatus::getModel()); //
        $this->run(ReturnReason::getModel()); //
    }

    private function run(Model $model)
    {
        $rows = $model::where('slug', 'like', '%-%')->get();
        foreach ($rows as $row) {
            $row->slug = str_replace('-', '_', $row->slug);
            $row->save();
        }
    }
}
