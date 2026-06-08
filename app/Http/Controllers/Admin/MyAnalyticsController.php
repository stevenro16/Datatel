<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class MyAnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $analytics) {}

    public function index(Request $request)
    {
        $range  = $this->analytics->resolveRange($request);
        $from   = $range['from'];
        $to     = $range['to'];
        $bucket = $this->analytics->pickBucket($from, $to);

        $kpis              = $this->analytics->kpis($from, $to);
        $workOrderSeries   = $this->analytics->workOrderTimeSeries($from, $to, $bucket);
        $statusDist        = $this->analytics->statusDistribution($from, $to);
        $pipeline          = $this->analytics->pipelineFunnel();
        $urgencyMix        = $this->analytics->urgencyMix($from, $to);
        $revenueSeries     = $this->analytics->revenueSeries($from, $to, $bucket);
        $invoiceStatusDist = $this->analytics->invoiceStatusDistribution($from, $to);
        $topServices       = $this->analytics->topServices($from, $to);
        $newCustomers12m   = $this->analytics->newCustomersTrailing12Months();
        $employee          = $this->analytics->employeePerformance($from, $to);

        // Bundle every chart's series + categories into one JSON payload for the view's <script>.
        $chartData = [
            'workOrders'     => $workOrderSeries,
            'status'         => $statusDist,
            'pipeline'       => $pipeline,
            'urgency'        => $urgencyMix,
            'revenue'        => $revenueSeries,
            'invoiceStatus'  => $invoiceStatusDist,
            'topServices'    => $topServices,
            'newCustomers'   => $newCustomers12m,
            'employee'       => $employee['chart'],
        ];

        return view('admin.my-analytics', [
            'range'        => $range,
            'bucket'       => $bucket,
            'kpis'         => $kpis,
            'leaderboard'  => $employee['leaderboard'],
            'chartData'    => $chartData,
        ]);
    }
}
