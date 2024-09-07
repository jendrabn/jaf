@extends('layouts.admin', ['title' => 'Product List'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Product List</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                {{ $dataTable->table(['class' => 'table table-sm table-striped table-bordered datatable ajaxTable']) }}
            </div>
        </div>
    </div>

    <div aria-hidden="true"
         aria-labelledby="exampleModalLabel"
         class="modal fade"
         data-backdrop="static"
         id="modal-filter"
         tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"
                        id="exampleModalLabel">
                        Filter Products
                    </h5>
                    <button aria-label="Close"
                            class="close"
                            data-dismiss="modal"
                            type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="form-row"
                          id="form-filter">
                        <div class="form-group col-md-6">
                            <label for="_product_category_id">Category</label>
                            <select class="form-control select2"
                                    id="_product_category_id"
                                    name="product_category_id"
                                    style="width: 100%">
                                @foreach ($product_categories as $id => $name)
                                    <option @selected($id === null)
                                            value="{{ $id }}">
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="_product_brand_id">Brand</label>
                            <select class="form-control select2"
                                    id="_product_brand_id"
                                    name="product_brand_id"
                                    style="width: 100%">
                                @foreach ($product_brands as $id => $name)
                                    <option @selected($id === null)
                                            value="{{ $id }}">
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="_sex">Gender</label>
                            <select class="form-control select2"
                                    id="_sex"
                                    name="sex"
                                    style="width: 100%">
                                <option selected
                                        value="">All</option>
                                @foreach (App\Models\Product::SEX_SELECT as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="_is_publish">Publish Status</label>
                            <select class="form-control select2"
                                    id="_is_publish"
                                    name="is_publish"
                                    style="width: 100%">
                                <option selected
                                        value="">All</option>
                                <option value="1">Published</option>
                                <option value="0">Unpublished</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary"
                            data-dismiss="modal"
                            type="button">
                        Close
                    </button>
                    <button class="btn btn-primary"
                            id="btn-filter"
                            type="button">
                        Save changes
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}
    <script>
        $(function() {
            $.fn.dataTable.ext.buttons.filter = {
                text: '<i class="fas fa-filter"></i> Filter',
                attr: {
                    "data-toggle": "modal",
                    "data-target": "#modal-filter",
                },
            };

            $.fn.dataTable.ext.buttons.bulkDelete = {
                text: "Delete selected",
                url: "{{ route('admin.products.massDestroy') }}",
                action: function(e, dt, node, config) {
                    let ids = $.map(
                        dt.rows({
                            selected: true,
                        }).data(),

                        function(entry) {
                            return entry.id;
                        }
                    );

                    if (ids.length === 0) {
                        toastr.warning("No rows selected", 'Warning');

                        return;
                    }

                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Delete"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                headers: {
                                    "x-csrf-token": _token,
                                },
                                method: "POST",
                                url: config.url,
                                data: {
                                    ids: ids,
                                    _method: "DELETE",
                                },
                                success: function(data) {
                                    toastr.success(data.message);
                                    dt.ajax.reload();
                                },
                            });
                        }
                    });
                },
            };

            const table = window.LaravelDataTables["dataTable-products"];

            table.on("click", ".btn-delete", function(e) {
                e.preventDefault();

                let url = $(this).attr("href");

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Delete"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                "x-csrf-token": _token,
                            },
                            method: "POST",
                            url: url,
                            data: {
                                _method: "DELETE",
                            },
                            success: function(data) {
                                toastr.success(data.message);

                                table.ajax.reload();
                            },
                        });
                    }
                });
            });

            $("#btn-filter").on("click", function() {
                $("#modal-filter").modal("hide");

                table.ajax.reload();
            });

            $('a[data-toggle="tab"]').on("shown.bs.tab click", function(e) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            });

            let visibleColumnsIndexes = null;

            $(".datatable thead").on("input", ".search", function() {
                let strict = $(this).attr("strict") || false;
                let value =
                    strict && this.value ? "^" + this.value + "$" : this.value;

                let index = $(this).parent().index();
                if (visibleColumnsIndexes !== null) {
                    index = visibleColumnsIndexes[index];
                }

                table.column(index).search(value, strict).draw();
            });

            table.on("column-visibility.dt", function(e, settings, column, state) {
                visibleColumnsIndexes = [];

                table.columns(":visible").every(function(colIdx) {
                    visibleColumnsIndexes.push(colIdx);
                });
            });
        });
    </script>
@endsection
