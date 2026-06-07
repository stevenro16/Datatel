<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    private function siteRules(): array
    {
        return [
            'label'  => 'required|string|max:100',
            'street' => 'required|string|max:255',
            'city'   => 'required|string|max:100',
            'state'  => 'required|string|max:2',
            'zip'    => 'required|string|max:10',
            'county' => 'nullable|string|max:100',
        ];
    }

    private function getCompanyId(): ?int
    {
        return auth()->user()
            ->companyMemberships()
            ->where('status', 'active')
            ->value('company_id');
    }

    private function authorizeSite(CustomerAddress $address): void
    {
        $user      = auth()->user();
        $companyId = $this->getCompanyId();

        if ($address->company_id) {
            abort_if($address->company_id !== $companyId, 403);
        } else {
            abort_if($address->user_id !== $user->id, 403);
        }
    }

    public function store(Request $request)
    {
        $data      = $request->validate($this->siteRules());
        $user      = auth()->user();
        $companyId = $this->getCompanyId();

        $isFirst = CustomerAddress::forUser($user)->where('is_active', true)->count() === 0;

        $site = CustomerAddress::create([
            ...$data,
            'user_id'    => $user->id,
            'company_id' => $companyId,
            'is_default' => $isFirst,
            'is_active'  => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['id' => $site->id]);
        }

        return back()->with('success', 'Site added.');
    }

    public function update(Request $request, CustomerAddress $address)
    {
        $this->authorizeSite($address);
        $address->update($request->validate($this->siteRules()));
        return back()->with('success', 'Site updated.');
    }

    public function deactivate(CustomerAddress $address)
    {
        $this->authorizeSite($address);
        $wasDefault = $address->is_default;

        $address->update(['is_active' => false, 'is_default' => false]);

        if ($wasDefault) {
            CustomerAddress::forUser(auth()->user())
                ->where('is_active', true)
                ->where('id', '!=', $address->id)
                ->first()
                ?->update(['is_default' => true]);
        }

        return back()->with('success', 'Site deactivated.');
    }

    public function reactivate(CustomerAddress $address)
    {
        $this->authorizeSite($address);
        $address->update(['is_active' => true]);
        return back()->with('success', 'Site reactivated.');
    }

    public function setDefault(CustomerAddress $address)
    {
        $this->authorizeSite($address);
        CustomerAddress::forUser(auth()->user())->update(['is_default' => false]);
        $address->update(['is_default' => true]);
        return back()->with('success', 'Default site updated.');
    }
}
