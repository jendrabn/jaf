<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
  public function edit()
  {
    return view('auth.passwords.edit');
  }

  public function update(Request $request)
  {
    $validatedData = $request->validate([
      'password' => [
        'nullable',
        'string',
        Password::min(8)->mixedCase()->numbers(),
        'max:30'
      ],
    ]);

    auth()->user()->update($validatedData);

    return redirect()->route('profile.password.edit')->with('message', __('Password changed successfully'));
  }

  public function updateProfile(Request $request)
  {
    $validatedData = $request->validate([
      'name' => [
        'required',
        'string',
        'min:1',
        'max:30'

      ],
      'email' => [
        'required',
        'string',
        'email',
        'unique:users,email,' . auth()->id(),
      ],
      'phone' => [
        'nullable',
        'string',
        'min:10',
        'max:15',
        'starts_with:08,62,+62'
      ],
      'sex' => [
        'nullable',
        'integer',
        'in:1,2'
      ],
      'birth_date' => [
        'nullable',
        'date',
      ],
    ]);

    $user = auth()->user();

    $user->update($validatedData);

    return redirect()->route('profile.password.edit')->with('message', __('Profile updated successfully'));
  }

  public function destroy()
  {
    $user = auth()->user();

    $user->update([
      'email' => time() . '_' . $user->email,
    ]);

    $user->delete();

    return redirect()->route('login')->with('message', __('global.delete_account_success'));
  }
}
