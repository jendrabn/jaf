@extends('layouts.admin')
@section('content')
  <div class="row"
    style="margin-bottom: 10px;">
    <div class="col-lg-12">
      <a class="btn btn-success"
        href="{{ route('admin.banks.create') }}">
        {{ __('Add') }} {{ __('Bank') }}
      </a>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      {{ __('Bank') }} {{ __('List') }}
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table-bordered table-striped table-hover datatable datatable-Bank table">
          <thead>
            <tr>
              <th width="10">

              </th>
              <th>
                {{ __('ID') }}
              </th>
              <th>
                {{ __('Bank Logo') }}
              </th>
              <th>
                {{ __('Bank Name') }}
              </th>
              <th>
                {{ __('Bank Code') }}
              </th>
              <th>
                {{ __('Account Name') }}
              </th>
              <th>
                {{ __('Account Number') }}
              </th>
              <th>
                &nbsp;
              </th>
            </tr>
          </thead>
          <tbody>
            @foreach ($banks as $key => $bank)
              <tr data-entry-id="{{ $bank->id }}">
                <td>

                </td>
                <td>
                  {{ $bank->id ?? '' }}
                </td>
                <td>
                  @if ($bank->logo)
                    <a href="{{ $bank->logo->getUrl() }}"
                      style="display: inline-block"
                      target="_blank">
                      <img src="{{ $bank->logo->getUrl('thumb') }}">
                    </a>
                  @endif
                </td>
                <td>
                  {{ $bank->name ?? '' }}
                </td>
                <td>
                  {{ $bank->code ?? '' }}
                </td>
                <td>
                  {{ $bank->account_name ?? '' }}
                </td>
                <td>
                  {{ $bank->account_number ?? '' }}
                </td>
                <td>
                  <a class="btn btn-xs btn-info"
                    href="{{ route('admin.banks.edit', $bank->id) }}">
                    {{ __('Edit') }}
                  </a>

                  <form style="display: inline-block;"
                    action="{{ route('admin.banks.destroy', $bank->id) }}"
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
        url: "{{ route('admin.banks.massDestroy') }}",
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

      let table = $('.datatable-Bank:not(.ajaxTable)').DataTable({
        buttons: dtButtons
      });

      $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e) {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      });
    });
  </script>
@endsection
