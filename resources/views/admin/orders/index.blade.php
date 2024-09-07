@extends('layouts.admin', ['title' => 'Order List'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Order List</h3>
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
                        Filter Orders
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control select2"
                                        id="status"
                                        name="status"
                                        style="width: 100%">
                                    <option selected
                                            value>All</option>
                                    @foreach (App\Models\Order::STATUSES as $key => $status)
                                        <option value="{{ $key }}">
                                            {{ $status['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
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
                url: "{{ route('admin.orders.massDestroy') }}",
                action: function(e, dt, node, config) {
                    let ids = $.map(
                        dt
                        .rows({
                            selected: true,
                        })
                        .data(),
                        function(entry) {
                            return entry.id;
                        }
                    );

                    if (ids.length === 0) {
                        toastr.warning("No rows selected", "Warning");

                        return;
                    }

                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Delete",
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

            const table = LaravelDataTables["dataTable-orders"];

            table.on("click", ".btn-delete", function(e) {
                e.preventDefault();

                let url = $(this).attr("href");

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Delete",
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

            table.on("click", ".btn-delete", function(e) {
                e.preventDefault();

                let url = $(this).attr("href");

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Delete",
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

            table.on("column-visibility.dt", function(e, settings, column, state) {
                visibleColumnsIndexes = [];
                table.columns(":visible").every(function(colIdx) {
                    visibleColumnsIndexes.push(colIdx);
                });
            });

            $("#btn-filter").on("click", function() {
                $("#modal-filter").modal("hide");
                table.ajax.reload();
            });
        });
    </script>
@endsection
