@extends('layouts.admin')
@section('content')
  <div class="row">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          {{ __('My profile') }}
        </div>

        <div class="card-body">
          <form method="POST"
            action="{{ route('profile.password.updateProfile') }}">
            @csrf
            <div class="form-group">
              <label class="required"
                for="name">{{ __('Name') }}</label>
              <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                id="name"
                name="name"
                type="text"
                value="{{ old('name', auth()->user()->name) }}"
                required>
              @if ($errors->has('name'))
                <div class="invalid-feedback">
                  {{ $errors->first('name') }}
                </div>
              @endif
            </div>
            <div class="form-group">
              <label class="required"
                for="title">{{ __('Email') }}</label>
              <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                id="email"
                name="email"
                type="text"
                value="{{ old('email', auth()->user()->email) }}"
                required>
              @if ($errors->has('email'))
                <div class="invalid-feedback">
                  {{ $errors->first('email') }}
                </div>
              @endif
            </div>
            <div class="form-group">
              <label for="phone">{{ __('Phone Number') }}</label>
              <input class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                id="phone"
                name="phone"
                type="text"
                value="{{ old('phone', auth()->user()->phone) }}">
              @if ($errors->has('phone'))
                <span class="text-danger">{{ $errors->first('phone') }}</span>
              @endif
              <span class="help-block">{{ __() }}</span>
            </div>
            <div class="form-group">
              <label>{{ __('Gender') }}</label>
              <select class="form-control {{ $errors->has('sex') ? 'is-invalid' : '' }}"
                id="sex"
                name="sex">
                <option value
                  disabled
                  {{ old('sex', null) === null ? 'selected' : '' }}>{{ __('Please select') }}</option>
                @foreach (App\Models\User::SEX_SELECT as $key => $label)
                  <option value="{{ $key }}"
                    {{ old('sex', auth()->user()->sex) === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
              @if ($errors->has('sex'))
                <span class="text-danger">{{ $errors->first('sex') }}</span>
              @endif
              <span class="help-block">{{ __() }}</span>
            </div>
            <div class="form-group">
              <label for="birth_date">{{ __('Birth Date') }}</label>
              <input class="form-control date {{ $errors->has('birth_date') ? 'is-invalid' : '' }}"
                id="birth_date"
                name="birth_date"
                type="text"
                value="{{ old('birth_date', auth()->user()->birth_date) }}">
              @if ($errors->has('birth_date'))
                <span class="text-danger">{{ $errors->first('birth_date') }}</span>
              @endif
              <span class="help-block">{{ __() }}</span>
            </div>
            <div class="form-group">
              <button class="btn btn-danger"
                type="submit">
                {{ __('Update') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          {{ __('Change password') }}
        </div>
        <div class="card-body">
          <form method="POST"
            action="{{ route('profile.password.update') }}">
            @csrf
            <div class="form-group">
              <label class="required"
                for="password">New {{ __('Password') }}</label>
              <input class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                id="password"
                name="password"
                type="password"
                required>
              @if ($errors->has('password'))
                <span class="text-danger">{{ $errors->first('password') }}</span>
              @endif
            </div>
            <div class="form-group">
              <label class="required"
                for="password_confirmation">Repeat New {{ __('Password') }}</label>
              <input class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required>
            </div>
            <div class="form-group">
              <button class="btn btn-danger"
                type="submit">
                {{ __('Change') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          {{ __('Delete account') }}
        </div>

        <div class="card-body">
          <form method="POST"
            action="{{ route('profile.password.destroyProfile') }}"
            onsubmit="return prompt('{{ __('Enter your email address to confirm you want to delete account.\nThis action is not reversible.') }}') == '{{ auth()->user()->email }}'">
            @csrf
            <div class="form-group">
              <button class="btn btn-danger"
                type="submit">
                {{ __('Delete') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
@endsection
