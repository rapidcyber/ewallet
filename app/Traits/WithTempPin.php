<?php

namespace App\Traits;

trait WithTempPin
{
    public function generate_temp_pin()
    {
        if (config('app.env') == 'local') {
            return '1234';
        } else {

            $alpha = '0123456789';
            $alphaLen = strlen($alpha);
            $randomAlpha = '';
            for ($i = 0; $i < 4; $i++) {
                $randomAlpha = $randomAlpha.$alpha[rand(0, $alphaLen - 1)];
            }

            return $randomAlpha;
        }
    }
}
