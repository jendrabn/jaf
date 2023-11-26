<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class UsersController extends Controller
{
  public function index(Request $request)
  {
    if ($request->ajax()) {
      $model = User::with(['roles'])->select('users.*');

      $table = Datatables::eloquent($model)
        ->filter(function ($q) use ($request) {
          $q->when(
            $request->filled('role'),
            fn ($q) => $q->whereHas('roles', fn ($q) => $q->where('id', $request->get('role')))
          );

          $q->when(
            $request->filled('sex'),
            fn ($q) => $q->where('sex', $request->get('sex'))
          );
        }, true);

      $table->addColumn('placeholder', '&nbsp;');
      $table->addColumn('actions', '&nbsp;');

      $table->editColumn('actions', function ($row) {
        $crudRoutePart = 'users';

        return view('partials.datatablesActions', compact('crudRoutePart', 'row'));
      });

      $table->editColumn('id', function ($row) {
        return $row->id ? $row->id : '';
      });
      $table->editColumn('name', function ($row) {
        return $row->name ? $row->name : '';
      });
      $table->editColumn('email', function ($row) {
        return $row->email ? $row->email : '';
      });

      $table->editColumn('roles', function ($row) {
        $labels = [];
        foreach ($row->roles as $role) {
          $labels[] = sprintf('<span class="label label-info label-many badge badge-info">%s</span>', $role->name);
        }

        return implode(' ', $labels);
      });
      $table->editColumn('phone', function ($row) {
        return $row->phone ? $row->phone : '';
      });
      $table->editColumn('sex', function ($row) {
        return $row->sex ? User::SEX_SELECT[$row->sex] : '';
      });

      $table->rawColumns(['actions', 'placeholder', 'roles']);

      return $table->make(true);
    }

    $roles = Role::pluck('name', 'id')->prepend('All', null);

    return view('admin.users.index', compact('roles'));
  }

  public function create()
  {
    $roles = Role::pluck('name', 'id');

    return view('admin.users.create', compact('roles'));
  }

  public function store(UserRequest $request)
  {
    $validatedData = $request->validated();

    $user = User::create($validatedData);
    $user->assignRole($validatedData['roles']);

    return redirect()->route('admin.users.index');
  }

  public function edit(User $user)
  {
    $roles = Role::pluck('name', 'id');

    $user->load('roles');

    return view('admin.users.edit', compact('roles', 'user'));
  }

  public function update(UserRequest $request, User $user)
  {
    $validatedData = $request->validated();

    $user->update($validatedData);
    $user->syncRoles($validatedData['roles']);

    return redirect()->route('admin.users.index');
  }

  public function show(User $user)
  {
    $user->load('roles');

    return view('admin.users.show', compact('user'));
  }

  public function destroy(User $user)
  {
    $user->delete();

    return back();
  }

  public function massDestroy(UserRequest $request)
  {
    $users = User::find($request->validated('ids'));

    foreach ($users as $user) {
      $user->delete();
    }

    return response(null, Response::HTTP_NO_CONTENT);
  }
}
