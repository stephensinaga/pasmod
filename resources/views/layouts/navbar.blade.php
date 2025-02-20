<header id="header" class="header fixed-top d-flex align-items-center">

<div class="d-flex align-items-center justify-content-between">
    <a href="" class="logo d-flex align-items-center">
        <img src="{{ asset('assets/img/dapur_negeri.png') }}" alt="">
        <span class="d-none d-lg-block">Dapur Negeriku</span>
    </a>
    {{-- <i class="bi bi-list toggle-sidebar-btn"></i> --}}
</div><!-- End Logo -->

    <nav class="header-nav ms-auto">
    <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
        <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
        </a>
        </li><!-- End Search Icon-->

        <li class="nav-item dropdown">

        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number">4</span>
        </a><!-- End Notification Icon -->

        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">

        </ul><!-- End Notification Dropdown Items -->

        </li><!-- End Notification Nav -->

        <li class="nav-item dropdown">

            <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                <i class="bi bi-chat-left-text"></i>
                <span class="badge bg-success badge-number">3</span>
            </a>

        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">

        </ul>

        </li>

        <li class="nav-item dropdown pe-3">

        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <span class="d-none d-md-block dropdown-toggle ps-2">
            <small>{{ auth()->user()->name }}</small>
                <br>
                <small>{{ auth()->user()->level }}</small>
            </span>
        </a>

        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
        @if(auth()->user() && auth()->user()->role === 'admin')
            <li>
                <a class="dropdown-item d-flex align-items-center" href="{{ route('cashiers.show') }}">
                    <i class="bi bi-person-plus"></i>
                    <span>Add Account</span>
                </a>
            </li>
        @endif
            <li>
                <a class="dropdown-item d-flex align-items-center" href="{{ route('logout') }}">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Sign Out</span>
                </a>
            </li>

        </ul>
        </li>

    </ul>
    </nav>

</header>
