@extends('layouts.auth', ['title' => 'Forgot Password'])

@section('content')
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg h5">Forgot Password</p>

            <form action="{{ route('auth.forgot_password.post') }}"
                  method="post">
                @csrf

                <div class="input-group mb-3 has-validation">
                    <input autofocus
                           class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           name="email"
                           placeholder="Email"
                           required
                           type="email" />
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                    @if ($errors->has('email'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-primary btn-block"
                                type="submit">Send Password Reset Link</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>

            <p class="mt-3 mb-0">
                <a class="text-center"
                   href="{{ route('auth.login') }}">Log In</a>
            </p>
        </div>
        <!-- /.login-card-body -->
    </div>
@endsection
