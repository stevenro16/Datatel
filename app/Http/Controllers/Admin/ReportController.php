<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\User;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to',   now()->toDateString());

        $stats = [
            'work_orders_total'     => WorkOrder::whereBetween('created_at', [$from, $to.' 23:59:59'])->count(),
            'work_orders_completed' => WorkOrder::where('status', WorkOrder::STATUS_COMPLETED)->whereBetween('created_at', [$from, $to.' 23:59:59'])->count(),
            'work_orders_canceled'  => WorkOrder::where('status', WorkOrder::STATUS_CANCELED)->whereBetween('created_at', [$from, $to.' 23:59:59'])->count(),
            'invoices_billed'       => Invoice::where('status', 'sent')->whereBetween('created_at', [$from, $to.' 23:59:59'])->count(),
            'revenue'               => Invoice::where('status', 'paid')->whereBetween('created_at', [$from, $to.' 23:59:59'])->withSum('lineItems', DB::raw('quantity * unit_price'))->get()->sum('line_items_sum_quantity * unit_price'),
            'new_customers'         => User::where('role', User::ROLE_CUSTOMER)->whereBetween('created_at', [$from, $to.' 23:59:59'])->count(),
        ];

        $byStatus = WorkOrder::whereBetween('created_at', [$from, $to.' 23:59:59'])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $topServices = DB::table('work_order_services')
            ->join('service_types', 'service_types.id', '=', 'work_order_services.service_type_id')
            ->join('work_orders', 'work_orders.id', '=', 'work_order_services.work_order_id')
            ->whereBetween('work_orders.created_at', [$from, $to.' 23:59:59'])
            ->selectRaw('service_types.name, count(*) as total')
            ->groupBy('service_types.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('admin.reports', compact('stats', 'byStatus', 'topServices', 'from', 'to'));
    }
}
