<?php

namespace App\Traits;

trait WithCustomPaginationLinks
{
    public function getPaginationElements($paginator)
    {
        $elements = [];

        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();

        if ($lastPage <= 5) {
            for ($i = 1; $i <= $lastPage; $i++) {
                $elements[] = $i;
            }
        } else {
            if ($currentPage <= 3) {
                for ($i = 1; $i <= 4; $i++) {
                    $elements[] = $i;
                }
                $elements[] = '...';
                $elements[] = $lastPage;
            } elseif ($currentPage > 3 && $currentPage < $lastPage - 2) {
                $elements[] = 1;
                $elements[] = '...';
                for ($i = $currentPage - 1; $i <= $currentPage + 1; $i++) {
                    $elements[] = $i;
                }
                $elements[] = '...';
                $elements[] = $lastPage;
            } else {
                $elements[] = 1;
                $elements[] = '...';
                for ($i = $lastPage - 3; $i <= $lastPage; $i++) {
                    $elements[] = $i;
                }
            }
        }

        return $elements;
    }
}
