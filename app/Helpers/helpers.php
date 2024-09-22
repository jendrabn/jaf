<?php

if (!function_exists('formatRupiah')) {
    function formatRupiah(int $price)
    {
        return 'Rp ' . number_format($price, 0, ',', '.');
    }
}

