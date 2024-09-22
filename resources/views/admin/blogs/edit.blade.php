@extends('layouts.admin', ['title' => 'Edit Blog'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Blog</h3>
        </div>

        <div class="card-body">
            <a class="btn btn-default mb-3"
               href="{{ route('admin.blogs.index') }}">Back to list</a>

            <form action="{{ route('admin.blogs.update', $blog->id) }}"
                  enctype="multipart/form-data"
                  method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="required"
                           for="_title">Title</label>
                    <input autofocus
                           class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                           id="_title"
                           name="title"
                           required
                           type="text"
                           value="{{ old('title', $blog->title) }}">
                    @if ($errors->has('title'))
                        <div class="invalid-feedback">{{ $errors->first('title') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="required">Featured Image</label>
                    <div class="needsclick dropzone {{ $errors->has('featured_image') ? 'is-invalid' : '' }}"
                         id="images-dropzone">
                    </div>
                    @if ($errors->has('featured_image'))
                        <div class="invalid-feedback">{{ $errors->first('featured_image') }}</div>
                    @endif

                    <input class="form-control form-control-sm mt-3"
                           name="featured_image_description"
                           placeholder="Featured Image Description"
                           type="text"
                           value="{{ old('featured_image_description', $blog->featured_image_description) }}">
                    @if ($errors->has('featured_image_description'))
                        <div class="invalid-feedback">{{ $errors->first('featured_image_description') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="required"
                           for="_user_id">Author</label>
                    <select class="form-control select2 {{ $errors->has('user_id') ? 'is-invalid' : '' }}"
                            id="_user_id"
                            name="user_id"
                            required>
                        @foreach ($authors as $id => $name)
                            <option @selected(old('user_id', $blog->user_id) == $id)
                                    value="{{ $id }}">{{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @if ($errors->has('user_id'))
                        <div class="invalid-feedback">{{ $errors->first('user_id') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="required"
                           for="_blog_category_id">Category</label>
                    <select class="form-control select2 {{ $errors->has('blog_category_id') ? 'is-invalid' : '' }}"
                            id="_blog_category_id"
                            name="blog_category_id"
                            required>
                        @foreach ($categories as $id => $name)
                            <option @selected(old('blog_category_id', $blog->blog_category_id) == $id)
                                    value="{{ $id }}">{{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @if ($errors->has('blog_category_id'))
                        <div class="invalid-feedback">{{ $errors->first('category_id') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="required"
                           for="_content">Content</label>
                    <textarea class="form-control ckeditor {{ $errors->has('content') ? 'is-invalid' : '' }}"
                              id="_content"
                              name="content">{!! old('content', $blog->content) !!}</textarea>
                    @if ($errors->has('content'))
                        <div class="invalid-feedback">{{ $errors->first('content') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <label for="_tag_ids">Tags</label>
                    <div style="padding-bottom: 4px">
                        <span class="btn btn-secondary btn-xs select-all"
                              style="border-radius: 0">Select all</span>
                        <span class="btn btn-secondary btn-xs deselect-all"
                              style="border-radius: 0">Deselect all</span>
                    </div>
                    <select class="form-control select2 {{ $errors->has('tag_ids') ? 'is-invalid' : '' }}"
                            id="_tag_ids"
                            multiple
                            name="tag_ids[]"
                            style="width: 100%;">
                        @foreach ($tags as $id => $name)
                            <option @selected(in_array($id, old('tag_ids', $blog->tags->pluck('id')->toArray())))
                                    value="{{ $id }}">
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @if ($errors->has('tag_ids'))
                        <div class="invalid-feedback">{{ $errors->first('tag_ids') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input @checked(old('is_publish', $blog->is_publish))
                               class="custom-control-input {{ $errors->has('is_publish') ? 'is-invalid' : '' }}"
                               id="is_publish"
                               name="is_publish"
                               type="checkbox">
                        <label class="custom-control-label"
                               for="is_publish">Publish Status</label>
                        @if ($errors->has('is_publish'))
                            <div class="invalid-feedback">{{ $errors->first('is_publish') }}</div>
                        @endif
                    </div>
                </div>

                <button class="btn btn-primary"
                        type="submit">
                    <i class="fas fa-floppy-disk"></i> Save Changes
                </button>

            </form>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor5/43.0.0/ckeditor.min.js"></script>

    <script>
        Dropzone.options.imagesDropzone = {
            url: '{{ route('admin.blogs.storeMedia') }}',
            maxFilesize: 2, // MB
            acceptedFiles: '.jpeg,.jpg,.png',
            addRemoveLinks: true,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            params: {
                size: 2,
                width: 4000,
                height: 3000
            },
            maxFiles: 1,
            success: function(file, response) {
                $('form').find('input[name="featured_image"]').remove()
                $('form').append('<input type="hidden" name="featured_image" value="' + response.name + '">')
            },
            removedfile: function(file) {
                file.previewElement.remove()

                if (file.status !== 'error') {
                    $('form').find('input[name="featured_image"]').remove()
                    this.options.maxFiles = this.options.maxFiles + 1
                }
            },
            init: function() {
                @if (isset($blog) && $blog->featured_image)
                    let file = {!! json_encode($blog->featured_image) !!}
                    this.options.addedfile.call(this, file)
                    this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
                    file.previewElement.classList.add('dz-complete')
                    $('form').append('<input type="hidden" name="featured_image" value="' + file.file_name + '">')
                    this.options.maxFiles = this.options.maxFiles - 1
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
                    uploadUrl: '{{ route('admin.blogs.storeCKEditorImages') . '?_token=' . csrf_token() }}',
                }
            })
            .catch(error => {
                toastr.error(error, 'Error');
            });
    </script>
@endsection
