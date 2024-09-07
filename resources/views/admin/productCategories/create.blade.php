@extends('layouts.admin', ['title' => 'Create Product Category'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create Product Category</h3>
        </div>

        <div class="card-body">
            <a class="btn btn-default mb-3"
               href="{{ route('admin.product-categories.index') }}">Back to list</a>

            <form action="{{ route('admin.product-categories.store') }}"
                  enctype="multipart/form-data"
                  method="POST">
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
                           value="{{ old('name', '') }}">
                    @if ($errors->has('name'))
                        <span class="text-danger">{{ $errors->first('name') }}</span>
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
