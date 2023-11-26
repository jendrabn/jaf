@extends('layouts.admin')
@section('content')
  <div class="card">
    <div class="card-header">
      {{ __('Create') }} {{ __('User') }}
    </div>

    <div class="card-body">
      <form method="POST"
        action="{{ route('admin.users.store') }}"
        enctype="multipart/form-data">
        @csrf
        <div class="form-group">
          <label class="required"
            for="name">{{ __('Name') }}</label>
          <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
            id="name"
            name="name"
            type="text"
            value="{{ old('name', '') }}"
            required>
          @if ($errors->has('name'))
            <span class="text-danger">{{ $errors->first('name') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label class="required"
            for="email">{{ __('Email') }}</label>
          <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
            id="email"
            name="email"
            type="email"
            value="{{ old('email') }}"
            required>
          @if ($errors->has('email'))
            <span class="text-danger">{{ $errors->first('email') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label class="required"
            for="password">{{ __('Password') }}</label>
          <input class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
            id="password"
            name="password"
            type="password"
            required>
          @if ($errors->has('password'))
            <span class="text-danger">{{ $errors->first('password') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label class="required"
            for="roles">{{ __('Roles') }}</label>
          <div style="padding-bottom: 4px">
            <span class="btn btn-info btn-xs select-all"
              style="border-radius: 0">{{ __('Select all') }}</span>
            <span class="btn btn-info btn-xs deselect-all"
              style="border-radius: 0">{{ __('Deselect all') }}</span>
          </div>
          <select class="form-control select2 {{ $errors->has('roles') ? 'is-invalid' : '' }}"
            id="roles"
            name="roles[]"
            multiple
            required>
            @foreach ($roles as $id => $role)
              <option value="{{ $id }}"
                {{ in_array($id, old('roles', [])) ? 'selected' : '' }}>{{ $role }}</option>
            @endforeach
          </select>
          @if ($errors->has('roles'))
            <span class="text-danger">{{ $errors->first('roles') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label for="phone">{{ __('Phone Number') }}</label>
          <input class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
            id="phone"
            name="phone"
            type="text"
            value="{{ old('phone', '') }}">
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
                {{ old('sex', '') === $key ? 'selected' : '' }}>{{ $label }}</option>
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
            value="{{ old('birth_date') }}">
          @if ($errors->has('birth_date'))
            <span class="text-danger">{{ $errors->first('birth_date') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <button class="btn btn-danger"
            type="submit">
            {{ __('Save') }}
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
