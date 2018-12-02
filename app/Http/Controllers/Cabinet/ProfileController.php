<?php

namespace App\Http\Controllers\Cabinet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        session()->put('active', 'profile');

        $user = Auth::user();

        return view('cabinet.profile.home', compact('user'));
   }

    public function edit()
    {
        $user = Auth::user();

        return view('cabinet.profile.edit', compact('user'));
   }

    public function update(Request $request)
    {
        $user = Auth::user();

        $this->validate($request, [
           'name' => 'required|string|max:255',
           'last_name' => 'required|string|max:255',
           'phone' => ['required', 'string', 'regex:/^\d+$/s', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $oldPhone = $user->phone;

        $user->update($request->only('name', 'last_name', 'phone'));

        if ($user->phone !== $oldPhone) {
            $user->unverifyPhone();
        }

        return redirect()->route('cabinet.profile.home');
   }
}
