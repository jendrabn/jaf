<a class="btn btn-xs btn-primary"
  href="{{ route('admin.' . $crudRoutePart . '.show', $row->id) }}">
  {{ __('View') }}
</a>
<a class="btn btn-xs btn-info"
  href="{{ route('admin.' . $crudRoutePart . '.edit', $row->id) }}">
  {{ __('Edit') }}
</a>

<form style="display: inline-block;"
  action="{{ route('admin.' . $crudRoutePart . '.destroy', $row->id) }}"
  method="POST"
  onsubmit="return confirm('{{ __('Are you sure?') }}');">
  <input name="_method"
    type="hidden"
    value="DELETE">
  <input name="_token"
    type="hidden"
    value="{{ csrf_token() }}">
  <input class="btn btn-xs btn-danger"
    type="submit"
    value="{{ __('Delete') }}">
</form>
