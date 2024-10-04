@extends('layouts.admin', ['title' => 'Show User'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Show User</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-default mb-3"
               href="{{ route('admin.users.index') }}">Back to list</a>

            <table class="table table-bordered table-sm table-striped">
                <tbody>
                    <tr>
                        <th>ID</th>
                        <td>{{ $user->id }}</td>
                    </tr>

                    <tr>
                        <th>Name</th>
                        <td>{{ $user->name }}</td>
                    </tr>

                    <tr>
                        <th>Email</th>
                        <td><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
                    </tr>

                    <tr>
                        <th>Email verified at</th>
                        <td>{{ $user->email_verified_at }}</td>
                    </tr>

                    <tr>
                        <th>Role</th>
                        <td>
                            @foreach ($user->roles as $role)
                                <span class="badge badge-info rounded-0">{{ $role->name }}</span>
                            @endforeach
                        </td>
                    </tr>

                    <tr>
                        <th>Phone Number</th>
                        <td><a href="https://wa.me/{{ $user->phone }}"
                               target="_blank">{{ $user->phone }}</a></td>
                    </tr>

                    <tr>
                        <th>Gender</th>
                        <td>{{ $user->sex_label }}</td>
                    </tr>

                    <tr>
                        <th>Birth Date</th>
                        <td>{{ $user->birth_date }}</td>
                    </tr>

                    <tr>
                        <th>Orders Count</th>
                        <td>{{ $user->orders_count }}</td>
                    </tr>

                    <tr>
                        <th>Crated At</th>
                        <td>{{ $user->created_at }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
