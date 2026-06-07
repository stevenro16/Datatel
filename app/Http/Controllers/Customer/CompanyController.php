<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyMember;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $membership = CompanyMember::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'active'])
            ->with(['company.sites' => fn($q) => $q->orderBy('is_default', 'desc')->orderBy('label')])
            ->latest()
            ->first();

        $pendingRequests  = collect();
        $activeMembers    = collect();
        $activeWorkOrders = collect();
        $doneWorkOrders   = collect();

        if ($membership && $membership->status === 'active' && $membership->company) {
            $companyId = $membership->company_id;

            $pendingRequests = CompanyMember::where('company_id', $companyId)
                ->where('status', 'pending')
                ->with('user')
                ->latest()
                ->get();

            $activeMembers = CompanyMember::where('company_id', $companyId)
                ->where('status', 'active')
                ->with('user')
                ->get()
                ->sortBy('user.name');

            $memberUserIds = CompanyMember::where('company_id', $companyId)
                ->where('status', 'active')
                ->pluck('user_id');

            $activeStatuses = ['new', 'triaged', 'scheduled', 'awaiting_feedback',
                               'services_performed', 'invoice_prepared', 'billed'];

            $activeWorkOrders = WorkOrder::with(['serviceTypes', 'customer'])
                ->whereIn('customer_id', $memberUserIds)
                ->whereIn('status', $activeStatuses)
                ->orderByRaw('scheduled_at IS NULL, scheduled_at ASC')
                ->get();

            $doneWorkOrders = WorkOrder::with(['serviceTypes', 'customer'])
                ->whereIn('customer_id', $memberUserIds)
                ->whereIn('status', ['completed', 'canceled'])
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        $companies = Company::where('status', 'active')->orderBy('name')->get();

        return view('customer.company.index', compact(
            'user', 'membership', 'pendingRequests', 'activeMembers', 'companies',
            'activeWorkOrders', 'doneWorkOrders'
        ));
    }

    public function requestCreate(Request $request)
    {
        $user = auth()->user();

        if (CompanyMember::where('user_id', $user->id)->whereIn('status', ['pending', 'active'])->exists()) {
            return back()->with('error', 'You already have an active or pending company membership.');
        }

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'owner_name'     => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'website'        => 'nullable|url|max:255',
            'address_street' => 'nullable|string|max:255',
            'address_city'   => 'nullable|string|max:100',
            'address_state'  => 'nullable|string|max:2',
            'address_zip'    => 'nullable|string|max:10',
        ]);

        $company = Company::create(array_merge($data, [
            'status'     => 'pending',
            'created_by' => $user->id,
        ]));

        CompanyMember::create([
            'company_id' => $company->id,
            'user_id'    => $user->id,
            'status'     => 'pending',
            'is_primary' => true,
        ]);

        return redirect()->route('portal.company')
            ->with('success', 'Your company creation request has been submitted. An admin will review it shortly.');
    }

    public function requestJoin(Request $request)
    {
        $user = auth()->user();

        if (CompanyMember::where('user_id', $user->id)->whereIn('status', ['pending', 'active'])->exists()) {
            return back()->with('error', 'You already have an active or pending company membership.');
        }

        $request->validate(['company_id' => 'required|exists:companies,id']);

        $company = Company::where('id', $request->company_id)
            ->where('status', 'active')
            ->firstOrFail();

        CompanyMember::create([
            'company_id' => $company->id,
            'user_id'    => $user->id,
            'status'     => 'pending',
            'is_primary' => false,
        ]);

        return redirect()->route('portal.company')
            ->with('success', "Your request to join \"{$company->name}\" has been submitted. It will be reviewed shortly.");
    }

    public function cancelRequest()
    {
        $user = auth()->user();

        $membership = CompanyMember::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('company')
            ->first();

        if (!$membership) {
            return back()->with('error', 'No pending request found.');
        }

        $company      = $membership->company;
        $isOnlyMember = $company
            ? CompanyMember::where('company_id', $company->id)->whereIn('status', ['pending', 'active'])->count() <= 1
            : false;

        $membership->delete();

        if ($company && $company->status === 'pending' && $isOnlyMember) {
            $company->delete();
        }

        return redirect()->route('portal.company')
            ->with('success', 'Your request has been cancelled.');
    }

    public function leaveCompany()
    {
        $user = auth()->user();

        $membership = CompanyMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            return back()->with('error', 'You are not an active member of any company.');
        }

        $membership->update(['status' => 'removed']);

        return redirect()->route('portal.company')
            ->with('success', 'You have been unlinked from the company.');
    }

    public function unlinkMember(Company $company, User $user)
    {
        $auth = auth()->user();

        abort_if(
            !CompanyMember::where('company_id', $company->id)
                ->where('user_id', $auth->id)
                ->where('status', 'active')
                ->where('is_primary', true)
                ->exists(),
            403
        );

        CompanyMember::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'removed']);

        return back()->with('success', "{$user->name} has been unlinked from the company.");
    }

    public function approveMember(Company $company, User $user)
    {
        $authUser = auth()->user();

        abort_if(
            !CompanyMember::where('company_id', $company->id)
                ->where('user_id', $authUser->id)
                ->where('status', 'active')
                ->exists(),
            403
        );

        CompanyMember::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->update([
                'status'      => 'active',
                'approved_by' => $authUser->id,
                'approved_at' => now(),
            ]);

        return back()->with('success', "{$user->name} has been approved to join the company.");
    }

    public function rejectMember(Company $company, User $user)
    {
        $authUser = auth()->user();

        abort_if(
            !CompanyMember::where('company_id', $company->id)
                ->where('user_id', $authUser->id)
                ->where('status', 'active')
                ->exists(),
            403
        );

        CompanyMember::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->update(['status' => 'removed']);

        return back()->with('success', "Request from {$user->name} has been declined.");
    }
}
