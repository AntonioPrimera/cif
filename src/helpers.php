<?php

use AntonioPrimera\Cif\Cif;

if (!function_exists('cif')) {
    function cif(Cif|string|null $cif): Cif|null
    {
        return $cif ? Cif::from($cif) : null;
    }
}
