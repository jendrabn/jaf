<div class="btn-group btn-group-xs"
     role="group">
    <a class="btn btn-primary"
       href="{{ route('admin.blogs.show', $id) }}">
        View
    </a>
    <a class="btn btn-info"
       href="{{ route('admin.blogs.edit', $id) }}">
        Edit
    </a>
    <button class="btn btn-danger btn-delete"
            data-url="{{ route('admin.blogs.destroy', $id) }}">
        Delete
    </button>
</div>
