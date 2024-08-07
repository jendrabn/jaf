@extends('layouts.auth', ['title' => 'Forgot Password'])

@section('content')
    <form action="{{ route('auth.forgot_password.post') }}"
          method="post">
        @csrf
        <div class="input-group mb-3">
            <input autofocus
                   class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                   name="email"
                   placeholder="Email"
                   type="email" />
            <div class="input-group-text"><span class="bi bi-envelope"></span></div>
            @if ($errors->has('email'))
                <div class="invalid-feedback">
                    {{ $errors->first('email') }}
                </div>
            @endif
        </div>

        <div class="d-grid">
            <button class="btn btn-primary"
                    type="submit">Send Password Reset Link
            </button>
        </div>
    </form>
    <p class="mt-3 mb-0">
        <a href="{{ route('auth.login') }}">Login</a>
    </p>
@endsection
