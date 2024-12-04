<?php

namespace App\Traits\Traits;

trait WithStringManipulation
{
    public function mask_name($name)
    {
        $words = explode(' ', $name);
        $masked_name = '';
        foreach ($words as $key => $word) {
            if ($key > 0) {
                $masked_name .= ' ';
            }
            $masked_name .= strlen($word) <= 4 ? 
                substr($word, 0, 1) . '***' 
                : substr($word, 0, 1) . str_repeat('*', strlen($word) - 2) . substr($word, -1);
        }

        return trim($masked_name);
    }
}
