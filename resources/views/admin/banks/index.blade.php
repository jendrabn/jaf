@extends('layouts.admin', ['title' => 'Bank List'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Bank List</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                {{ $dataTable->table(['class' => 'table table-sm table-striped table-bordered datatable ajaxTable']) }}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}
    <script>
        $(function() {
            $.fn.dataTable.ext.buttons.bulkDelete = {
                text: "Delete selected",
                url: "{{ route('admin.banks.massDestroy') }}",
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
                        toastr.warning("No rows selected", 'Warning');

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

            const table = window.LaravelDataTables["dataTable-banks"];

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
