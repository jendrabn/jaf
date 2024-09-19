<div class="btn-group btn-group-xs"
     role="group">
    <button class="btn btn-info btn-edit"
            data-url="{{ route('admin.blog-categories.update', $id) }}">
        Edit
    </button>
    <button class="btn btn-danger btn-delete"
            data-url="{{ route('admin.blog-categories.destroy', $id) }}">
        Delete
    </button>
</div>
