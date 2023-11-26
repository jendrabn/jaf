@extends('layouts.admin')
@section('content')
  <div class="card">
    <div class="card-header">
      {{ __('Edit') }} {{ __('Banner') }}
    </div>

    <div class="card-body">
      <form method="POST"
        action="{{ route('admin.banners.update', [$banner->id]) }}"
        enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="form-group">
          <label class="required"
            for="image">{{ __('Image') }}</label>
          <div class="needsclick dropzone {{ $errors->has('image') ? 'is-invalid' : '' }}"
            id="image-dropzone">
          </div>
          @if ($errors->has('image'))
            <span class="text-danger">{{ $errors->first('image') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label class="required"
            for="image_alt">{{ __('Image Alt') }}</label>
          <input class="form-control {{ $errors->has('image_alt') ? 'is-invalid' : '' }}"
            id="image_alt"
            name="image_alt"
            type="text"
            value="{{ old('image_alt', $banner->image_alt) }}"
            required>
          @if ($errors->has('image_alt'))
            <span class="text-danger">{{ $errors->first('image_alt') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label for="url">{{ __('Url') }}</label>
          <input class="form-control {{ $errors->has('url') ? 'is-invalid' : '' }}"
            id="url"
            name="url"
            type="text"
            value="{{ old('url', $banner->url) }}">
          @if ($errors->has('url'))
            <span class="text-danger">{{ $errors->first('url') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <button class="btn btn-primary"
            type="submit">
            {{ __('Update') }}
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('scripts')
  <script>
    Dropzone.options.imageDropzone = {
      url: '{{ route('admin.banners.storeMedia') }}',
      maxFilesize: 2, // MB
      acceptedFiles: '.jpeg,.jpg,.png,.gif',
      maxFiles: 1,
      addRemoveLinks: true,
      headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      },
      params: {
        size: 2,
        width: 4096,
        height: 4096
      },
      success: function(file, response) {
        $('form').find('input[name="image"]').remove()
        $('form').append('<input type="hidden" name="image" value="' + response.name + '">')
      },
      removedfile: function(file) {
        file.previewElement.remove()
        if (file.status !== 'error') {
          $('form').find('input[name="image"]').remove()
          this.options.maxFiles = this.options.maxFiles + 1
        }
      },
      init: function() {
        @if (isset($banner) && $banner->image)
          var file = {!! json_encode($banner->image) !!}
          this.options.addedfile.call(this, file)
          this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
          file.previewElement.classList.add('dz-complete')
          $('form').append('<input type="hidden" name="image" value="' + file.file_name + '">')
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
