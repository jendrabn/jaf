<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a class="brand-link text-center"
    href="#">
    <span class="brand-text font-weight-bold">{{ __('JAF Parfum\'s') }}</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user (optional) -->
    <div class="user-panel d-flex mb-3 mt-3 pb-3">
      <div class="image">
        <img class="img-circle elevation-2"
          src="{{ asset('img/profile-placeholder.jpg') }}"
          alt="User Image">
      </div>
      <div class="info">
        <a class="d-block"
          href="{{ route('profile.password.edit') }}">{{ str(auth()->user()->name)->words(1, '') }}</a>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column"
        data-widget="treeview"
        data-accordion="false"
        role="menu">

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.home') ? 'active' : '' }}"
            href="{{ route('admin.home') }}">
            <i class="fas fa-fw fa-tachometer-alt nav-icon">
            </i>
            <p>
              {{ __('Dashboard') }}
            </p>
          </a>
        </li>

        <li class="nav-item has-treeview {{ request()->is('admin/users*') ? 'menu-open' : '' }}">
          <a class="nav-link nav-dropdown-toggle {{ request()->is('admin/users*') ? 'active' : '' }}"
            href="#">
            <i class="fa-fw nav-icon fas fa-users">
            </i>
            <p>
              {{ __('User management') }}
              <i class="right fa fa-fw fa-angle-left nav-icon"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a class="nav-link {{ request()->is('admin/users') || request()->is('admin/users/*') ? 'active' : '' }}"
                href="{{ route('admin.users.index') }}">
                <i class="fa-fw nav-icon fas fa-user">
                </i>
                <p>
                  {{ __('Users') }}
                </p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->is('admin/orders') || request()->is('admin/orders/*') ? 'active' : '' }}"
            href="{{ route('admin.orders.index') }}">
            <i class="fa-fw nav-icon fas fa-shopping-basket">
            </i>
            <p>
              {{ __('Orders') }}
            </p>
          </a>
        </li>

        <li
          class="nav-item has-treeview {{ request()->is('admin/product-categories*') ? 'menu-open' : '' }} {{ request()->is('admin/product-brands*') ? 'menu-open' : '' }} {{ request()->is('admin/products*') ? 'menu-open' : '' }}">
          <a class="nav-link nav-dropdown-toggle {{ request()->is('admin/product-categories*') ? 'active' : '' }} {{ request()->is('admin/product-brands*') ? 'active' : '' }} {{ request()->is('admin/product*') ? 'active' : '' }}"
            href="#">
            <i class="fa-fw nav-icon fas fa-shopping-basket">
            </i>
            <p>
              {{ __('Product management') }}
              <i class="right fa fa-fw fa-angle-left nav-icon"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a class="nav-link {{ request()->is('admin/product-categories') || request()->is('admin/product-categories/*') ? 'active' : '' }}"
                href="{{ route('admin.product-categories.index') }}">
                <i class="fa-fw nav-icon fas fa-folder">
                </i>
                <p>
                  {{ __('Categories') }}
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->is('admin/product-brands') || request()->is('admin/product-brands/*') ? 'active' : '' }}"
                href="{{ route('admin.product-brands.index') }}">
                <i class="fa-fw nav-icon fas fa-folder">
                </i>
                <p>
                  {{ __('Brands') }}
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->is('admin/products') || request()->is('admin/products/*') ? 'active' : '' }}"
                href="{{ route('admin.products.index') }}">
                <i class="fa-fw nav-icon fas fa-folder">
                </i>
                <p>
                  {{ __('Products') }}
                </p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item has-treeview {{ request()->is('admin/banks*') ? 'menu-open' : '' }}">
          <a class="nav-link nav-dropdown-toggle {{ request()->is('admin/banks*') ? 'active' : '' }}"
            href="#">
            <i class="fa-fw nav-icon fas fa-dollar-sign">
            </i>
            <p>
              {{ __('Payment method') }}
              <i class="right fa fa-fw fa-angle-left nav-icon"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a class="nav-link {{ request()->is('admin/banks') || request()->is('admin/banks/*') ? 'active' : '' }}"
                href="{{ route('admin.banks.index') }}">
                <i class="fa-fw nav-icon fas fa-folder">
                </i>
                <p>
                  {{ __('Banks') }}
                </p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->is('admin/banners') || request()->is('admin/banners/*') ? 'active' : '' }}"
            href="{{ route('admin.banners.index') }}">
            <i class="fa-fw nav-icon fas fa-image">
            </i>
            <p>
              {{ __('Banners') }}
            </p>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->is('profile/password') || request()->is('profile/password/*') ? 'active' : '' }}"
            href="{{ route('profile.password.edit') }}">
            <i class="fa-fw fas fa-key nav-icon">
            </i>
            <p>
              {{ trans('Change password') }}
            </p>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link"
            href="#"
            onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
            <p>
              <i class="fas fa-fw fa-sign-out-alt nav-icon">
              </i>
              <p>{{ __('Logout') }}</p>
            </p>
          </a>
        </li>

      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>
