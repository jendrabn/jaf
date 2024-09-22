<div class="btn-group btn-group-xs"
     role="group">
    <a class="btn btn-primary"
       href="{{ route('admin.orders.show', $id) }}">
        View
    </a>
    <a class="btn btn-danger btn-delete"
       href="{{ route('admin.orders.destroy', $id) }}">
        Delete
    </a>
</div>
