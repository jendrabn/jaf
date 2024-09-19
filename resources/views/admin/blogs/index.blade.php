@extends('layouts.admin', ['title' => 'Blog List'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Blog List</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                {{ $dataTable->table(['class' => 'table table-sm table-striped table-bordered datatable ajaxTable']) }}
            </div>
        </div>
    </div>

    @include('admin.blogs.partials.modal-filter')
@endSection

@section('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}

    <script>
        $(document).ready(function() {
            const table = window.LaravelDataTables["blog-table"];

            $.fn.dataTable.ext.buttons.bulkDelete = {
                text: "Delete selected",
                url: "{{ route('admin.blogs.massDestroy') }}",
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

            $.fn.dataTable.ext.buttons.filter = {
                text: "<i class='fa fa-filter'></i>",
                action: function(e, dt, node, config) {
                    $("#modal-filter").modal("show");
                },
            };

            table.on("click", ".btn-delete", function(e) {
                const data = table.row($(this).parents("tr")).data();

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
                            url: $(this).data("url"),
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

            table.on("change", ".check-published", function(e) {
                $.ajax({
                    headers: {
                        "x-csrf-token": _token,
                    },
                    method: "PUT",
                    url: $(this).data("url"),
                    success: function(data) {
                        toastr.success(data.message);
                    },
                });
            });

            $('#btn-reset-filter').on('click', function() {
                $('#form-filter')[0].reset();
                table.ajax.reload();
            });

            $('#btn-filter').on('click', function() {
                table.ajax.reload();
            });
        });
    </script>
@endSection
