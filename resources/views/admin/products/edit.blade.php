@extends('layouts.admin')
@section('content')
  <div class="card">
    <div class="card-header">
      {{ __('Edit') }} {{ __('Product') }}
    </div>

    <div class="card-body">
      <form method="POST"
        action="{{ route('admin.products.update', [$product->id]) }}"
        enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="form-group">
          <label class="required"
            for="images">{{ __('Product Images') }}</label>
          <div class="needsclick dropzone {{ $errors->has('images') ? 'is-invalid' : '' }}"
            id="images-dropzone">
          </div>
          @if ($errors->has('images'))
            <span class="text-danger">{{ $errors->first('images') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label class="required"
            for="name">{{ __('Product Name') }}</label>
          <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $product->name) }}"
            required>
          @if ($errors->has('name'))
            <span class="text-danger">{{ $errors->first('name') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label class="required"
            for="product_category_id">{{ __('Category') }}</label>
          <select class="form-control select2 {{ $errors->has('product_category') ? 'is-invalid' : '' }}"
            id="product_category_id"
            name="product_category_id"
            required>
            @foreach ($product_categories as $id => $entry)
              <option value="{{ $id }}"
                {{ (old('product_category_id') ? old('product_category_id') : $product->category->id ?? '') == $id ? 'selected' : '' }}>
                {{ $entry }}</option>
            @endforeach
          </select>
          @if ($errors->has('product_category'))
            <span class="text-danger">{{ $errors->first('product_category') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label class="required"
            for="description">{{ __('Product Description') }}</label>
          <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
            id="description"
            name="description">{!! old('description', $product->description) !!}</textarea>
          @if ($errors->has('description'))
            <span class="text-danger">{{ $errors->first('description') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label for="product_brand_id">{{ __('Brand') }}</label>
          <select class="form-control select2 {{ $errors->has('product_brand') ? 'is-invalid' : '' }}"
            id="product_brand_id"
            name="product_brand_id">
            @foreach ($product_brands as $id => $entry)
              <option value="{{ $id }}"
                {{ (old('product_brand_id') ? old('product_brand_id') : $product->brand->id ?? '') == $id ? 'selected' : '' }}>
                {{ $entry }}</option>
            @endforeach
          </select>
          @if ($errors->has('product_brand'))
            <span class="text-danger">{{ $errors->first('product_brand') }}</span>
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
            @foreach (App\Models\Product::SEX_SELECT as $key => $label)
              <option value="{{ $key }}"
                {{ old('sex', $product->sex) === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          @if ($errors->has('sex'))
            <span class="text-danger">{{ $errors->first('sex') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label class="required"
            for="price">{{ __('Price') }}</label>
          <input class="form-control {{ $errors->has('price') ? 'is-invalid' : '' }}"
            id="price"
            name="price"
            type="number"
            value="{{ old('price', $product->price) }}"
            step="0.01"
            required>
          @if ($errors->has('price'))
            <span class="text-danger">{{ $errors->first('price') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label class="required"
            for="stock">{{ __('Stock') }}</label>
          <input class="form-control {{ $errors->has('stock') ? 'is-invalid' : '' }}"
            id="stock"
            name="stock"
            type="number"
            value="{{ old('stock', $product->stock) }}"
            step="1"
            required>
          @if ($errors->has('stock'))
            <span class="text-danger">{{ $errors->first('stock') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <label class="required"
            for="weight">{{ __('Weight') }}</label>
          <input class="form-control {{ $errors->has('weight') ? 'is-invalid' : '' }}"
            id="weight"
            name="weight"
            type="number"
            value="{{ old('weight', $product->weight) }}"
            step="1"
            required>
          @if ($errors->has('weight'))
            <span class="text-danger">{{ $errors->first('weight') }}</span>
          @endif
          <span class="help-block">{{ __() }}</span>
        </div>
        <div class="form-group">
          <div class="form-check {{ $errors->has('is_publish') ? 'is-invalid' : '' }}">
            <input class="form-check-input"
              id="is_publish"
              name="is_publish"
              type="checkbox"
              value="1"
              {{ $product->is_publish || old('is_publish', 0) === 1 ? 'checked' : '' }}>
            <label class="form-check-label"
              for="is_publish">{{ __('Publish') }}</label>
          </div>
          @if ($errors->has('is_publish'))
            <span class="text-danger">{{ $errors->first('is_publish') }}</span>
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
    var uploadedImagesMap = {}
    Dropzone.options.imagesDropzone = {
      url: '{{ route('admin.products.storeMedia') }}',
      maxFilesize: 1, // MB
      acceptedFiles: '.jpeg,.jpg,.png,.gif',
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
        $('form').append('<input type="hidden" name="images[]" value="' + response.name + '">')
        uploadedImagesMap[file.name] = response.name
      },
      removedfile: function(file) {
        console.log(file)
        file.previewElement.remove()
        var name = ''
        if (typeof file.file_name !== 'undefined') {
          name = file.file_name
        } else {
          name = uploadedImagesMap[file.name]
        }
        $('form').find('input[name="images[]"][value="' + name + '"]').remove()
      },
      init: function() {
        @if (isset($product) && $product->images)
          var files = {!! json_encode($product->images) !!}
          for (var i in files) {
            var file = files[i]
            this.options.addedfile.call(this, file)
            this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
            file.previewElement.classList.add('dz-complete')
            $('form').append('<input type="hidden" name="images[]" value="' + file.file_name + '">')
          }
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
  <script>
    $(document).ready(function() {
      function SimpleUploadAdapter(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = function(loader) {
          return {
            upload: function() {
              return loader.file
                .then(function(file) {
                  return new Promise(function(resolve, reject) {
                    // Init request
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '{{ route('admin.products.storeCKEditorImages') }}', true);
                    xhr.setRequestHeader('x-csrf-token', window._token);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.responseType = 'json';

                    // Init listeners
                    var genericErrorText = `Couldn't upload file: ${ file.name }.`;
                    xhr.addEventListener('error', function() {
                      reject(genericErrorText)
                    });
                    xhr.addEventListener('abort', function() {
                      reject()
                    });
                    xhr.addEventListener('load', function() {
                      var response = xhr.response;

                      if (!response || xhr.status !== 201) {
                        return reject(response && response.message ?
                          `${genericErrorText}\n${xhr.status} ${response.message}` :
                          `${genericErrorText}\n ${xhr.status} ${xhr.statusText}`);
                      }

                      $('form').append('<input type="hidden" name="ck-media[]" value="' + response.id +
                        '">');

                      resolve({
                        default: response.url
                      });
                    });

                    if (xhr.upload) {
                      xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                          loader.uploadTotal = e.total;
                          loader.uploaded = e.loaded;
                        }
                      });
                    }

                    // Send request
                    var data = new FormData();
                    data.append('upload', file);
                    data.append('crud_id', '{{ $product->id ?? 0 }}');
                    xhr.send(data);
                  });
                })
            }
          };
        }
      }

      var allEditors = document.querySelectorAll('.ckeditor');
      for (var i = 0; i < allEditors.length; ++i) {
        ClassicEditor.create(
          allEditors[i], {
            extraPlugins: [SimpleUploadAdapter]
          }
        );
      }
    });
  </script>
@endsection
