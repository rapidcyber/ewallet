<?php

namespace App\Traits;

trait WithCountryName
{
    public function country_name($code)
    {
        return config('constants.code_country_name')[$code] ?? null;
    }
}
