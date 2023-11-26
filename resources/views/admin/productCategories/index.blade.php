@extends('layouts.admin')
@section('content')
  <div class="row"
    style="margin-bottom: 10px;">
    <div class="col-lg-12">
      <a class="btn btn-success"
        href="{{ route('admin.product-categories.create') }}">
        {{ __('Add') }} {{ __('Category') }}
      </a>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      {{ __('Category') }} {{ __('List') }}
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table-bordered table-striped table-hover datatable datatable-ProductCategory table">
          <thead>
            <tr>
              <th width="10">

              </th>
              <th>
                {{ __('ID') }}
              </th>
              <th>
                {{ __('Category Name') }}
              </th>
              <th>
                {{ __('Category Slug') }}
              </th>
              <th>
                &nbsp;
              </th>
            </tr>
          </thead>
          <tbody>
            @foreach ($productCategories as $key => $productCategory)
              <tr data-entry-id="{{ $productCategory->id }}">
                <td>

                </td>
                <td>
                  {{ $productCategory->id ?? '' }}
                </td>
                <td>
                  {{ $productCategory->name ?? '' }}
                </td>
                <td>
                  {{ $productCategory->slug ?? '' }}
                </td>
                <td>

                  <a class="btn btn-xs btn-info"
                    href="{{ route('admin.product-categories.edit', $productCategory->id) }}">
                    {{ __('Edit') }}
                  </a>

                  <form style="display: inline-block;"
                    action="{{ route('admin.product-categories.destroy', $productCategory->id) }}"
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
        url: "{{ route('admin.product-categories.massDestroy') }}",
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
          [1, 'desc']
        ],
        pageLength: 25,
      });

      let table = $('.datatable-ProductCategory:not(.ajaxTable)').DataTable({
        buttons: dtButtons
      });

      table.column(3).visible(false);

      $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e) {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      });
    });
  </script>
@endsection
