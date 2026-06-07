<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\InquiryNote;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status', 'all');
        $query  = Inquiry::latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $inquiries = $query->paginate(25)->withQueryString();

        $counts = [
            'all'         => Inquiry::count(),
            'new'         => Inquiry::where('status', Inquiry::STATUS_NEW)->count(),
            'in_progress' => Inquiry::where('status', Inquiry::STATUS_IN_PROGRESS)->count(),
            'closed'      => Inquiry::where('status', Inquiry::STATUS_CLOSED)->count(),
        ];

        return view('admin.inquiries.index', compact('inquiries', 'status', 'counts'));
    }

    public function show(Inquiry $inquiry): View
    {
        $inquiry->load('notes.admin');
        $serviceNames = [];
        if (!empty($inquiry->services)) {
            $serviceNames = ServiceType::whereIn('id', $inquiry->services)->pluck('name')->all();
        }
        $allServices = ServiceType::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']);

        return view('admin.inquiries.show', compact('inquiry', 'serviceNames', 'allServices'));
    }

    public function addNote(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $request->validate(['note' => 'required|string|max:3000']);

        InquiryNote::create([
            'inquiry_id' => $inquiry->id,
            'admin_id'   => auth()->id(),
            'note'       => $request->input('note'),
        ]);

        if ($inquiry->status === Inquiry::STATUS_NEW) {
            $inquiry->update(['status' => Inquiry::STATUS_IN_PROGRESS]);
        }

        return back()->with('success', 'Note added.');
    }

    public function updateStatus(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $request->validate(['status' => 'required|in:new,in_progress,closed']);
        $inquiry->update(['status' => $request->input('status')]);

        return back()->with('success', 'Status updated.');
    }

    public function checkEmail(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $user  = User::where('email', $email)->first(['id', 'name', 'email', 'phone', 'role', 'status']);

        if ($user) {
            return response()->json(['exists' => true, 'user' => $user]);
        }

        return response()->json(['exists' => false]);
    }

    public function createWorkOrder(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:120',
            'email'       => 'required|email|max:200',
            'phone'       => 'nullable|string|max:30',
            'company'     => 'nullable|string|max:150',
            'description' => 'required|string|max:5000',
            'urgency'     => 'required|in:routine,urgent,emergency',
        ]);

        // Find or create the customer user
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'] ?? null,
                'password' => Hash::make(Str::random(24)),
                'role'     => User::ROLE_CUSTOMER,
                'status'   => User::STATUS_ACTIVE,
            ]);
        }

        // Create the work order
        $workOrder = WorkOrder::create([
            'customer_id' => $user->id,
            'status'      => WorkOrder::STATUS_NEW,
            'urgency'     => $data['urgency'],
            'description' => $data['description'],
            'created_by'  => auth()->id(),
        ]);

        // Pre-attach service types from inquiry
        if (!empty($inquiry->services)) {
            $validIds = ServiceType::whereIn('id', $inquiry->services)->pluck('id')->all();
            if ($validIds) {
                $workOrder->serviceTypes()->sync($validIds);
            }
        }

        WorkOrderHistory::create([
            'work_order_id' => $workOrder->id,
            'changed_by'    => auth()->id(),
            'field_name'    => 'status',
            'old_value'     => null,
            'new_value'     => WorkOrder::STATUS_NEW,
            'comment'       => 'Work order created from contact inquiry #' . $inquiry->id . '.',
            'changed_at'    => now(),
        ]);

        $inquiry->update(['status' => Inquiry::STATUS_CLOSED]);

        return redirect()
            ->route('admin.work-orders.show', $workOrder)
            ->with('success', 'Work order created from inquiry. ' . ($user->wasRecentlyCreated ? 'A new customer account was created for ' . $user->email . '.' : 'Linked to existing account: ' . $user->email . '.'));
    }
}
