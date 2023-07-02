<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class AppExtensionRuntime implements RuntimeExtensionInterface
{

    public function hashInMd5(string $string): string
    {
        return md5($string);
    }
}
