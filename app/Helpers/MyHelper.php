<?php

namespace App\Helpers;

use App\Models\Prabayar;
use Illuminate\Support\Facades\DB;

class MyHelper
{
    public static function invoice($userId, string $prefix, string $tableName = 'deposits')
    {
        $lastInvoice = DB::table($tableName)->orderBy('created_at', 'desc')->first();
        $invoiceNumber = $lastInvoice ? $lastInvoice->id + 1 : 1;

        return sprintf($prefix . '-%06d-%d', $invoiceNumber, $userId);
    }
}
