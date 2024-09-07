@extends('layouts.admin', ['title' => 'Create Product Brand'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create Product</h3>
        </div>

        <div class="card-body">
            <a class="btn btn-default mb-3"
               href="{{ route('admin.product-brands.index') }}">
                Back to list
            </a>

            <form action="{{ route('admin.product-brands.store') }}"
                  enctype="multipart/form-data"
                  method="POST">
                @csrf

                <div class="form-group">
                    <label class="required">Name</label>
                    <input autofocus
                           class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           name="name"
                           required
                           type="text"
                           value="{{ old('name') }}">
                    @if ($errors->has('name'))
                        <span class="invalid-feedback">{{ $errors->first('name') }}</span>
                    @endif
                </div>

                <button class="btn btn-primary"
                        type="submit">
                    <i class="fas fa-save"></i> Save
                </button>
            </form>
        </div>
    </div>
@endsection
