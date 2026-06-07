<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $companies = Company::where('status', 'active')->orderBy('name')->get();
        return view('auth.register', compact('companies'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'title'              => ['nullable', 'string', 'max:100'],
            'email'              => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password'           => ['required', 'confirmed', Rules\Password::defaults()],
            'company_id'         => ['nullable', 'string'],
            'company_name_other' => ['nullable', 'string', 'max:255', 'required_if:company_id,other'],
        ]);

        $requestedCompanyId   = null;
        $requestedCompanyName = null;

        if ($request->company_id === 'other') {
            $requestedCompanyName = $request->company_name_other;
        } elseif ($request->filled('company_id') && is_numeric($request->company_id)) {
            $requestedCompanyId = (int) $request->company_id;
        }

        $user = User::create([
            'name'                   => $request->name,
            'title'                  => $request->title,
            'email'                  => $request->email,
            'password'               => Hash::make($request->password),
            'status'                 => 'pending',
            'requested_company_id'   => $requestedCompanyId,
            'requested_company_name' => $requestedCompanyName,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('account.pending');
    }
}
