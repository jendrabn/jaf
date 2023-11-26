@extends('layouts.admin')
@section('content')
  <div class="card">
    <div class="card-body">
      <form id="form-filter">
        <div class="row">
          <div class="col-sm-12 col-md-4 col-md-3">
            <div class="form-group">
              <label for="status">Status</label>
              <select class="form-control select2"
                id="status"
                name="status"
                strict="true">
                <option value
                  selected>{{ __('All') }}</option>
                @foreach (App\Models\Order::STATUSES as $key => $status)
                  <option value="{{ $key }}">{{ $status['label'] }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        <div class="form-group">
          <button class="btn btn-primary mr-1"
            type="submit">
            {{ __('Search') }}
          </button>

          <button class="btn btn-light"
            type="reset">
            {{ __('Reset') }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      {{ __('Order') }} {{ __('List') }}
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table-bordered table-striped table-hover datatable ajaxTable datatable-Order table">
          <thead>
            <tr>
              <th width="10">

              </th>
              <th>
                {{ __('ID') }}
              </th>
              <th>
                {{ __('User') }}
              </th>
              <th>
                {{ __('Product(s)') }}
              </th>
              <th>
                {{ __('Amount') }}
              </th>
              <th>
                {{ __('Shipping') }}
              </th>
              <th>
                {{ __('Status') }}
              </th>
              <th>
                {{ __('Created at') }}
              </th>
              <th>
                {{ __('Confirmed at') }}
              </th>
              <th>
                {{ __('Completed at') }}
              </th>
              <th>
                {{ __('Cancelled at') }}
              </th>
              <th>
                &nbsp;
              </th>
            </tr>
          </thead>
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
      let formFilter = $('#form-filter');
      let dtOverrideGlobals = {
        buttons: dtButtons,
        processing: true,
        serverSide: true,
        retrieve: true,
        aaSorting: [],
        ajax: {
          url: "{{ route('admin.orders.index') }}",
          data: function(d) {
            $.each(formFilter.serializeArray(), function(key, val) {
              d[val.name] = val.value;
            })
          }
        },
        columns: [{
            data: 'placeholder',
            name: 'placeholder'
          },
          {
            data: 'id',
            name: 'id'
          },
          {
            data: 'user',
            name: 'user.name'
          },
          {
            data: 'items',
            name: 'items',
            orderable: false,
            searchable: false
          },
          {
            data: 'amount',
            name: 'invoice.amount'
          },
          {
            data: 'shipping',
            name: 'shipping.tracking_number',
            orderable: false,
          },
          {
            data: 'status',
            name: 'status'
          },
          {
            data: 'created_at',
            name: 'created_at',
            visible: false
          },
          {
            data: 'confirmed_at',
            name: 'confirmed_at',
            visible: false
          },
          {
            data: 'completed_at',
            name: 'completed_at',
            visible: false
          },
          {
            data: 'cancelled_at',
            name: 'cancelled_at',
            visible: false
          },
          {
            data: 'actions',
            name: '{{ __('Actions') }}'
          }
        ],
        orderCellsTop: true,
        order: [
          [1, 'desc']
        ],
        pageLength: 25,
      };

      let table = $('.datatable-Order').DataTable(dtOverrideGlobals);
      $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e) {
        $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
      });

      let visibleColumnsIndexes = null;

      $('.datatable thead').on('input', '.search', function() {
        let strict = $(this).attr('strict') || false
        let value = strict && this.value ? "^" + this.value + "$" : this.value

        let index = $(this).parent().index()
        if (visibleColumnsIndexes !== null) {
          index = visibleColumnsIndexes[index]
        }

        table
          .column(index)
          .search(value, strict)
          .draw()
      });

      table.on('column-visibility.dt', function(e, settings, column, state) {
        visibleColumnsIndexes = []
        table.columns(":visible").every(function(colIdx) {
          visibleColumnsIndexes.push(colIdx);
        });
      })

      formFilter.on('submit', function(e) {
        e.preventDefault()

        table.ajax.reload()
      })

      formFilter.on('reset', function(e) {
        e.preventDefault()

        $(this).find('select').each(function() {
          $(this).val(null).trigger('change')
        })

        table.ajax.reload()
      })
    });
  </script>
@endsection
