<?php

namespace App\Jobs;

use App\Models\ProductCondition;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateProductConditionSlug implements ShouldQueue
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
        $condition = ProductCondition::where('slug', 'brand-new')->first();

        if (empty($condition) == false) {
            $condition->slug = str('brand-new')->slug('_');
            $condition->save();
        }
    }
}
