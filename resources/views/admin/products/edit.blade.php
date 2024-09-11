@extends('layouts.admin', ['title' => 'Edit Product'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Product</h3>
        </div>

        <div class="card-body">
            <a class="btn btn-default mb-3"
               href="{{ route('admin.products.index') }}">Back to list</a>

            <form action="{{ route('admin.products.update', [$product->id]) }}"
                  enctype="multipart/form-data"
                  method="POST">
                @method('PUT')
                @csrf

                <div class="form-group">
                    <label class="required"
                           for="_images">Product Images</label>
                    <div class="needsclick dropzone {{ $errors->has('images') ? 'is-invalid' : '' }}"
                         id="images-dropzone">
                    </div>
                    @if ($errors->has('images'))
                        <span class="invalid-feedback">{{ $errors->first('images') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label class="required"
                           for="_name">Product Name</label>
                    <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           id="_name"
                           name="name"
                           required
                           type="text"
                           value="{{ old('name', $product->name) }}">
                    @if ($errors->has('name'))
                        <span class="invalid-feedback">{{ $errors->first('name') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label class="required"
                           for="_product_category_id">Category</label>
                    <select class="form-control select2 {{ $errors->has('product_category') ? 'is-invalid' : '' }}"
                            id="_product_category_id"
                            name="product_category_id"
                            required>
                        @foreach ($product_categories as $id => $entry)
                            <option @selected((old('product_category_id') ? old('product_category_id') : $product->category->id ?? '') == $id)
                                    value="{{ $id }}">
                                {{ $entry }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('product_category'))
                        <span class="invalid-feedback">{{ $errors->first('product_category') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label class="required"
                           for="_description">Product Description'</label>
                    <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                              id="_description"
                              name="description">{!! old('description', $product->description) !!}</textarea>
                    @if ($errors->has('description'))
                        <span class="invalid-feedback">{{ $errors->first('description') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="_product_brand_id">Brand</label>
                    <select class="form-control select2 {{ $errors->has('product_brand') ? 'is-invalid' : '' }}"
                            id="_product_brand_id"
                            name="product_brand_id">
                        @foreach ($product_brands as $id => $entry)
                            <option @selected((old('product_brand_id') ? old('product_brand_id') : $product->brand->id ?? '') == $id)
                                    value="{{ $id }}">
                                {{ $entry }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('product_brand'))
                        <span class="invalid-feedback">{{ $errors->first('product_brand') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="_sex">Gender</label>
                    <select class="form-control select2 {{ $errors->has('sex') ? 'is-invalid' : '' }}"
                            id="_sex"
                            name="sex">
                        <option @selected(old('sex', null) === null)
                                value>---</option>
                        @foreach (App\Models\Product::SEX_SELECT as $key => $label)
                            <option @selected(old('sex', $product->sex) === $key)
                                    value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('sex'))
                        <span class="invalid-feedback">{{ $errors->first('sex') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label class="required"
                           for="_price">Price</label>
                    <input class="form-control {{ $errors->has('price') ? 'is-invalid' : '' }}"
                           id="_price"
                           name="price"
                           required
                           step="0.01"
                           type="number"
                           value="{{ old('price', $product->price) }}">
                    @if ($errors->has('price'))
                        <span class="invalid-feedback">{{ $errors->first('price') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label class="required"
                           for="_stock">Stock</label>
                    <input class="form-control {{ $errors->has('stock') ? 'is-invalid' : '' }}"
                           id="_stock"
                           name="stock"
                           required
                           step="1"
                           type="number"
                           value="{{ old('stock', $product->stock) }}" />
                    @if ($errors->has('stock'))
                        <span class="invalid-feedback">{{ $errors->first('stock') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label class="required"
                           for="_weight">Weight</label>
                    <input class="form-control {{ $errors->has('weight') ? 'is-invalid' : '' }}"
                           id="_weight"
                           name="weight"
                           required
                           step="1"
                           type="number"
                           value="{{ old('weight', $product->weight) }}">
                    @if ($errors->has('weight'))
                        <span class="invalid-feedback">{{ $errors->first('weight') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox {{ $errors->has('is_publish') ? 'is-invalid' : '' }}">
                        <input @checked($product->is_publish || old('is_publish', 0) === 1)
                               class="custom-control-input"
                               id="_is_publish"
                               name="is_publish"
                               type="checkbox" />
                        <label class="custom-control-label"
                               for="_is_publish">Publish Status</label>
                    </div>

                    @if ($errors->has('is_publish'))
                        <span class="invalid-feedback">{{ $errors->first('is_publish') }}</span>
                    @endif
                </div>

                <button class="btn btn-primary"
                        type="submit">
                    <i class="fa-solid fa-floppy-disk"></i> Update
                </button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor5/43.0.0/ckeditor.min.js"></script>

    <script>
        let uploadedImagesMap = {}

        Dropzone.options.imagesDropzone = {
            url: '{{ route('admin.products.storeMedia') }}',
            maxFilesize: 1, // MB
            acceptedFiles: '.jpeg,.jpg,.png',
            addRemoveLinks: true,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            params: {
                size: 1,
                width: 2048,
                height: 2048
            },
            maxFiles: 5,
            success: function(file, response) {
                $('form').append('<input type="hidden" name="images[]" value="' + response.name + '">')
                uploadedImagesMap[file.name] = response.name
            },
            removedfile: function(file) {
                console.log(file)
                file.previewElement.remove()
                let name = ''
                if (typeof file.file_name !== 'undefined') {
                    name = file.file_name
                } else {
                    name = uploadedImagesMap[file.name]
                }
                $('form').find('input[name="images[]"][value="' + name + '"]').remove()
            },
            init: function() {
                @if (isset($product) && $product->images)
                    let files = {!! json_encode($product->images) !!}
                    for (let i in files) {
                        let file = files[i]
                        this.options.addedfile.call(this, file)
                        this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
                        file.previewElement.classList.add('dz-complete')
                        $('form').append('<input type="hidden" name="images[]" value="' + file.file_name + '">')
                    }
                @endif
            },
            error: function(file, response) {
                let message = '';

                if ($.type(response) === 'string') {
                    message = response //dropzone sends it's own error messages in string
                } else {
                    message = response.errors.file
                }
                file.previewElement.classList.add('dz-error')
                _ref = file.previewElement.querySelectorAll('[data-dz-errormessage]')
                _results = []
                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                    node = _ref[_i]
                    _results.push(node.textContent = message)
                }

                toastr.error(message, 'Error');

                return _results
            }
        }

        ClassicEditor
            .create(document.querySelector('.ckeditor'), {
                ckfinder: {
                    uploadUrl: '{{ route('admin.products.storeCKEditorImages') . '?_token=' . csrf_token() }}',
                }
            })
            .catch(error => {
                toastr.error(error, 'Error');
            });
    </script>
@endsection
