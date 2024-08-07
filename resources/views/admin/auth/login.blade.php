@extends('layouts.auth', ['title' => 'Log In'])

@section('content')
    <form action="{{ route('auth.login.post') }}"
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
        <div class="input-group mb-3">
            <input class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
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
        <div class="row">
            <div class="col-8">
                <div class="form-check">
                    <input class="form-check-input"
                           id="remember"
                           name="remember"
                           type="checkbox" />
                    <label class="form-check-label"
                           for="remember">
                        Remember Me
                    </label>
                </div>
            </div>
            <div class="col-12">
                <div class="d-grid gap-2">
                    <button class="btn btn-primary"
                            type="submit">Log In
                    </button>
                </div>
            </div>
        </div>
    </form>
    <p class="mt-3 mb-0">
        <a href="{{ route('auth.forgot_password') }}">Forgot Password?</a>
    </p>
@endsection
