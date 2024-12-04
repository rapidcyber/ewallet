<?php

namespace App\Traits;

trait WithPhoneNumberPrefixes
{
    public function get_phone_number_prefixes()
    {
        return config('constants.phone_number_prefixes');
    }
}
