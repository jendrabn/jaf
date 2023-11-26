@extends('layouts.admin')
@section('content')
  <div class="card">
    <div class="card-header">
      {{ __('Show') }} {{ __('Users') }}
    </div>

    <div class="card-body">
      <div class="form-group">
        <div class="form-group">
          <a class="btn btn-default"
            href="{{ route('admin.users.index') }}">
            {{ __('Back to list') }}
          </a>
        </div>
        <table class="table-bordered table-striped table">
          <tbody>
            <tr>
              <th>
                {{ __('ID') }}
              </th>
              <td>
                {{ $user->id }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Name') }}
              </th>
              <td>
                {{ $user->name }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Email') }}
              </th>
              <td>
                {{ $user->email }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Email verified at') }}
              </th>
              <td>
                {{ $user->email_verified_at }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Roles') }}
              </th>
              <td>
                @foreach ($user->roles as $key => $roles)
                  <span class="label label-info">{{ $roles->name }}</span>
                @endforeach
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Phone Number') }}
              </th>
              <td>
                {{ $user->phone }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Gender') }}
              </th>
              <td>
                {{ App\Models\User::SEX_SELECT[$user->sex] ?? '' }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Birth Date') }}
              </th>
              <td>
                {{ $user->birth_date }}
              </td>
            </tr>
          </tbody>
        </table>
        <div class="form-group">
          <a class="btn btn-default"
            href="{{ route('admin.users.index') }}">
            {{ __('Back to list') }}
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection
