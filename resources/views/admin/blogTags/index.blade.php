@extends('layouts.admin', ['title' => 'Blog Tag List'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Blog Tag List</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                {{ $dataTable->table(['class' => 'table table-sm table-striped table-bordered datatable ajaxTable']) }}
            </div>
        </div>
    </div>

    @include('admin.blogCategories.partials.modal-create')
    @include('admin.blogCategories.partials.modal-edit')
@endSection

@section('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}

    <script>
        $(document).ready(function() {
            const table = window.LaravelDataTables["blogtag-table"];

            $.fn.dataTable.ext.buttons.create = {
                text: "Create",
                action: function(e, dt, node, config) {
                    $('#modal-create form input[name=name]').val('');
                    $('#modal-create').modal('show')
                },
            }

            $.fn.dataTable.ext.buttons.bulkDelete = {
                text: "Delete selected",
                url: "{{ route('admin.blog-tags.massDestroy') }}",
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

            table.on('click', '.btn-edit', function(e) {
                const data = table.row($(this).parents('tr')).data();

                $('#modal-edit form').attr('action', $(this).data('url'));
                $('#modal-edit form input[name=name]').val(data.name);
                $('#modal-edit').modal('show');
            });

            table.on('click', '.btn-delete', function(e) {
                const data = table.row($(this).parents('tr')).data();

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
                            url: $(this).data('url'),
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

            $('#modal-create form').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    headers: {
                        "x-csrf-token": _token,
                    },
                    method: "POST",
                    url: "{{ route('admin.blog-tags.store') }}",
                    data: $(this).serialize(),
                    success: function(data) {
                        $('#modal-create').modal('hide');
                        toastr.success(data.message);
                        table.ajax.reload();
                    },
                });
            });

            $('#modal-edit form').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    headers: {
                        "x-csrf-token": _token,
                    },
                    method: "PUT",
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    success: function(data) {
                        $('#modal-edit').modal('hide');
                        toastr.success(data.message);
                        table.ajax.reload();
                    },
                });
            });
        })
    </script>
@endSection
