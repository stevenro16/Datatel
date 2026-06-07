<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\ServiceType;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = AdminSetting::all()->keyBy('key');
        $serviceTypes = ServiceType::orderBy('name')->get();
        return view('admin.settings', compact('settings', 'serviceTypes'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name'          => 'nullable|string|max:255',
            'company_phone'         => 'nullable|string|max:30',
            'company_email'         => 'nullable|email',
            'default_tax_rate'      => 'nullable|numeric|min:0|max:100',
            'invoice_terms'         => 'nullable|string',
            'invoice_due_days'      => 'nullable|integer|min:0',
        ]);

        foreach ($data as $key => $value) {
            AdminSetting::set($key, $value ?? '');
        }

        return back()->with('success', 'Settings saved.');
    }

    public function updateQueueOrder(Request $request)
    {
        $valid = ['all','new','pending_confirmation','scheduled','prepare_invoice','confirm_payment','unread'];

        $order = collect(explode(',', $request->input('queue_order', '')))
            ->map(fn($k) => trim($k))
            ->filter(fn($k) => in_array($k, $valid))
            ->unique()
            ->values();

        // Append any missing keys at the end so nothing is lost
        foreach ($valid as $key) {
            if (!$order->contains($key)) {
                $order->push($key);
            }
        }

        AdminSetting::set('work_queue_order', $order->implode(','));

        return back()->with('queue_success', 'Queue order saved.');
    }

    public function updateInvoiceQueueOrder(Request $request)
    {
        $valid = ['new', 'billed', 'payment_received', 'all_active', 'past_due', 'completed'];

        $order = collect(explode(',', $request->input('invoice_queue_order', '')))
            ->map(fn($k) => trim($k))
            ->filter(fn($k) => in_array($k, $valid))
            ->unique()
            ->values();

        foreach ($valid as $key) {
            if (!$order->contains($key)) {
                $order->push($key);
            }
        }

        AdminSetting::set('invoice_queue_order', $order->implode(','));

        return back()->with('invoice_queue_success', 'Invoice queue order saved.');
    }
}
