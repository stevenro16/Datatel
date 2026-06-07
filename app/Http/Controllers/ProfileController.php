<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\CustomerAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user      = $request->user();
        $membership = $user->companyMemberships()->where('status', 'active')->with('company')->first();
        $company   = $membership?->company;

        $sites = CustomerAddress::forUser($user)
            ->orderByDesc('is_active')
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->get();

        return view('profile.edit', compact('user', 'sites', 'company'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());
        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updateAvailability(Request $request): RedirectResponse
    {
        $avail = null;
        $raw   = $request->input('preferred_availability');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $validDays  = ['monday','tuesday','wednesday','thursday','friday','saturday'];
                $validTimes = ['morning','lunch','afternoon'];
                $result = [];
                foreach ($validDays as $day) {
                    if (isset($decoded[$day]) && is_array($decoded[$day])) {
                        $times = array_values(array_intersect($decoded[$day], $validTimes));
                        if ($times) $result[$day] = $times;
                    }
                }
                $avail = $result ?: null;
            }
        }

        $request->user()->update(['preferred_availability' => $avail]);

        return Redirect::route('profile.edit')->with('success', 'Default availability saved.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
