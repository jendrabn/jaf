<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
          name="viewport" />
    <meta content="ie=edge"
          http-equiv="X-UA-Compatible" />
    <meta content="{{ csrf_token() }}"
          name="csrf-token" />

    <title>{{ $title }} | {{ config('app.name') }}</title>

    <link href="{{ asset('img/favicon.ico') }}"
          rel="icon"
          type="image/x-icon" />

    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700"
          rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"
          rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
          rel="stylesheet" />
    <link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.1.3/b-3.1.1/b-colvis-3.1.1/b-html5-3.1.1/b-print-3.1.1/r-3.0.2/sb-1.7.1/sp-2.3.1/sl-2.0.4/datatables.min.css"
          rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css"
          rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css"
          rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"
          rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css"
          rel="stylesheet" />
    @vite('resources/scss/adminlte.scss')
    @yield('styles')
    @stack('styles')
</head>

<body class="hold-transition sidebar-mini fixed-layout">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-light border-bottom bg-white">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link"
                       data-widget="pushmenu"
                       href="#"><i class="fa-solid fa-bars"></i>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link"
                       data-widget="fullscreen"
                       href="#"
                       role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
                <li class="nav-item dropdown user-menu">
                    <a aria-expanded="false"
                       class="nav-link dropdown-toggle"
                       data-toggle="dropdown"
                       href="#">
                        <img alt="User Image"
                             class="user-image rounded-circle shadow"
                             src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}" />
                        <span class="d-none d-md-inline font-weight-bold">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg-right"
                        style="max-width: 150px">
                        <a class="dropdown-item btn btn-light"
                           href="{{ route('admin.profile.index') }}">Profile</a>
                        <a class="dropdown-item btn btn-light"
                           href="{{ route('auth.logout') }}">Logout</a>
                    </ul>
                </li>
            </ul>
        </nav>

        @include('partials.sidebar')

        <div class="content-wrapper">
            <section class="content"
                     style="padding-top: 20px">
                @if (session('message'))
                    <div class="row mb-2">
                        <div class="col-lg-12">
                            <div class="alert alert-success"
                                 role="alert">
                                {{ session('message') }}
                            </div>
                        </div>
                    </div>
                    @endif @if ($errors->count() > 0)
                        <div class="alert alert-danger">
                            <ul class="list-unstyled">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @yield('content')
            </section>
        </div>

        @include('partials.footer')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script
            src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.1.3/b-3.1.1/b-colvis-3.1.1/b-html5-3.1.1/b-print-3.1.1/r-3.0.2/sb-1.7.1/sp-2.3.1/sl-2.0.4/datatables.min.js">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/locale/id.min.js"></script>
    <script
            src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js">
    </script>
    <script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    <script>
        $(function() {
            Swal = Swal.mixin({
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-secondary",
                },
            });

            toastr.options = {
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: 5000,
            };

            $.ajaxSetup({
                error: function(jqXHR, textStatus, errorThrown) {
                    toastr.error(jqXHR.responseJSON.message || errorThrown);
                },
            });

            $.extend(true, $.fn.dataTable.Buttons.defaults.dom.button, {
                className: "btn btn-secondary",
            });

            $.extend(true, $.fn.dataTable.defaults, {
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.10.19/i18n/English.json",
                },
                order: [1, "desc"],
                scrollX: true,
                pageLength: 10,
                lengthMenu: [
                    10,
                    25,
                    50,
                    100,
                    {
                        label: "All",
                        value: -1,
                    },
                ],
                dom: 'lBfrtip<"actions">',
                initComplete: function() {
                    this.api().columns.adjust().draw();
                },
            });

            $.fn.dataTable.ext.classes.sPageButton = "";

            $('a[data-widget^="pushmenu"]').click(function() {
                let isCollapsed =
                    document.body.classList.contains("sidebar-collapse");

                localStorage.setItem("pushmenu", !isCollapsed);

                setTimeout(function() {
                    $($.fn.dataTable.tables(true))
                        .DataTable()
                        .columns.adjust();
                }, 350);
            });

            if (localStorage.getItem("pushmenu") === "true") {
                document.body.classList.add("sidebar-collapse");
            }
        });
    </script>

    @yield('scripts')
    @stack('scripts')
</body>

</html>
