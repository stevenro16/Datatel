<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withTrashed()
            ->with(['companyMemberships' => fn($q) => $q->where('status', 'active')->with('company')])
            ->latest();

        $role = $request->input('role', 'employee');
        if ($role && $role !== 'all') {
            $query->where('role', $role);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
        }

        $users = $query->paginate(25)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $companies = Company::where('status', 'active')->orderBy('name')->get();
        return view('admin.users.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'title'      => 'nullable|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:30',
            'role'       => 'required|in:customer,employee,admin',
            'status'     => 'required|in:active,inactive,pending',
            'password'   => 'required|string|min:8|confirmed',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        $user = User::create([
            'name'              => $data['name'],
            'title'             => $data['title'] ?? null,
            'email'             => $data['email'],
            'phone'             => $data['phone'] ?? null,
            'role'              => $data['role'],
            'status'            => $data['status'],
            'password'          => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        if ($data['role'] === 'customer' && !empty($data['company_id'])) {
            CompanyMember::create([
                'user_id'     => $user->id,
                'company_id'  => $data['company_id'],
                'status'      => 'active',
                'is_primary'  => true,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function show(User $user)
    {
        $user->load('workOrders', 'timeEntries');
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $companies        = Company::where('status', 'active')->orderBy('name')->get();
        $currentCompanyId = $user->companyMemberships()->where('status', 'active')->value('company_id');
        return view('admin.users.edit', compact('user', 'companies', 'currentCompanyId'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'title'         => 'nullable|string|max:100',
            'email'         => 'required|email|unique:users,email,'.$user->id,
            'phone'         => 'nullable|string|max:30',
            'role'          => 'required|in:customer,employee,admin',
            'status'        => 'required|in:active,inactive,pending',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
            'company_id'    => 'nullable|exists:companies,id',
            'home_street'   => 'nullable|string|max:255',
            'home_city'     => 'nullable|string|max:100',
            'home_state'    => 'nullable|string|max:50',
            'home_zip'      => 'nullable|string|max:20',
        ]);

        $user->name        = $data['name'];
        $user->title       = $data['title'] ?? null;
        $user->email       = $data['email'];
        $user->phone       = $data['phone'] ?? null;
        $user->role        = $data['role'];
        $user->status      = $data['status'];
        $user->home_street = $data['home_street'] ?? null;
        $user->home_city   = $data['home_city']   ?? null;
        $user->home_state  = $data['home_state']  ?? null;
        $user->home_zip    = $data['home_zip']    ?? null;

        if ($request->hasFile('profile_photo')) {
            // Delete old photo if present
            if ($user->profile_photo) {
                $old = storage_path('app/profile-photos/' . $user->profile_photo);
                if (file_exists($old)) {
                    unlink($old);
                }
            }

            $file     = $request->file('profile_photo');
            $filename = $user->id . '.' . $file->getClientOriginalExtension();
            $dir      = storage_path('app/profile-photos');
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $file->move($dir, $filename);
            $user->profile_photo = $filename;
        }

        if ($request->boolean('remove_photo') && $user->profile_photo) {
            $path = storage_path('app/profile-photos/' . $user->profile_photo);
            if (file_exists($path)) {
                unlink($path);
            }
            $user->profile_photo = null;
        }

        $user->save();

        // Update company membership for customers
        if ($user->role === 'customer') {
            $newCompanyId     = !empty($data['company_id']) ? (int) $data['company_id'] : null;
            $currentCompanyId = $user->companyMemberships()->where('status', 'active')->value('company_id');

            if ($newCompanyId !== $currentCompanyId) {
                $user->companyMemberships()->where('status', 'active')->update(['status' => 'removed']);

                if ($newCompanyId) {
                    CompanyMember::updateOrCreate(
                        ['user_id' => $user->id, 'company_id' => $newCompanyId],
                        [
                            'status'      => 'active',
                            'is_primary'  => true,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]
                    );
                }
            }
        }

        $redirectTo = $request->input('_redirect_to');
        if ($redirectTo && str_starts_with($redirectTo, url('/'))) {
            return redirect($redirectTo)->with('success', 'Customer updated.');
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function sendPasswordReset(User $user)
    {
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Password reset email sent to ' . $user->email . '.');
        }

        return back()->with('error', 'Could not send password reset email. Please check that the address is valid.');
    }

    public function destroy(User $user)
    {
        if ($user->is_super_admin) {
            return back()->with('error', 'Cannot delete the super admin account.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deactivated.');
    }

    public function quickStoreCompany(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:255',
        ]);

        $company = Company::create([
            'name'       => $data['name'],
            'owner_name' => $data['owner_name'] ?? null,
            'phone'      => $data['phone'] ?? null,
            'email'      => $data['email'] ?? null,
            'status'     => 'active',
        ]);

        return response()->json(['id' => $company->id, 'name' => $company->name]);
    }
}
