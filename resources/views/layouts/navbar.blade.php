            <!-- BEGIN NAVBAR MENU -->
            <ul class="navbar-nav pt-lg-3">
                <li class="nav-item {{ areActiveRoutes('home') }}">
                    <a class="nav-link" href="{{ route('home') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="fa-regular fa-house-heart"></i>
                        </span>
                        <span class="nav-link-title">Home</span>
                    </a>
                </li>
                <li class="nav-item dropdown {{ areActiveRoutes(['forms.*', 'formversions.*', 'formfields.*', 'formsubmissions.*']) }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="fa-regular fa-folder-gear"></i>
                        </span>
                        <span class="nav-link-title">Forms</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['forms.*', 'formversions.*', 'formfields.*', 'formsubmissions.*', 'approval-workflows.*']) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ areActiveRoutes('formsubmissions.submissions') }}" href="{{ route('formsubmissions.submissions') }}">
                                    <i class="fa-regular fa-square-list"></i> &nbsp;Form Submissions
                                </a>
                                <!-- Pending Approvals with Badge -->
                                @php
                                    $pendingCount = auth()->user() ? 
                                        \App\Models\ApprovalLog::where('assigned_to', auth()->id())
                                            ->where('status', 'pending')
                                            ->count() : 0;
                                @endphp 
                                <a class="dropdown-item {{ areActiveRoutes('formsubmissions.pending') }}" href="{{ route('formsubmissions.pending') }}">
                                    <i class="fa-regular fa-hourglass-clock"></i> &nbsp;Pending Approvals
                                    @if($pendingCount > 0)
                                        <span class="badge badge-outline text-danger ms-1">{{ $pendingCount }}</span>
                                    @endif
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('formsubmissions.index') }}" href="{{ route('formsubmissions.index') }}">
                                    <i class="fa-regular fa-clipboard-list-check"></i> &nbsp;Fill Form
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes(['forms.*', 'formversions.*', 'formfields.*', 'approval-workflows.*']) }}" href="{{ route('forms.index') }}">
                                    <i class="fa-regular fa-money-check-pen"></i> &nbsp;Form Templates
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown {{ areActiveRoutes(['manufacturing.*']) }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="fa-regular fa-industry"></i>
                        </span>
                        <span class="nav-link-title">Manufacturing</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['manufacturing.*']) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ areActiveRoutes('manufacturing.dashboard') }}" href="{{ route('manufacturing.dashboard') }}">
                                    <i class="fa-regular fa-chart-line"></i> &nbsp;Dashboard
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('manufacturing.items.*') }}" href="{{ route('manufacturing.items.index') }}">
                                    <i class="fa-regular fa-boxes-stacked"></i> &nbsp;Items
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('manufacturing.item-categories.*') }}" href="{{ route('manufacturing.item-categories.index') }}">
                                    <i class="fa-regular fa-layer-group"></i> &nbsp;Item Categories
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('manufacturing.warehouses.*') }}" href="{{ route('manufacturing.warehouses.index') }}">
                                    <i class="fa-regular fa-warehouse"></i> &nbsp;Warehouses
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('manufacturing.bom.*') }}" href="{{ route('manufacturing.bom.index') }}">
                                    <i class="fa-regular fa-sitemap"></i> &nbsp;Bill of Materials
                                </a>
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header">Coming Soon</h6>
                                <a class="dropdown-item disabled">
                                    <i class="fa-regular fa-calendar-check"></i> &nbsp;Production Planning
                                </a>
                                <a class="dropdown-item disabled">
                                    <i class="fa-regular fa-gears"></i> &nbsp;Production Execution
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown {{ areActiveRoutes(['maintenance.*']) }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="fa-regular fa-wrench"></i>
                        </span>
                        <span class="nav-link-title">Maintenance</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['maintenance.*']) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.dashboard') }}" href="{{ route('maintenance.dashboard') }}">
                                    <i class="fa-regular fa-chart-line"></i> &nbsp;Dashboard
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.assets.*') }}" href="{{ route('maintenance.assets.index') }}">
                                    <i class="fa-regular fa-boxes-stacked"></i> &nbsp;Assets
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.asset-categories.*') }}" href="{{ route('maintenance.asset-categories.index') }}">
                                    <i class="fa-regular fa-layer-group"></i> &nbsp;Asset Categories
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.work-orders.*') }}" href="{{ route('maintenance.work-orders.index') }}">
                                    <i class="fa-regular fa-clipboard-list"></i> &nbsp;Work Orders
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.schedules.*') }}" href="{{ route('maintenance.schedules.index') }}">
                                    <i class="fa-regular fa-calendar-check"></i> &nbsp;Maintenance Schedules
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.logs.*') }}" href="{{ route('maintenance.logs.index') }}">
                                    <i class="fa-regular fa-file-lines"></i> &nbsp;Maintenance Logs
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.reports.*') }}" href="{{ route('maintenance.reports.index') }}">
                                    <i class="fa-regular fa-chart-bar"></i> &nbsp;Reports
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.calendar') }}" href="{{ route('maintenance.calendar') }}">
                                    <i class="fa-regular fa-calendar"></i> &nbsp;Calendar
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown {{ areActiveRoutes(['users.*', 'roles.*', 'permissions.*', 'departments.*']) }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="fa-regular fa-user-crown"></i>
                        </span>
                        <span class="nav-link-title">Admin</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['users.*', 'roles.*', 'permissions.*', 'departments.*']) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ areActiveRoutes('users.*') }}" href="{{ route('users.index') }}">
                                    <i class="fa-regular fa-users"></i> &nbsp;Users
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('roles.*') }}" href="{{ route('roles.index') }}">
                                    <i class="fa-regular fa-user-question"></i> &nbsp;Roles
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('permissions.*') }}" href="{{ route('permissions.index') }}">
                                    <i class="fa-regular fa-user-key"></i> &nbsp;Permissions
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('departments.*') }}" href="{{ route('departments.index') }}">
                                    <i class="fa-regular fa-building-user"></i> &nbsp;Departments
                                </a>
                                <div class="dropend">
                                    <a class="dropdown-item dropdown-toggle" href="#sidebar-authentication" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                                        Authentication
                                    </a>
                                    <div class="dropdown-menu">
                                        <a href="./sign-in.html" class="dropdown-item"> Sign in </a>
                                        <a href="./sign-in-link.html" class="dropdown-item"> Sign in link </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    @impersonating($guard = null)
                    <a class="nav-link" href="{{ route('impersonate.leave') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="fa-regular fa-right-from-bracket"></i>
                        </span>
                        <span class="nav-link-title">Leave Impersonating</span>
                    </a>
                    @else
                    <a class="nav-link" href="#" onclick="confirm('Are you sure?'); event.preventDefault(); document.getElementById('navbar-logout-form').submit();">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="fa-regular fa-right-from-bracket"></i>
                        </span>
                        <span class="nav-link-title">Logout</span>
                    </a>
                    <form id="navbar-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    @endImpersonating
                </li>
            </ul>
            <!-- END NAVBAR MENU -->
