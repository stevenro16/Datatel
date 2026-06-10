<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Company;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\ReportService;
use Illuminate\Http\Request;

/**
 * Admin Reports — print-oriented, multi-page reports across work orders,
 * invoicing, technicians, and customers/companies.
 *
 * index() renders the report catalog (with filter forms). Each report method
 * resolves the period, gathers the data via ReportService, and returns a
 * standalone print view (extends admin.reports.layout — no admin chrome).
 */
class ReportController extends Controller
{
    public function __construct(
        private AnalyticsService $analytics,
        private ReportService $reports,
    ) {}

    /**
     * The report catalog, grouped by category. Drives the index cards and keeps
     * report metadata in one place.
     *
     * @return array<string, array<int, array{slug:string,title:string,desc:string,filters:array<int,string>}>>
     */
    public static function catalog(): array
    {
        return [
            'Work Orders' => [
                ['slug' => 'work-order-summary', 'title' => 'Work Order Summary',  'desc' => 'All work orders created in a period, broken down by status and urgency.', 'filters' => ['range']],
                ['slug' => 'work-order-aging',   'title' => 'Open Order Aging',     'desc' => 'Every open work order, aged from its creation date and grouped by status.', 'filters' => []],
            ],
            'Invoicing' => [
                ['slug' => 'invoice-register',   'title' => 'Invoice Register',     'desc' => 'All invoices raised in a period with subtotal, tax, and totals by status.', 'filters' => ['range']],
                ['slug' => 'accounts-receivable','title' => 'Accounts Receivable Aging', 'desc' => 'Outstanding invoices aged into 30/60/90-day buckets. Current snapshot.', 'filters' => []],
            ],
            'Technicians' => [
                ['slug' => 'technician-productivity', 'title' => 'Technician Productivity', 'desc' => 'Per-tech completed orders, hours logged, and average days to complete.', 'filters' => ['range']],
                ['slug' => 'technician-time',         'title' => 'Technician Time Detail',  'desc' => 'Detailed labor log of every clocked time entry, grouped by technician.', 'filters' => ['range', 'tech']],
            ],
            'Customers & Companies' => [
                ['slug' => 'customer-statement',  'title' => 'Customer Statement',   'desc' => 'Per-customer activity and balance: work orders, invoiced, paid, outstanding.', 'filters' => ['range', 'customer']],
                ['slug' => 'company-performance', 'title' => 'Company Performance',  'desc' => 'Per-company work order volume, completions, revenue, and outstanding balance.', 'filters' => ['range', 'company']],
                ['slug' => 'service-usage',       'title' => 'Service Catalog Usage','desc' => 'How often each service is requested, completions, and list value.', 'filters' => ['range']],
            ],
        ];
    }

    public function index()
    {
        return view('admin.reports.index', [
            'catalog'   => self::catalog(),
            'customers' => User::where('role', User::ROLE_CUSTOMER)->orderBy('name')->get(['id', 'name']),
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'techs'     => User::where('role', User::ROLE_EMPLOYEE)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    // ── Work Orders ───────────────────────────────────────────────────────────

    public function workOrderSummary(Request $request)
    {
        $p = $this->period($request);

        return $this->render('work-order-summary', 'Work Order Summary', $p['label'],
            $this->reports->workOrderSummary($p['from'], $p['to']));
    }

    public function workOrderAging()
    {
        return $this->render('work-order-aging', 'Open Order Aging', $this->asOf(),
            $this->reports->workOrderAging());
    }

    // ── Invoicing ─────────────────────────────────────────────────────────────

    public function invoiceRegister(Request $request)
    {
        $p = $this->period($request);

        return $this->render('invoice-register', 'Invoice Register', $p['label'],
            $this->reports->invoiceRegister($p['from'], $p['to']));
    }

    public function accountsReceivable()
    {
        return $this->render('accounts-receivable', 'Accounts Receivable Aging', $this->asOf(),
            $this->reports->accountsReceivable());
    }

    // ── Technicians ───────────────────────────────────────────────────────────

    public function technicianProductivity(Request $request)
    {
        $p = $this->period($request);

        return $this->render('technician-productivity', 'Technician Productivity', $p['label'],
            $this->reports->technicianProductivity($p['from'], $p['to']));
    }

    public function technicianTime(Request $request)
    {
        $p      = $this->period($request);
        $techId = $request->integer('tech_id') ?: null;

        return $this->render('technician-time', 'Technician Time Detail', $p['label'],
            $this->reports->technicianTime($p['from'], $p['to'], $techId));
    }

    // ── Customers & Companies ─────────────────────────────────────────────────

    public function customerStatement(Request $request)
    {
        $p          = $this->period($request);
        $customerId = $request->integer('customer_id') ?: null;

        return $this->render('customer-statement', 'Customer Statement', $p['label'],
            $this->reports->customerStatement($p['from'], $p['to'], $customerId));
    }

    public function companyPerformance(Request $request)
    {
        $p         = $this->period($request);
        $companyId = $request->integer('company_id') ?: null;

        return $this->render('company-performance', 'Company Performance', $p['label'],
            $this->reports->companyPerformance($p['from'], $p['to'], $companyId));
    }

    public function serviceUsage(Request $request)
    {
        $p = $this->period($request);

        return $this->render('service-usage', 'Service Catalog Usage', $p['label'],
            $this->reports->serviceCatalogUsage($p['from'], $p['to']));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function render(string $slug, string $title, string $rangeLabel, array $data)
    {
        return view("admin.reports.$slug", [
            'data'        => $data,
            'company'     => $this->company(),
            'reportTitle' => $title,
            'rangeLabel'  => $rangeLabel,
            'generatedAt' => now(),
        ]);
    }

    private function period(Request $request): array
    {
        $r = $this->analytics->resolveRange($request);

        return ['from' => $r['from'], 'to' => $r['to'], 'label' => $r['label']];
    }

    private function asOf(): string
    {
        return 'As of ' . now()->format('M j, Y');
    }

    private function company(): array
    {
        return [
            'name'    => AdminSetting::get('company_name', 'DataTel'),
            'phone'   => AdminSetting::get('company_phone', ''),
            'email'   => AdminSetting::get('company_email', ''),
            'address' => AdminSetting::get('company_address', ''),
        ];
    }
}
