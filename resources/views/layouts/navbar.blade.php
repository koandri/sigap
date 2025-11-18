            <!-- BEGIN NAVBAR MENU -->
            <ul class="navbar-nav pt-lg-3">
                <li class="nav-item {{ areActiveRoutes('home') }}">
                    <a class="nav-link" href="{{ route('home') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="far fa-house-heart"></i>&nbsp;
                        </span>
                        <span class="nav-link-title">Home</span>
                    </a>
                </li>
                <li class="nav-item dropdown {{ areActiveRoutes(['forms.*', 'formversions.*', 'formfields.*', 'formsubmissions.*']) }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="far fa-folder-gear"></i>&nbsp;
                        </span>
                        <span class="nav-link-title">Forms</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['forms.*', 'formversions.*', 'formfields.*', 'formsubmissions.*', 'approval-workflows.*']) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ areActiveRoutes('formsubmissions.submissions') }}" href="{{ route('formsubmissions.submissions') }}">
                                    <i class="far fa-square-list"></i>&nbsp; &nbsp;Form Submissions
                                </a>
                                <!-- Pending Approvals with Badge -->
                                @php
                                    $pendingCount = auth()->user() ? 
                                        \App\Models\ApprovalLog::where('assigned_to', auth()->id())
                                            ->where('status', 'pending')
                                            ->count() : 0;
                                @endphp 
                                <a class="dropdown-item {{ areActiveRoutes('formsubmissions.pending') }}" href="{{ route('formsubmissions.pending') }}">
                                    <i class="far fa-hourglass-clock"></i>&nbsp; &nbsp;Pending Approvals
                                    @if($pendingCount > 0)
                                        <span class="badge badge-outline text-danger ms-1">{{ $pendingCount }}</span>
                                    @endif
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('formsubmissions.index') }}" href="{{ route('formsubmissions.index') }}">
                                    <i class="far fa-clipboard-list-check"></i>&nbsp; &nbsp;Fill Form
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes(['forms.*', 'formversions.*', 'formfields.*', 'approval-workflows.*']) }}" href="{{ route('forms.index') }}">
                                    <i class="far fa-money-check-pen"></i>&nbsp; &nbsp;Form Templates
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown {{ areActiveRoutes(['documents.*', 'documents.versions.*', 'document-versions.*', 'document-access.*', 'my-document-access', 'form-requests.*', 'printed-forms.*', 'dms-dashboard', 'correspondences.*']) }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="far fa-file-lines"></i>&nbsp;
                        </span>
                        <span class="nav-link-title">Document Management</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['documents.*', 'documents.versions.*', 'document-versions.*', 'document-access.*', 'my-document-access', 'form-requests.*', 'printed-forms.*', 'dms-dashboard', 'correspondences.*']) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ areActiveRoutes('dms-dashboard') }}" href="{{ route('dms-dashboard') }}">
                                    <i class="far fa-chart-line"></i>&nbsp; &nbsp;Dashboard
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes(['documents.*', 'documents.versions.*', 'document-versions.*']) }}" href="{{ route('documents.index') }}">
                                    <i class="far fa-folder"></i>&nbsp; &nbsp;Documents
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('my-document-access') }}" href="{{ route('my-document-access') }}">
                                    <i class="far fa-eye"></i>&nbsp; &nbsp;My Documents
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('form-requests.*') }}" href="{{ route('form-requests.index') }}">
                                    <i class="far fa-file-text"></i>&nbsp; &nbsp;Form Requests
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('printed-forms.*') }}" href="{{ route('printed-forms.index') }}">
                                    <i class="far fa-print"></i>&nbsp; &nbsp;Printed Forms
                                </a>
                                @can('viewAny', App\Models\DocumentInstance::class)
                                <a class="dropdown-item {{ areActiveRoutes('correspondences.*') }}" href="{{ route('correspondences.index') }}">
                                    <i class="far fa-envelope"></i>&nbsp; &nbsp;Correspondence
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown {{ areActiveRoutes(['manufacturing.*']) }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="far fa-industry"></i>&nbsp;
                        </span>
                        <span class="nav-link-title">Manufacturing</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['manufacturing.*']) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ areActiveRoutes('manufacturing.dashboard') }}" href="{{ route('manufacturing.dashboard') }}">
                                    <i class="far fa-chart-line"></i>&nbsp; &nbsp;Dashboard
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('manufacturing.items.*') }}" href="{{ route('manufacturing.items.index') }}">
                                    <i class="far fa-boxes-stacked"></i>&nbsp; &nbsp;Items
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('manufacturing.item-categories.*') }}" href="{{ route('manufacturing.item-categories.index') }}">
                                    <i class="far fa-layer-group"></i>&nbsp; &nbsp;Item Categories
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('manufacturing.warehouses.*') }}" href="{{ route('manufacturing.warehouses.index') }}">
                                    <i class="far fa-warehouse"></i>&nbsp; &nbsp;Warehouses
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item {{ areActiveRoutes('manufacturing.recipes.*') }}" href="{{ route('manufacturing.recipes.index') }}">
                                    <i class="far fa-book"></i>&nbsp; &nbsp;Recipes
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('manufacturing.production-plans.*') }}" href="{{ route('manufacturing.production-plans.index') }}">
                                    <i class="far fa-calendar-check"></i>&nbsp; &nbsp;Production Planning
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('manufacturing.yield-guidelines.*') }}" href="{{ route('manufacturing.yield-guidelines.index') }}">
                                    <i class="far fa-chart-line"></i>&nbsp; &nbsp;Yield Guidelines
                                </a>
                                @can('manufacturing.packing-blueprints.view')
                                <a class="dropdown-item {{ areActiveRoutes('manufacturing.packing-material-blueprints.*') }}" href="{{ route('manufacturing.packing-material-blueprints.index') }}">
                                    <i class="far fa-box-open"></i>&nbsp; &nbsp;Packing Blueprints
                                </a>
                                @endcan
                                @can('manufacturing.kerupuk-pack-config.view')
                                <a class="dropdown-item {{ areActiveRoutes('manufacturing.kerupuk-pack-configurations.*') }}" href="{{ route('manufacturing.kerupuk-pack-configurations.index') }}">
                                    <i class="far fa-link"></i>&nbsp; &nbsp;Kerupuk Pack Config
                                </a>
                                @endcan
                                <a class="dropdown-item disabled">
                                    <i class="far fa-gears"></i>&nbsp; &nbsp;Production Execution
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown {{ areActiveRoutes(['maintenance.*']) }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="far fa-wrench"></i>&nbsp;
                        </span>
                        <span class="nav-link-title">Assets Maintenance</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['maintenance.*']) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.dashboard') }}" href="{{ route('maintenance.dashboard') }}">
                                    <i class="far fa-chart-line"></i>&nbsp; &nbsp;Dashboard
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.work-orders.*') }}" href="{{ route('maintenance.work-orders.index') }}">
                                    <i class="far fa-clipboard-list"></i>&nbsp; &nbsp;Work Orders
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.schedules.*') }}" href="{{ route('maintenance.schedules.index') }}">
                                    <i class="far fa-calendar-check"></i>&nbsp; &nbsp;Maintenance Schedules
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('maintenance.logs.*') }}" href="{{ route('maintenance.logs.index') }}">
                                    <i class="far fa-file-lines"></i>&nbsp; &nbsp;Maintenance Logs
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                @canany(['facility.dashboard.view', 'facility.tasks.view', 'facility.schedules.view', 'facility.submissions.review'])
                <li class="nav-item dropdown {{ areActiveRoutes(['facility.*']) }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="far fa-broom"></i>&nbsp;
                        </span>
                        <span class="nav-link-title">Facility Management</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['facility.*']) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                @can('facility.dashboard.view')
                                <a class="dropdown-item {{ areActiveRoutes('facility.dashboard') }}" href="{{ route('facility.dashboard') }}">
                                    <i class="far fa-chart-line"></i>&nbsp; &nbsp;Dashboard
                                </a>
                                @endcan
                                @can('facility.tasks.view')
                                <a class="dropdown-item {{ areActiveRoutes('facility.tasks.my-tasks') }}" href="{{ route('facility.tasks.my-tasks') }}">
                                    <i class="far fa-clipboard-check"></i>&nbsp; &nbsp;My Tasks
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('facility.tasks.index') }}" href="{{ route('facility.tasks.index') }}">
                                    <i class="far fa-list-check"></i>&nbsp; &nbsp;All Tasks
                                </a>
                                @endcan
                                @can('facility.schedules.view')
                                <a class="dropdown-item {{ areActiveRoutes('facility.schedules.*') }}" href="{{ route('facility.schedules.index') }}">
                                    <i class="far fa-calendar-days"></i>&nbsp; &nbsp;Cleaning Schedules
                                </a>
                                @endcan
                                @can('facility.submissions.review')
                                <a class="dropdown-item {{ areActiveRoutes('facility.approvals.*') }}" href="{{ route('facility.approvals.index') }}">
                                    <i class="far fa-check-double"></i>&nbsp; &nbsp;Approvals
                                </a>
                                @php
                                    $pendingApprovals = \App\Models\CleaningApproval::where('status', 'pending')->count();
                                @endphp
                                @if($pendingApprovals > 0)
                                    <span class="badge badge-outline text-danger ms-1">{{ $pendingApprovals }}</span>
                                @endif
                                @endcan
                                <a class="dropdown-item" href="{{ route('facility.requests.guest-form') }}" target="_blank">
                                    <i class="far fa-paper-plane"></i>&nbsp; &nbsp;Submit Request
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                @endcanany
                <li class="nav-item dropdown {{ areActiveRoutes(['reports.assets.*', 'reports.facility.*', 'reports.document-management.*']) }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="far fa-chart-bar"></i>&nbsp;
                        </span>
                        <span class="nav-link-title">Reports</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['reports.assets.*', 'reports.facility.*', 'reports.document-management.*']) }}">
                        <div class="dropdown-menu-columns">
                            @canany(['dms.reports.view', 'dms.sla.report.view'])
                            <div class="dropdown-menu-column">
                                <h6 class="dropdown-header">Document Management</h6>
                                @can('dms.reports.view')
                                <a class="dropdown-item {{ areActiveRoutes('reports.document-management.locations.*') }}" href="{{ route('reports.document-management.locations.index') }}">
                                    <i class="far fa-map-marker-alt"></i>&nbsp; &nbsp;Location Reports
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('reports.document-management.masterlist.*') }}" href="{{ route('reports.document-management.masterlist') }}">
                                    <i class="far fa-list"></i>&nbsp; &nbsp;Masterlist
                                </a>
                                @endcan
                                @can('dms.sla.report.view')
                                <a class="dropdown-item {{ areActiveRoutes('reports.document-management.sla') }}" href="{{ route('reports.document-management.sla') }}">
                                    <i class="far fa-chart-bar"></i>&nbsp; &nbsp;SLA Report
                                </a>
                                @endcan
                            </div>
                            @endcanany
                            @can('asset.reports.view')
                            <div class="dropdown-menu-column">
                                <h6 class="dropdown-header">Assets</h6>
                                <a class="dropdown-item {{ areActiveRoutes('reports.assets.by-location') }}" href="{{ route('reports.assets.by-location') }}">
                                    <i class="far fa-map-marker-alt"></i>&nbsp; &nbsp;by Location
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('reports.assets.by-category') }}" href="{{ route('reports.assets.by-category') }}">
                                    <i class="far fa-tags"></i>&nbsp; &nbsp;by Category
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('reports.assets.by-category-location') }}" href="{{ route('reports.assets.by-category-location') }}">
                                    <i class="far fa-layer-group"></i>&nbsp; &nbsp;by Category & Location
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('reports.assets.by-department') }}" href="{{ route('reports.assets.by-department') }}">
                                    <i class="far fa-building"></i>&nbsp; &nbsp;by Department
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('reports.assets.by-user') }}" href="{{ route('reports.assets.by-user') }}">
                                    <i class="far fa-user"></i>&nbsp; &nbsp;by Assigned User
                                </a>
                            </div>
                            @endcan
                            @can('facility.reports.view')
                            <div class="dropdown-menu-column">
                                <h6 class="dropdown-header">Facility Management</h6>
                                <a class="dropdown-item {{ areActiveRoutes('reports.facility.daily') }}" href="{{ route('reports.facility.daily') }}">
                                    <i class="far fa-calendar-day"></i>&nbsp; &nbsp;Daily Report
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('reports.facility.weekly') }}" href="{{ route('reports.facility.weekly') }}">
                                    <i class="far fa-calendar-week"></i>&nbsp; &nbsp;Weekly Report
                                </a>
                            </div>
                            @endcan
                            
                        </div>
                    </div>
                </li>
                <li class="nav-item {{ areActiveRoutes('guides.*') }}">
                    <a class="nav-link" href="{{ route('guides.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="far fa-book-open"></i>&nbsp;
                        </span>
                        <span class="nav-link-title">Guides</span>
                    </a>
                </li>
                <li class="nav-item dropdown {{ areActiveRoutes(['options.*']) }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="far fa-sliders"></i>&nbsp;
                        </span>
                        <span class="nav-link-title">Options</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['options.*']) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ areActiveRoutes('options.assets.*') }}" href="{{ route('options.assets.index') }}">
                                    <i class="far fa-boxes-stacked"></i>&nbsp; &nbsp;Assets
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('options.asset-categories.*') }}" href="{{ route('options.asset-categories.index') }}">
                                    <i class="far fa-layer-group"></i>&nbsp; &nbsp;Asset Categories
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('options.locations.*') }}" href="{{ route('options.locations.index') }}">
                                    <i class="far fa-map-marker-alt"></i>&nbsp; &nbsp;Locations
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown {{ areActiveRoutes(['users.*', 'roles.*', 'permissions.*', 'departments.*']) }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="far fa-user-crown"></i>&nbsp;
                        </span>
                        <span class="nav-link-title">Admin</span>
                    </a>
                    <div class="dropdown-menu {{ areOpenRoutes(['users.*', 'roles.*', 'permissions.*', 'departments.*']) }}">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">
                                <a class="dropdown-item {{ areActiveRoutes('users.*') }}" href="{{ route('users.index') }}">
                                    <i class="far fa-users"></i>&nbsp; &nbsp;Users
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('roles.*') }}" href="{{ route('roles.index') }}">
                                    <i class="far fa-user-question"></i>&nbsp; &nbsp;Roles
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('permissions.*') }}" href="{{ route('permissions.index') }}">
                                    <i class="far fa-user-key"></i>&nbsp; &nbsp;Permissions
                                </a>
                                <a class="dropdown-item {{ areActiveRoutes('departments.*') }}" href="{{ route('departments.index') }}">
                                    <i class="far fa-building-user"></i>&nbsp; &nbsp;Departments
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
                            <i class="far fa-right-from-bracket"></i>&nbsp;
                        </span>
                        <span class="nav-link-title">Leave Impersonating</span>
                    </a>
                    @else
                    <a class="nav-link" href="#" onclick="confirm('Are you sure?'); event.preventDefault(); document.getElementById('navbar-logout-form').submit();">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="far fa-right-from-bracket"></i>&nbsp;
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
