<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
	public function login()
	{
		return view('auth/login');
	}

	public function loginAksi(Request $request)
	{
		Validator::make($request->all(), [
			'email' => 'required|email',
			'password' => 'required'
		])->validate();

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if (Auth::user()->role !== 'cashier') {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'You do not have the required access rights.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('Dashboard');

	}

	public function logout(Request $request)
	{
		Auth::guard('web')->logout();

		$request->session()->invalidate();

		return redirect('/');
	}

    public function show()
    {
        // Ambil semua user dengan role cashier
        $cashiers = User::where('role', 'cashier')->get();

        return view('Admin.addCashier', compact('cashiers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // Pastikan hashing
            'role' => 'cashier',
        ]);

        return redirect()->route('cashiers.show')->with('success', 'Cashier added successfully.');
    }

    public function edit(User $cashier)
    {
        // Pastikan user yang diedit adalah cashier
        if ($cashier->role !== 'cashier') {
            abort(403, 'Unauthorized action.');
        }

        return view('Admin.editCashier', compact('cashier'));
    }


    public function update(Request $request, User $cashier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($cashier->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $cashier->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        return redirect()->route('cashiers.show')->with('success', 'Cashier updated successfully.');
    }

    public function destroy(User $cashier)
    {
        // Pastikan user yang dihapus adalah cashier
        if ($cashier->role !== 'cashier') {
            abort(403, 'Unauthorized action.');
        }

        $cashier->delete();

        return redirect()->route('cashiers.show')->with('success', 'Cashier deleted successfully.');
    }

}
