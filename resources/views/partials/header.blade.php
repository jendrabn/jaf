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
        <li class="nav-item dropdown user-menu">
            <a aria-expanded="false"
               class="nav-link dropdown-toggle"
               data-toggle="dropdown"
               href="#">
                <img alt="User Image"
                     class="user-image rounded-circle shadow"
                     src="{{ Auth::user()->avatar->url ?? 'https://ui-avatars.com/api/?name=' . Auth::user()->name }}" />
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
