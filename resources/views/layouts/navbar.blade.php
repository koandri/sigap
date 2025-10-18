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
                        <span class="nav-link-title">Assets Maintenance</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['maintenance.*']) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.dashboard') }}" href="{{ route('maintenance.dashboard') }}">
                                    <i class="fa-regular fa-chart-line"></i> &nbsp;Dashboard
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.work-orders.*') }}" href="{{ route('maintenance.work-orders.index') }}">
                                    <i class="fa-regular fa-clipboard-list"></i> &nbsp;Work Orders
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.schedules.*') }}" href="{{ route('maintenance.schedules.index') }}">
                                    <i class="fa-regular fa-calendar-check"></i> &nbsp;Maintenance Schedules
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.logs.*') }}" href="{{ route('maintenance.logs.index') }}">
                                    <i class="fa-regular fa-file-lines"></i> &nbsp;Maintenance Logs
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                @canany(['facility.dashboard.view', 'facility.tasks.view', 'facility.schedules.view', 'facility.submissions.review'])
                <li class="nav-item dropdown {{ areActiveRoutes(['facility.*']) }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="fa-regular fa-broom"></i>
                        </span>
                        <span class="nav-link-title">Facility Management</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['facility.*']) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                @can('facility.dashboard.view')
                                <a class="dropdown-item {{ areActiveRoutes('facility.dashboard') }}" href="{{ route('facility.dashboard') }}">
                                    <i class="fa-regular fa-chart-line"></i> &nbsp;Dashboard
                                </a>
                                @endcan
                                @can('facility.tasks.view')
                                <a class="dropdown-item {{ areActiveRoutes('facility.tasks.my-tasks') }}" href="{{ route('facility.tasks.my-tasks') }}">
                                    <i class="fa-regular fa-clipboard-check"></i> &nbsp;My Tasks
                                </a>
                                @endcan
                                @can('facility.schedules.view')
                                <a class="dropdown-item {{ areActiveRoutes('facility.schedules.*') }}" href="{{ route('facility.schedules.index') }}">
                                    <i class="fa-regular fa-calendar-days"></i> &nbsp;Cleaning Schedules
                                </a>
                                @endcan
                                @can('facility.submissions.review')
                                <a class="dropdown-item {{ areActiveRoutes('facility.approvals.*') }}" href="{{ route('facility.approvals.index') }}">
                                    <i class="fa-regular fa-check-double"></i> &nbsp;Approvals
                                </a>
                                @php
                                    $pendingApprovals = \App\Models\CleaningApproval::where('status', 'pending')->count();
                                @endphp
                                @if($pendingApprovals > 0)
                                    <span class="badge badge-outline text-danger ms-1">{{ $pendingApprovals }}</span>
                                @endif
                                @endcan
                                <a class="dropdown-item" href="{{ route('facility.requests.guest-form') }}" target="_blank">
                                    <i class="fa-regular fa-paper-plane"></i> &nbsp;Submit Request
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                @endcanany
                <li class="nav-item dropdown {{ areActiveRoutes(['reports.assets.*']) }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="fa-regular fa-chart-bar"></i>
                        </span>
                        <span class="nav-link-title">Reports</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['reports.assets.*']) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <h6 class="dropdown-header">Assets</h6>
                                <a class="dropdown-item {{ areActiveRoutes('reports.assets.by-location') }}" href="{{ route('reports.assets.by-location') }}">
                                    <i class="fa-regular fa-map-marker-alt"></i> &nbsp;by Location
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('reports.assets.by-category') }}" href="{{ route('reports.assets.by-category') }}">
                                    <i class="fa-regular fa-tags"></i> &nbsp;by Category
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('reports.assets.by-category-location') }}" href="{{ route('reports.assets.by-category-location') }}">
                                    <i class="fa-regular fa-layer-group"></i> &nbsp;by Category & Location
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('reports.assets.by-department') }}" href="{{ route('reports.assets.by-department') }}">
                                    <i class="fa-regular fa-building"></i> &nbsp;by Department
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('reports.assets.by-user') }}" href="{{ route('reports.assets.by-user') }}">
                                    <i class="fa-regular fa-user"></i> &nbsp;by Assigned User
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown {{ areActiveRoutes(['options.*']) }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="fa-regular fa-sliders"></i>
                        </span>
                        <span class="nav-link-title">Options</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['options.*']) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ areActiveRoutes('options.assets.*') }}" href="{{ route('options.assets.index') }}">
                                    <i class="fa-regular fa-boxes-stacked"></i> &nbsp;Assets
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('options.asset-categories.*') }}" href="{{ route('options.asset-categories.index') }}">
                                    <i class="fa-regular fa-layer-group"></i> &nbsp;Asset Categories
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('options.locations.*') }}" href="{{ route('options.locations.index') }}">
                                    <i class="fa-regular fa-map-marker-alt"></i> &nbsp;Locations
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
