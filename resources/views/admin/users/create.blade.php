@extends('layouts.admin', ['title' => 'Create User'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create User</h3>
        </div>

        <div class="card-body">
            <a class="btn btn-default mb-3"
               href="{{ route('admin.users.index') }}">Back to list</a>

            <form action="{{ route('admin.users.store') }}"
                  enctype="multipart/form-data"
                  method="POST">
                @csrf

                <div class="form-group">
                    <label class="required"
                           id="_name">Name</label>
                    <input autofocus
                           class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           id="_name"
                           name="name"
                           required
                           type="text"
                           value="{{ old('name', '') }}">
                    @if ($errors->has('name'))
                        <span class="invalid-feedback">{{ $errors->first('name') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label class="required"
                           for="_email">Email</label>
                    <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           id="_email"
                           name="email"
                           required
                           type="email"
                           value="{{ old('email') }}">
                    @if ($errors->has('email'))
                        <span class="invalid-feedback">{{ $errors->first('email') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label class="required"
                           for="_password">Password</label>
                    <input class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           id="_password"
                           name="password"
                           required
                           type="password">
                    @if ($errors->has('password'))
                        <span class="invalid-feedback">{{ $errors->first('password') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label class="required"
                           for="_roles">Roles</label>
                    <div style="padding-bottom: 4px">
                        <span class="btn btn-info btn-xs select-all"
                              style="border-radius: 0">Select all</span>
                        <span class="btn btn-info btn-xs deselect-all"
                              style="border-radius: 0">Deselect all</span>
                    </div>
                    <select class="form-control select2 {{ $errors->has('roles') ? 'is-invalid' : '' }}"
                            id="_roles"
                            multiple
                            name="roles[]"
                            required>
                        @foreach ($roles as $id => $role)
                            <option @selected(in_array($id, old('roles', [])))
                                    value="{{ $role }}">{{ $role }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('roles'))
                        <span class="invalid-feedback">{{ $errors->first('roles') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="_phone">Phone Number</label>
                    <input class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                           id="_phone"
                           name="phone"
                           type="text"
                           value="{{ old('phone', '') }}">
                    @if ($errors->has('phone'))
                        <span class="invalid-feedback">{{ $errors->first('phone') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="_sex">Gender</label>
                    <select class="form-control select2 {{ $errors->has('sex') ? 'is-invalid' : '' }}"
                            id="_sex"
                            name="sex">
                        <option @selected(old('sex', null) === null)
                                disabled
                                value>---</option>
                        @foreach (App\Models\User::SEX_SELECT as $key => $label)
                            <option @selected(old('sex', null) === $key)
                                    value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('sex'))
                        <span class="invalid-feedback">{{ $errors->first('sex') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="_birth_date">Birth Date</label>
                    <input autocomplete="off"
                           class="form-control date datetimepicker-input {{ $errors->has('birth_date') ? 'is-invalid' : '' }}"
                           data-toggle="datetimepicker"
                           id="_birth_date"
                           name="birth_date"
                           placeholder="DD-MM-YYYY"
                           type="text"
                           value="{{ old('birth_date') }}">
                    @if ($errors->has('birth_date'))
                        <span class="invalid-feedback">{{ $errors->first('birth_date') }}</span>
                    @endif
                </div>

                <button class="btn btn-primary"
                        type="submit">
                    <i class="fa-solid fa-floppy-disk"></i> Save
                </button>
            </form>
        </div>
    </div>
@endsection
