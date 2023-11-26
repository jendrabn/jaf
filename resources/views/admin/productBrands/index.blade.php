@extends('layouts.admin')
@section('content')
  <div class="row"
    style="margin-bottom: 10px;">
    <div class="col-lg-12">
      <a class="btn btn-success"
        href="{{ route('admin.product-brands.create') }}">
        {{ __('Add') }} {{ __('Brand') }}
      </a>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      {{ __('Brand') }} {{ __('List') }}
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table-bordered table-striped table-hover datatable datatable-ProductBrand table">
          <thead>
            <tr>
              <th width="10">

              </th>
              <th>
                {{ __('ID') }}
              </th>
              <th>
                {{ __('Brand Name') }}
              </th>
              <th>
                {{ __('Brand Slug') }}
              </th>
              <th>
                &nbsp;
              </th>
            </tr>
          </thead>
          <tbody>
            @foreach ($productBrands as $key => $productBrand)
              <tr data-entry-id="{{ $productBrand->id }}">
                <td>

                </td>
                <td>
                  {{ $productBrand->id ?? '' }}
                </td>
                <td>
                  {{ $productBrand->name ?? '' }}
                </td>
                <td>
                  {{ $productBrand->slug ?? '' }}
                </td>
                <td>

                  <a class="btn btn-xs btn-info"
                    href="{{ route('admin.product-brands.edit', $productBrand->id) }}">
                    {{ __('Edit') }}
                  </a>

                  <form style="display: inline-block;"
                    action="{{ route('admin.product-brands.destroy', $productBrand->id) }}"
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
        url: "{{ route('admin.product-brands.massDestroy') }}",
        className: 'btn-danger',
        action: function(e, dt, node, config) {
          let ids = $.map(dt.rows({
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

      let table = $('.datatable-ProductBrand:not(.ajaxTable)').DataTable({
        buttons: dtButtons
      });

      table.column(3).visible(false)

      $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e) {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      });
    });
  </script>
@endsection
