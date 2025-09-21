<?php

namespace App\Service;

final class TextSimiliarityService
{
    public function ratio(string $a, string $b): float
    {
        $normalize = fn(string $s) => trim(
            preg_replace('/[^\p{L}\d]+/u', '', iconv('UTF-8', 'ASCII//TRANSLIT', mb_strtolower($s)))
        );

        similar_text($normalize($a), $normalize($b), $percent);

        return round($percent, 1);
    }
}