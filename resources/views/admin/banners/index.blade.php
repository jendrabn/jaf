@extends('layouts.admin')
@section('content')
  <div class="row"
    style="margin-bottom: 10px;">
    <div class="col-lg-12">
      <a class="btn btn-success"
        href="{{ route('admin.banners.create') }}">
        {{ __('Add') }} {{ __('Banner') }}
      </a>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      {{ __('Banner') }} {{ __('List') }}
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table-bordered table-striped table-hover datatable datatable-Banner table">
          <thead>
            <tr>
              <th width="10">

              </th>
              <th>
                {{ __('ID') }}
              </th>
              <th>
                {{ __('Banner Image') }}
              </th>
              <th>
                {{ __('Image Alt') }}
              </th>
              <th>
                {{ __('Url') }}
              </th>
              <th>
                &nbsp;
              </th>
            </tr>
          </thead>
          <tbody>
            @foreach ($banners as $key => $banner)
              <tr data-entry-id="{{ $banner->id }}">
                <td>

                </td>
                <td>
                  {{ $banner->id ?? '' }}
                </td>
                <td>
                  @if ($banner->image)
                    <a href="{{ $banner->image->getUrl() }}"
                      style="display: inline-block"
                      target="_blank">
                      <img src="{{ $banner->image->getUrl('thumb') }}">
                    </a>
                  @endif
                </td>
                <td>
                  {{ $banner->image_alt ?? '' }}
                </td>
                <td>
                  {{ $banner->url ?? '' }}
                </td>
                <td>

                  <a class="btn btn-xs btn-info"
                    href="{{ route('admin.banners.edit', $banner->id) }}">
                    {{ __('Edit') }}
                  </a>

                  <form style="display: inline-block;"
                    action="{{ route('admin.banners.destroy', $banner->id) }}"
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

                </td>

              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
@section('scripts')
  @parent
  <script>
    $(function() {
      let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons);
      let deleteButton = {
        text: '{{ __('Delete selected') }}',
        url: "{{ route('admin.banners.massDestroy') }}",
        className: 'btn-danger',
        action: function(e, dt, node, config) {
          var ids = $.map(dt.rows({
            selected: true
          }).nodes(), function(entry) {
            return $(entry).data('entry-id')
          });

          if (ids.length === 0) {
            alert('{{ __('No rows selected') }}')

            return
          }

          if (confirm('{{ __('Are you sure?') }}')) {
            $.ajax({
                headers: {
                  'x-csrf-token': _token
                },
                method: 'POST',
                url: config.url,
                data: {
                  ids: ids,
                  _method: 'DELETE'
                }
              })
              .done(function() {
                location.reload()
              })
          }
        }
      }
      dtButtons.push(deleteButton);

      $.extend(true, $.fn.dataTable.defaults, {
        orderCellsTop: true,
        order: [
          [1, 'asc']
        ],
        pageLength: 25,
      });

      let table = $('.datatable-Banner:not(.ajaxTable)').DataTable({
        buttons: dtButtons
      });

      $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e) {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      });

    })
  </script>
@endsection
