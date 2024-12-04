<?php

use App\Jobs\RemoveDuplicateServiceCategories;
use App\Jobs\UpdateProductConditionSlug;
use App\Jobs\UpdateSlugsToSnakecase;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('remove-duplicate:service-categories', function () {
    RemoveDuplicateServiceCategories::dispatch();
})->purpose('Remove duplicate service categories from running static seeder twice.');

Artisan::command('update-slug:product-condition', function () {
    UpdateProductConditionSlug::dispatch();
})->purpose('Update slug for "Brand New" product condition from brand-new to brand_new.');

Artisan::command('update-slug:kebab-snake', function () {
    UpdateSlugsToSnakecase::dispatch();
})->purpose('Update slug for statics from kebab-case to snake_case');