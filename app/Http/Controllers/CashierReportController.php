<?php

namespace App\Http\Controllers;

use App\Services\Tenants\CashierReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CashierReportController extends Controller
{
    public function __invoke(Request $request, CashierReportService $cashierReportService)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $reportData = $cashierReportService->generate($request->all());
        $reports = $reportData['reports'];
        $footer = $reportData['footer'];
        $header = $reportData['header'];

        $pdf = Pdf::loadView('reports.cashier', compact('reports', 'footer', 'header'))
            ->setPaper('a4', 'landscape');
        $pdf->output();
        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->getCanvas();
        $canvas->page_text(720, 570, 'software by {PAGE_NUM} shulepress {PAGE_COUNT}', null, 10, [0, 0, 0]);

        if ($request->ajax()) {
            return $pdf->download();
        }

        return $pdf->stream();
    }
}
