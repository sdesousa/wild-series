<?php


namespace App\Service;


class Slugify
{

    public function generate(string $input) : string
    {
        $oldLocale = setlocale(LC_ALL, '0');
        setlocale(LC_ALL, 'fr_FR.UTF-8');
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $input);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower($clean);
        $clean = preg_replace("/[\/_|+ -]+/", '-', $clean);
        $clean = trim($clean, '-');
        setlocale(LC_ALL, $oldLocale);
        return $clean;
    }
}
