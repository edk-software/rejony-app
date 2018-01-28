<?php

namespace Cantiga\CoreBundle\Controller\Traits;

trait SlugifyTrait
{
    protected function slugify(string $text) : string
    {
        $text = mb_strtolower($text);
        $text = $this->changeSmallRegionalChars($text);
        $text = str_replace(' ', '-', $text);
        $text = preg_replace('#[^0-9a-z_-]#', '', $text);
        while (strpos($text, '--') !== false) {
            $text = str_replace('--', '-', $text);
        }
        $text = trim($text, '-');

        return $text;
    }

    private function changeSmallRegionalChars(string $text) : string
    {
        $regionalChars = 'ąàáâãäåćçęèéêëìíîïńñóðòôõöśšùúûüýÿźżž';
        $standardChars = 'aaaaaaacceeeeeiiiinnoooooossuuuuyyzzz';
        $text = str_replace(preg_split('//u', $regionalChars, -1, PREG_SPLIT_NO_EMPTY),
            preg_split('//u', $standardChars, -1, PREG_SPLIT_NO_EMPTY), $text);

        return $text;
    }
}
