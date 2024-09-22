<div class="btn-group btn-group-xs"
     role="group">
    <a class="btn btn-primary"
       href="{{ route('admin.products.show', $id) }}">
        View
    </a>
    <a class="btn btn-info"
       href="{{ route('admin.products.edit', $id) }}">
        Edit
    </a>
    <a class="btn btn-danger btn-delete"
       href="{{ route('admin.products.destroy', $id) }}">
        Delete
    </a>
</div>
