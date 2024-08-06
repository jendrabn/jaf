@extends('layouts.auth', ['title' => 'Reset Password'])

@section('content')
    <form action="{{ route('auth.reset-password.put', $params) }}"
          method="post">
        @csrf
        @method('PUT')
        <div class="input-group mb-3">
            <input autofocus
                   class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                   name="password"
                   placeholder="Password"
                   type="password" />
            <div class="input-group-text"><span class="bi bi-lock-fill"></span> </div>
            @if ($errors->has('password'))
                <div class="invalid-feedback">
                    {{ $errors->first('password') }}
                </div>
            @endif
        </div>

        <div class="input-group mb-3">
            <input class="form-control"
                   name="password_confirmation"
                   placeholder="Confirm Password"
                   type="password" />
            <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
        </div>
        <div class="d-grid">
            <button class="btn btn-primary"
                    type="submit">Reset Password
            </button>
        </div>
    </form>
    <p class="mb-0 mt-3">
        <a href="{{ route('auth.login') }}">Login</a>
    </p>
@endsection
