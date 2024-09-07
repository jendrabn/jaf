@extends('layouts.auth', ['title' => 'Reset Password'])

@section('content')
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg h5">Reset Password</p>

            <form action="{{ route('auth.reset_password.put', $params) }}"
                  method="post">
                @csrf
                @method('PUT')

                <div class="input-group mb-3 has-validation">
                    <input autofocus
                           class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           name="password"
                           placeholder="Password"
                           required
                           type="password" />
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

                <div class="input-group mb-3">
                    <input class="form-control"
                           name="password_confirmation"
                           placeholder="Password"
                           required
                           type="password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-primary btn-block"
                                type="submit">Reset Password</button>
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
