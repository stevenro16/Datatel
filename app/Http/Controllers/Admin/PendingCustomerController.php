<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Company;
use App\Models\CompanyMember;
use App\Models\User;
use Illuminate\Http\Request;

class PendingCustomerController extends Controller
{
    public function index()
    {
        $pending = User::with('requestedCompany')
            ->where('role', 'customer')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $defaultTaxPct = (float) AdminSetting::get('default_tax_rate', '0.0750') * 100;

        return view('admin.pending-customers.index', compact('pending', 'defaultTaxPct'));
    }

    public function approve(User $user)
    {
        abort_if($user->role !== 'customer' || $user->status !== 'pending', 404);

        $user->update(['status' => 'active']);

        if ($user->requested_company_id) {
            CompanyMember::updateOrCreate(
                ['company_id' => $user->requested_company_id, 'user_id' => $user->id],
                [
                    'status'      => 'active',
                    'is_primary'  => true,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]
            );
        }

        return back()->with('success', "{$user->name}'s account has been approved. They can now access the customer portal.");
    }

    public function reject(User $user)
    {
        abort_if($user->role !== 'customer' || $user->status !== 'pending', 404);

        $name = $user->name;
        $user->forceDelete();

        return back()->with('success', "{$name}'s account request has been rejected and removed.");
    }

    public function createCompany(Request $request, User $user)
    {
        abort_if($user->role !== 'customer' || $user->status !== 'pending', 404);

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'owner_name'     => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'address_street' => 'nullable|string|max:255',
            'address_city'   => 'nullable|string|max:100',
            'address_state'  => 'nullable|string|max:2',
            'address_zip'    => 'nullable|string|max:10',
            'tax_rate_pct'   => 'nullable|numeric|min:0|max:100',
        ]);

        $company = Company::create([
            'name'           => $data['name'],
            'owner_name'     => $data['owner_name'] ?? null,
            'phone'          => $data['phone'] ?? null,
            'email'          => $data['email'] ?? null,
            'address_street' => $data['address_street'] ?? null,
            'address_city'   => $data['address_city'] ?? null,
            'address_state'  => $data['address_state'] ?? null,
            'address_zip'    => $data['address_zip'] ?? null,
            'tax_rate'       => isset($data['tax_rate_pct']) ? round($data['tax_rate_pct'] / 100, 6) : null,
            'status'         => 'active',
            'created_by'     => auth()->id(),
        ]);

        $user->update([
            'requested_company_id'   => $company->id,
            'requested_company_name' => null,
        ]);

        return back()->with('success', "Company \"{$company->name}\" created and linked to {$user->name}'s account.");
    }
}
