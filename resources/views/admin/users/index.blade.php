@extends('layouts.admin')
@section('content')
  <div class="row"
    style="margin-bottom: 10px;">
    <div class="col-lg-12">
      <a class="btn btn-success"
        href="{{ route('admin.users.create') }}">
        {{ __('Add') }} {{ __('User') }}
      </a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">

      <form id="form-filter">
        <div class="row">
          <div class="col-12 col-md-4 col-lg-3">
            <div class="form-group">
              <label for="role">{{ __('Role') }}</label>
              <select class="form-control select2"
                id="role"
                name="role">
                @foreach ($roles as $id => $name)
                  <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="col-12 col-md-4 col-lg-3">
            <div class="form-group">
              <label>{{ __('Gender') }}</label>
              <select class="form-control select2"
                id="sex"
                name="sex">
                <option value
                  selected>{{ __('All') }}</option>
                @foreach (App\Models\User::SEX_SELECT as $key => $label)
                  <option value="{{ $key }}">{{ $label }}</option>
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
      {{ __('User') }} {{ __('List') }}
    </div>

    <div class="card-body">
      <table class="table-bordered table-striped table-hover ajaxTable datatable datatable-User table">
        <thead>
          <tr>
            <th width="10">

            </th>
            <th>
              {{ __('ID') }}
            </th>
            <th>
              {{ __('Name') }}
            </th>
            <th>
              {{ __('Email') }}
            </th>
            <th>
              {{ __('Email verified at') }}
            </th>
            <th>
              {{ __('Roles') }}
            </th>
            <th>
              {{ __('Phone') }}
            </th>
            <th>
              {{ __('Gender') }}
            </th>
            <th>
              {{ __('Birth Date') }}
            </th>
            <th>
              &nbsp;
            </th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
@endsection
@section('scripts')
  @parent
  <script>
    $(function() {
      let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
      let deleteButton = {
        text: '{{ __('Delete selected') }}',
        url: "{{ route('admin.users.massDestroy') }}",
        className: 'btn-danger',
        action: function(e, dt, node, config) {
          var ids = $.map(dt.rows({
            selected: true
          }).data(), function(entry) {
            return entry.id
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

      dtButtons.push(deleteButton)

      let formFilter = $('#form-filter')

      let dtOverrideGlobals = {
        buttons: dtButtons,
        processing: true,
        serverSide: true,
        retrieve: true,
        aaSorting: [],
        ajax: {
          url: "{{ route('admin.users.index') }}",
          data: function(d) {
            let formData = formFilter.serializeArray()
            console.info(formData)
            $.each(formData, function(key, val) {
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
            data: 'name',
            name: 'name'
          },
          {
            data: 'email',
            name: 'email'
          },
          {
            data: 'email_verified_at',
            name: 'email_verified_at',
            visible: false
          },
          {
            data: 'roles',
            name: 'roles.name'
          },
          {
            data: 'phone',
            name: 'phone',
            visible: false
          },
          {
            data: 'sex',
            name: 'sex',
            visible: false
          },
          {
            data: 'birth_date',
            name: 'birth_date',
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

      let table = $('.datatable-User').DataTable(dtOverrideGlobals);

      $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e) {
        $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
      });

      let visibleColumnsIndexes = null;

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

      // $('.datatable thead').on('input', '.search', function() {
      //   let strict = $(this).attr('strict') || false
      //   let value = strict && this.value ? "^" + this.value + "$" : this.value

      //   let index = $(this).parent().index()
      //   if (visibleColumnsIndexes !== null) {
      //     index = visibleColumnsIndexes[index]
      //   }

      //   table
      //     .column(index)
      //     .search(value, strict)
      //     .draw()
      // })
    });
  </script>
@endsection
