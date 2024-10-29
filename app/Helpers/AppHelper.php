<?php

use App\Models\Digiflazz;

if (!function_exists('getProducts')) {
    function getProducts()
    {
        return Digiflazz::select('category')->groupBy('category')->get();
    }
}
