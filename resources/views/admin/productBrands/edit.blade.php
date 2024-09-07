@extends('layouts.admin', ['title' => 'Edit Product Brand'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Product Brand</h3>
        </div>

        <div class="card-body">
            <a class="btn btn-default mb-3"
               href="{{ route('admin.product-brands.index') }}">
                Back to list
            </a>

            <form action="{{ route('admin.product-brands.update', [$productBrand->id]) }}"
                  enctype="multipart/form-data"
                  method="POST">
                @method('PUT')
                @csrf

                <div class="form-group">
                    <label class="required"
                           for="_name">Name</label>
                    <input autofocus
                           class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           id="_name"
                           name="name"
                           required
                           type="text"
                           value="{{ old('name', $productBrand->name) }}">
                    @if ($errors->has('name'))
                        <span class="invalid-feedback">{{ $errors->first('name') }}</span>
                    @endif
                </div>

                <button class="btn btn-primary"
                        type="submit">
                    <i class="fas fa-save"></i> Update
                </button>
            </form>
        </div>
    </div>
@endsection
