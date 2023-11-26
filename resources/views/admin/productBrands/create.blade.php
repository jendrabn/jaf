@extends('layouts.admin')
@section('content')
  <div class="card">
    <div class="card-header">
      {{ __('Create') }} {{ __('Brand') }}
    </div>

    <div class="card-body">
      <form method="POST"
        action="{{ route('admin.product-brands.store') }}"
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
          <button class="btn btn-primary"
            type="submit">
            {{ __('Save') }}
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
