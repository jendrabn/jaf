<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;

class ProfileController extends Controller
{
    /**
     * Displays the user's profile page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $roles = Role::pluck("name", "id");

        return view('admin.profile', compact('user', 'roles'));
    }

    /**
     * Updates the user's profile information.
     *
     * @param ProfileRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(ProfileRequest $request)
    {
        $request->user()->update($request->validated());

        toastr('Profile updated successfully.', 'success');

        return back();
    }

    /**
     * Update the user's password.
     *
     * @param ProfileRequest $request -
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(ProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->only('password'));

        toastr('Password updated successfully.', 'success');

        return back();
    }
}
