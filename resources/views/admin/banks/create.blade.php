@extends('layouts.admin')
@section('content')
  <div class="card">
    <div class="card-header">
      {{ __('Create') }} {{ __('Bank') }}
    </div>

    <div class="card-body">
      <form method="POST"
        action="{{ route('admin.banks.store') }}"
        enctype="multipart/form-data">
        @csrf
        <div class="form-group">
          <label class="required"
            for="logo">{{ __('Bank Logo') }}</label>
          <div class="needsclick dropzone {{ $errors->has('logo') ? 'is-invalid' : '' }}"
            id="logo-dropzone">
          </div>
          @if ($errors->has('logo'))
            <span class="text-danger">{{ $errors->first('logo') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label class="required"
            for="name">{{ __('Bank Name') }}</label>
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
            for="code">{{ __('Bank Code') }}</label>
          <input class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
            id="code"
            name="code"
            type="text"
            value="{{ old('code', '') }}"
            required>
          @if ($errors->has('code'))
            <span class="text-danger">{{ $errors->first('code') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label class="required"
            for="account_name">{{ __('Account Name') }}</label>
          <input class="form-control {{ $errors->has('account_name') ? 'is-invalid' : '' }}"
            id="account_name"
            name="account_name"
            type="text"
            value="{{ old('account_name', '') }}"
            required>
          @if ($errors->has('account_name'))
            <span class="text-danger">{{ $errors->first('account_name') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label class="required"
            for="account_number">{{ __('Account Number') }}</label>
          <input class="form-control {{ $errors->has('account_number') ? 'is-invalid' : '' }}"
            id="account_number"
            name="account_number"
            type="text"
            value="{{ old('account_number', '') }}"
            required>
          @if ($errors->has('account_number'))
            <span class="text-danger">{{ $errors->first('account_number') }}</span>
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

@section('scripts')
  <script>
    Dropzone.options.logoDropzone = {
      url: '{{ route('admin.banks.storeMedia') }}',
      maxFilesize: 1, // MB
      acceptedFiles: '.jpeg,.jpg,.png,.gif',
      maxFiles: 1,
      addRemoveLinks: true,
      headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      },
      params: {
        size: 1,
        width: 4096,
        height: 4096
      },
      success: function(file, response) {
        $('form').find('input[name="logo"]').remove()
        $('form').append('<input type="hidden" name="logo" value="' + response.name + '">')
      },
      removedfile: function(file) {
        file.previewElement.remove()
        if (file.status !== 'error') {
          $('form').find('input[name="logo"]').remove()
          this.options.maxFiles = this.options.maxFiles + 1
        }
      },
      init: function() {
        @if (isset($bank) && $bank->logo)
          var file = {!! json_encode($bank->logo) !!}
          this.options.addedfile.call(this, file)
          this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
          file.previewElement.classList.add('dz-complete')
          $('form').append('<input type="hidden" name="logo" value="' + file.file_name + '">')
          this.options.maxFiles = this.options.maxFiles - 1
        @endif
      },
      error: function(file, response) {
        if ($.type(response) === 'string') {
          var message = response //dropzone sends it's own error messages in string
        } else {
          var message = response.errors.file
        }
        file.previewElement.classList.add('dz-error')
        _ref = file.previewElement.querySelectorAll('[data-dz-errormessage]')
        _results = []
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          node = _ref[_i]
          _results.push(node.textContent = message)
        }

        return _results
      }
    }
  </script>
@endsection
