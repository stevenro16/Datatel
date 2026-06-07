<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function edit()
    {
        return view('employee.account', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'home_street' => 'nullable|string|max:255',
            'home_city'   => 'nullable|string|max:100',
            'home_state'  => 'nullable|string|max:50',
            'home_zip'    => 'nullable|string|max:20',
        ]);

        $user->update($data);

        return back()->with('success', 'Home address updated successfully.');
    }
}
