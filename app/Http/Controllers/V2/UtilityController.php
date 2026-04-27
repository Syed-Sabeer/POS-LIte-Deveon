<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Account;
use App\Models\V2\Invoice;
use App\Models\V2\Item;
use App\Models\V2\Voucher;

class UtilityController extends Controller
{
    public function backup()
    {
        $payload = [
            'generated_at' => now()->toDateTimeString(),
            'accounts' => Account::count(),
            'items' => Item::count(),
            'invoices' => Invoice::count(),
            'vouchers' => Voucher::count(),
        ];

        return view('v2.reports.utility', ['title' => 'Backup', 'payload' => $payload]);
    }

    public function restore()
    {
        return view('v2.reports.utility', [
            'title' => 'Restore',
            'payload' => ['message' => 'Restore screen is ready. Upload/restore execution should be enabled only after a verified backup policy is approved.'],
        ]);
    }
}
