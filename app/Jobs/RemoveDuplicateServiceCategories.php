<?php

namespace App\Jobs;

use App\Models\ServiceCategory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveDuplicateServiceCategories implements ShouldQueue
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
        $duplicateCategories = ServiceCategory::select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateCategories as $category) {
            $duplicates = ServiceCategory::where('name', $category->name)->get();
            
            $duplicates->shift();

            foreach ($duplicates as $duplicate) {
                $duplicate->delete();
            }
        }
    }
}
