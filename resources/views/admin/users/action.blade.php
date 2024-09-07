<div class="btn-group btn-group-xs"
     role="group">
    <a class="btn btn-primary"
       href="{{ route('admin.users.show', $id) }}">
        View
    </a>
    <a class="btn btn-info"
       href="{{ route('admin.users.edit', $id) }}">
        Edit
    </a>
    <a class="btn btn-danger btn-delete"
       href="{{ route('admin.users.destroy', $id) }}">
        Delete
    </a>
</div>
