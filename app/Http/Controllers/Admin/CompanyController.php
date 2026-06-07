<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Company;
use App\Models\CompanyMember;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status', '');

        if (!$request->has('status') && !$search) {
            $pendingMemberCountQuick = CompanyMember::where('status', 'pending')
                ->whereHas('company', fn($q) => $q->where('status', 'active'))
                ->count();
            $pendingCompanyCountQuick = Company::where('status', 'pending')->count();
            if ($pendingMemberCountQuick + $pendingCompanyCountQuick > 0) {
                return redirect()->route('admin.companies.index', ['status' => 'pending']);
            }
        }

        $pendingMemberCount = CompanyMember::where('status', 'pending')
            ->whereHas('company', fn($q) => $q->where('status', 'active'))
            ->count();

        $tabCounts = [
            ''         => Company::count(),
            'active'   => Company::where('status', 'active')->count(),
            'inactive' => Company::where('status', 'inactive')->count(),
            'pending'  => Company::where('status', 'pending')->count() + $pendingMemberCount,
        ];

        $pendingCompanies = collect();
        $pendingMembers   = collect();

        if ($status === 'pending') {
            $pendingCompanies = Company::where('status', 'pending')
                ->with(['members' => fn($q) => $q->wherePivot('status', 'pending')])
                ->orderBy('created_at', 'desc')
                ->get();

            $pendingMembers = CompanyMember::where('status', 'pending')
                ->whereHas('company', fn($q) => $q->where('status', 'active'))
                ->with(['company', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $companies = collect();
        if ($status !== 'pending') {
            $companies = Company::withCount([
                    'members as active_members_count' => fn($q) => $q->where('company_members.status', 'active'),
                    'sites',
                ])
                ->when($search, fn($q) => $q->where(fn($inner) =>
                    $inner->where('name', 'like', "%$search%")
                          ->orWhere('email', 'like', "%$search%")
                          ->orWhere('phone', 'like', "%$search%")
                          ->orWhere('owner_name', 'like', "%$search%")
                ))
                ->when($status, fn($q) => $q->where('status', $status))
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString();
        }

        return view('admin.companies.index', compact(
            'companies', 'search', 'status', 'tabCounts',
            'pendingCompanies', 'pendingMembers'
        ));
    }

    public function create()
    {
        $defaultTaxPct = round((float) AdminSetting::get('default_tax_rate', 0.075) * 100, 4);
        return view('admin.companies.create', compact('defaultTaxPct'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'owner_name'     => 'nullable|string|max:255',
            'tax_id'         => 'nullable|string|max:50',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'website'        => 'nullable|url|max:255',
            'address_street' => 'nullable|string|max:255',
            'address_city'   => 'nullable|string|max:100',
            'address_state'  => 'nullable|string|max:2',
            'address_zip'    => 'nullable|string|max:10',
            'tax_rate_pct'   => 'nullable|numeric|min:0|max:100',
            'status'         => 'required|in:pending,active,inactive',
        ]);

        $data['tax_rate']   = ($data['tax_rate_pct'] !== null && $data['tax_rate_pct'] !== '')
            ? round($data['tax_rate_pct'] / 100, 6) : null;
        unset($data['tax_rate_pct']);
        $data['created_by'] = auth()->id();

        $company = Company::create($data);

        return redirect()->route('admin.companies.show', $company)
            ->with('success', "Company \"{$company->name}\" created.");
    }

    public function show(Company $company)
    {
        $company->load([
            'members' => fn($q) => $q->wherePivot('status', 'active')->orderBy('users.name'),
            'sites'   => fn($q) => $q->orderBy('is_default', 'desc')->orderBy('label'),
        ]);

        $pendingMembers = CompanyMember::where('company_id', $company->id)
            ->where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        $recentOrders = $company->workOrders()
            ->with('customer')
            ->latest()
            ->limit(10)
            ->get();

        $availableUsers = User::where('role', 'customer')
            ->where('status', 'active')
            ->whereDoesntHave('companyMemberships', fn($q) =>
                $q->where('company_id', $company->id)->whereIn('status', ['pending', 'active'])
            )
            ->orderBy('name')
            ->get();

        return view('admin.companies.show', compact(
            'company', 'pendingMembers', 'recentOrders', 'availableUsers'
        ));
    }

    public function edit(Company $company)
    {
        $defaultTaxPct = round((float) AdminSetting::get('default_tax_rate', 0.075) * 100, 4);
        return view('admin.companies.edit', compact('company', 'defaultTaxPct'));
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'owner_name'     => 'nullable|string|max:255',
            'tax_id'         => 'nullable|string|max:50',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'website'        => 'nullable|url|max:255',
            'address_street' => 'nullable|string|max:255',
            'address_city'   => 'nullable|string|max:100',
            'address_state'  => 'nullable|string|max:2',
            'address_zip'    => 'nullable|string|max:10',
            'tax_rate_pct'   => 'nullable|numeric|min:0|max:100',
            'status'         => 'required|in:pending,active,inactive',
        ]);

        $data['tax_rate'] = ($data['tax_rate_pct'] !== null && $data['tax_rate_pct'] !== '')
            ? round($data['tax_rate_pct'] / 100, 6) : null;
        unset($data['tax_rate_pct']);

        $company->update($data);

        return redirect()->route('admin.companies.show', $company)
            ->with('success', 'Company updated.');
    }

    public function destroy(Company $company)
    {
        $company->update(['status' => 'inactive']);
        return redirect()->route('admin.companies.index', ['status' => 'inactive'])
            ->with('success', "\"{$company->name}\" has been inactivated.");
    }

    public function storeSite(Request $request, Company $company)
    {
        $data = $request->validate([
            'label'  => 'required|string|max:100',
            'street' => 'required|string|max:255',
            'city'   => 'required|string|max:100',
            'state'  => 'required|string|max:2',
            'zip'    => 'required|string|max:10',
            'county' => 'nullable|string|max:100',
        ]);

        CustomerAddress::create(array_merge($data, [
            'company_id' => $company->id,
            'is_active'  => true,
            'is_default' => $company->sites()->count() === 0,
        ]));

        return back()->with('success', 'Site added.');
    }

    public function updateSite(Request $request, Company $company, CustomerAddress $site)
    {
        abort_if($site->company_id !== $company->id, 404);
        $data = $request->validate([
            'label'  => 'required|string|max:100',
            'street' => 'required|string|max:255',
            'city'   => 'required|string|max:100',
            'state'  => 'required|string|max:2',
            'zip'    => 'required|string|max:10',
            'county' => 'nullable|string|max:100',
        ]);
        $site->update($data);
        return back()->with('success', 'Site updated.');
    }

    public function setDefaultSite(Company $company, CustomerAddress $site)
    {
        abort_if($site->company_id !== $company->id, 404);
        CustomerAddress::where('company_id', $company->id)->update(['is_default' => false]);
        $site->update(['is_default' => true]);
        return back()->with('success', 'Default site updated.');
    }

    public function destroySite(Company $company, CustomerAddress $site)
    {
        abort_if($site->company_id !== $company->id, 404);
        $site->delete();
        return back()->with('success', 'Site removed.');
    }

    public function attachMember(Request $request, Company $company)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $userId   = $request->input('user_id');
        $existing = CompanyMember::where('company_id', $company->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->update([
                'status'      => 'active',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        } else {
            CompanyMember::create([
                'company_id'  => $company->id,
                'user_id'     => $userId,
                'status'      => 'active',
                'is_primary'  => false,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        }

        return back()->with('success', 'Customer linked to company.');
    }

    public function detachMember(Company $company, User $user)
    {
        CompanyMember::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->update(['status' => 'removed']);

        return back()->with('success', 'Customer unlinked from company.');
    }

    public function setPrimaryMember(Company $company, User $user)
    {
        CompanyMember::where('company_id', $company->id)
            ->update(['is_primary' => false]);

        CompanyMember::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->update(['is_primary' => true]);

        return back()->with('success', 'Primary contact updated.');
    }

    public function approveCompany(Company $company)
    {
        $company->update(['status' => 'active']);

        CompanyMember::where('company_id', $company->id)
            ->where('status', 'pending')
            ->update([
                'status'      => 'active',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

        return back()->with('success', "\"{$company->name}\" has been approved and is now active.");
    }

    public function rejectCompany(Company $company)
    {
        CompanyMember::where('company_id', $company->id)->update(['status' => 'removed']);
        $company->delete();

        return redirect()->route('admin.companies.index', ['status' => 'pending'])
            ->with('success', 'Company request rejected and removed.');
    }

    public function approveMember(Company $company, User $user)
    {
        CompanyMember::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->update([
                'status'      => 'active',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

        return back()->with('success', "{$user->name} has been approved.");
    }

    public function rejectMember(Company $company, User $user)
    {
        CompanyMember::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->update(['status' => 'removed']);

        return back()->with('success', "Request from {$user->name} declined.");
    }
}
