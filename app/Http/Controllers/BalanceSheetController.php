<?php

namespace App\Http\Controllers;

use App\Services\Accounting\BalanceSheetService;
use Illuminate\Http\Request;

class BalanceSheetController extends Controller
{
    public function __construct(private readonly BalanceSheetService $balanceSheetService)
    {
    }

    public function index(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());
        $report = $this->balanceSheetService->build($asOfDate);

        return view('reports.balance-sheet', compact('report', 'asOfDate'));
    }
}
