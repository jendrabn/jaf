<div class="btn-group btn-group-xs"
     role="group">
    {{-- <a class="btn btn-primary"
       href="">
        View
    </a> --}}
    <a class="btn btn-info"
       href="{{ route('admin.banners.edit', $id) }}">
        Edit
    </a>
    <a class="btn btn-danger btn-delete"
       href="{{ route('admin.banners.destroy', $id) }}">
        Delete
    </a>
</div>
