@extends('layouts.auth', ['title' => 'Log In'])

@section('content')
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg h5">Log In</p>

            <form action="{{ route('auth.login.post') }}"
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
                <div class="input-group mb-3 has-validation">
                    <input class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           name="password"
                           placeholder="Password"
                           required
                           type="password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    @if ($errors->has('password'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input"
                                   id="_remember"
                                   name="remember"
                                   type="checkbox">
                            <label class="custom-control-label"
                                   for="_remember">Remember Me</label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-4">
                        <button class="btn btn-primary btn-block"
                                type="submit">Log In</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>

            <p class="mt-3 mb-0">
                <a class="text-center"
                   href="{{ route('auth.forgot_password') }}">Forgot Password?</a>
            </p>
        </div>
        <!-- /.login-card-body -->
    </div>
@endsection
