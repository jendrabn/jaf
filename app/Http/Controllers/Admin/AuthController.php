<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handle the login request.
     *
     * @param LoginRequest $request
     * @return View|RedirectResponse
     */
    public function login(LoginRequest $request): View|RedirectResponse
    {
        if ($request->isMethod('GET')) {
            return view('admin.auth.login');
        }

        $throttleKey = strtolower($request->username . '|' . $request->ip());

        $maxAttempts = 3;

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->remember)) {
            RateLimiter::hit($throttleKey);

            toastr('Invalid credentials.', 'error');

            return back();
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        return redirect()->intended(route('admin.home', absolute: false));
    }

    /**
     * Logs out the user and redirects to the homepage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Returns the view for the forgot password page.
     *
     * @return View
     */
    public function forgotPassword(): View
    {
        return view('admin.auth.forgot_password');
    }

    /**
     * Sends a password reset email to the specified email address.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function sendResetPasswordLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'string', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            toastr($status, 'error');

            return redirect()->back();
        }

        toastr('We have emailed your password reset link.', 'success');

        return redirect()->back();
    }

    /**
     * Resets the password for a user.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function resetPassword(ResetPasswordRequest $request): View|RedirectResponse
    {
        if ($request->isMethod('GET')) {
            return view('admin.auth.reset_password', ['params' => $request->query()]);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(compact('password'))
                    ->setRememberToken(Str::random(60));

                $user->save();
            }
        );


        if ($status !== Password::PASSWORD_RESET) {
            toastr($status, 'error');

            return redirect()->back();
        }

        toastr('Your password has been reset.', 'success');

        return to_route('auth.login');
    }
}
