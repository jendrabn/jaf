<input @checked($is_publish)
       class="check-published"
       data-url="{{ route('admin.blogs.published', $id) }}"
       type="checkbox" />
