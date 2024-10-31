<?php

namespace App\Http\Controllers\Ui;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class AuthController extends Controller
{
    public function login()
    {
        $title = 'Login';

        return view('auth/login', compact('title'));
    }

    public function loginStore(Request $request)
    {
        $request->validate([
            'phone' => 'required|max:20',
            'user_tel_id' => 'required'
        ]);

        $user = User::where('phone', $request->phone)->where('user_tel_id', $request->user_tel_id)->first();

        if ($user) {
            $remember = $request->has('remember');
            Auth::login($user, $remember);
            return redirect(route('dashboard'));
        }

        Alert::error('Login Gagal', 'Nomor HP atau User ID Salah!');
        return redirect()->back();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('login'));
    }
}
