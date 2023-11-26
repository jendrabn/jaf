@extends('layouts.admin')
@section('content')
  <div class="row"
    style="margin-bottom: 10px;">
    <div class="col-lg-12">
      <a class="btn btn-success"
        href="{{ route('admin.products.create') }}">
        {{ __('Add') }} {{ __('Product') }}
      </a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <form id="form-filter">
        <div class="row">
          <div class="col-12 col-md-4 col-lg-3">
            <div class="form-group">
              <label for="category_id">{{ __('Category') }}</label>
              <select class="form-control select2"
                id="category_id"
                name="category_id">
                @foreach ($product_categories as $id => $name)
                  <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="col-12 col-md-4 col-lg-3">
            <div class="form-group">
              <label for="brand_id">{{ __('Brand') }}</label>
              <select class="form-control select2"
                id="brand_id"
                name="brand_id">
                @foreach ($product_brands as $id => $name)
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
                @foreach (App\Models\Product::SEX_SELECT as $key => $label)
                  <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="col-sm-12 col-md-4 col-lg-3">
            <div class="form-group">
              <label>{{ __('Publish Status') }}</label>
              <select class="form-control select2"
                id="is_publish"
                name="is_publish">
                <option value
                  selected>{{ __('All') }}</option>
                <option value="1">{{ __('Published') }}</option>
                <option value="0">{{ __('Unpublished') }}</option>
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
      {{ __('Product') }} {{ __('List') }}
    </div>

    <div class="card-body">
      <table class="table-bordered table-striped table-hover ajaxTable datatable datatable-Product table">
        <thead>
          <tr>
            <th width="10">

            </th>
            <th>
              {{ __('ID') }}
            </th>
            <th>
              {{ __('Product Image') }}
            </th>
            <th>
              {{ __('Product Name') }}
            </th>
            <th>
              {{ __('Category') }}
            </th>
            <th>
              {{ __('Brand') }}
            </th>
            <th>
              {{ __('Gender') }}
            </th>
            <th>
              {{ __('Price') }}
            </th>
            <th>
              {{ __('Stock') }}
            </th>
            <th>
              {{ __('Weight') }}
            </th>
            <th>
              {{ __('Publish') }}
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
        url: "{{ route('admin.products.massDestroy') }}",
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
          url: "{{ route('admin.products.index') }}",
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
            data: 'image',
            name: 'image',
            sortable: false,
            searchable: false
          },
          {
            data: 'name',
            name: 'name'
          },
          {
            data: 'category_name',
            name: 'category.name'
          },
          {
            data: 'brand_name',
            name: 'brand.name',
            visible: false
          },
          {
            data: 'sex',
            name: 'sex',
            visible: false
          },
          {
            data: 'price',
            name: 'price'
          },
          {
            data: 'stock',
            name: 'stock'
          },
          {
            data: 'weight',
            name: 'weight',
            visible: false
          },
          {
            data: 'is_publish',
            name: 'is_publish',
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

      let table = $('.datatable-Product').DataTable(dtOverrideGlobals);

      $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e) {
        $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
      });

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
