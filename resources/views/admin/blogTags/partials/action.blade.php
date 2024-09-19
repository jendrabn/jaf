<div class="btn-group btn-group-xs"
     role="group">
    <button class="btn btn-info btn-edit"
            data-url="{{ route('admin.blog-tags.update', $id) }}">
        Edit
    </button>
    <button class="btn btn-danger btn-delete"
            data-url="{{ route('admin.blog-tags.destroy', $id) }}">
        Delete
    </button>
</div>
