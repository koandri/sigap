<!--  BEGIN SIDEBAR  -->
<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
        <!-- BEGIN NAVBAR TOGGLER -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- END NAVBAR TOGGLER -->
        <!-- BEGIN NAVBAR LOGO -->
        <div class="navbar-brand">
            <a href="{{ route('home') }}" aria-label="SIGaP">
                <img src="/imgs/logo.png" alt="SIGaP Logo" class="navbar-brand-image" />
            </a>
        </div>
        <!-- END NAVBAR LOGO -->
        <div class="navbar-nav flex-row d-lg-none">
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown" aria-label="Open user menu">
                    <span class="avatar avatar-sm" style="color: #fff">{!! generateInitials(Auth::user()->name) !!}</span>
                    <div class="d-none d-xl-block ps-2">
                        <div>{{ Auth::user()->name }}</div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    @impersonating($guard = null)
                    <a class="dropdown-item" href="{{ route('impersonate.leave') }}">Leave Impersonating</a>
                    @else
                    <a href="{{ route('editmyprofile') }}" class="dropdown-item">Edit Profile</a>
                    <a href="#" class="dropdown-item" onclick="confirm('Are you sure?'); event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    @endImpersonating
                </div>
            </div>
        </div>
        <div class="collapse navbar-collapse" id="sidebar-menu">
            @include('layouts.navbar')
        </div>
    </div>
</aside>
<!--  END SIDEBAR  -->