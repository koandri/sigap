@section('title', 'Create User')

@extends('layouts.app')

@section('content')
            <!-- BEGIN PAGE HEADER -->
            <div class="page-header d-print-none" aria-label="Page header">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title">@yield('title')</h2>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE HEADER -->
            <!-- BEGIN PAGE BODY -->
            <div class="page-body">
                <div class="container-xl">
                    <div class="row">
                        @include('layouts.alerts')
                    </div>
                    
                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <form class="card" action="{{ route('users.store') }}" method="POST">
                                @csrf
                                <div class="card-header">
                                    <h3 class="card-title">@yield('title')</h3>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label required">Name</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" min="10" max="100" maxlength="100" required />
                                            <small class="form-hint">
                                                Your password must be 8-20 characters long, contain letters and numbers, and must not contain spaces, special characters, or emoji.
                                            </small>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="email" class="col-sm-2 col-form-label required">Email</label>
                                        <div class="col-sm-10">
                                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" min="10" max="100" maxlength="100" required />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label required">Mobile Phone No</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="mobilephone_no" class="form-control" value="{{ old('mobilephone_no') }}" min="11" max="16" maxlength="16" required />
                                            <div class="form-text">Must start with 628xxxx</div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label">Manager</label>
                                        <div class="col">
                                            <select class="form-select" name="manager_id">
                                                <option value=""></option>
                                                @foreach ($managers as $manager)
                                                <option value="{{$manager['value']}}">{{$manager['label']}}</option>
                                                @endforeach
                                            </select>
                                        </div> 
                                    </div>
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label">Locations</label>
                                        <div class="col-sm-10">
                                            @foreach ($locations as $location)
                                            <label class="form-check">
                                                <input class="form-check-input" name="locations[]" type="checkbox" value="{{ $location['value'] }}">
                                                <span class="form-check-label">{{ $location['label'] }}</span>
                                            </label>
                                            @endforeach
                                        </div> 
                                    </div>
                                    <div class="row mb-3">
                                        <label for="departments" class="col-sm-2 col-form-label">Departments</label>
                                        <div class="col-sm-10">
                                            @foreach ($departments as $department)
                                            <div class="col-3">
                                                <label class="form-check">
                                                    <input class="form-check-input" name="departments[]" value="{{ $department->id }}" type="checkbox">
                                                    <span class="form-check-label">{{ $department->name }}</span>
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="roles" class="col-sm-2 col-form-label">Roles <span class="text-danger">*</span></label>
                                        <div class="col-sm-10">
                                            @foreach ($roles as $role)
                                            <div class="col-3">
                                                <label class="form-check">
                                                    <input class="form-check-input" name="roles[]" value="{{ $role->id }}" type="checkbox">
                                                    <span class="form-check-label">{{ $role->name }}</span>
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-2"></div>
                                        <div class="col-sm-10">
                                            <div class="accordion" id="permissionsAccordion">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#directPermissions" aria-expanded="false" aria-controls="directPermissions">
                                                            <i class="far fa-exclamation-triangle me-2 text-warning"></i>
                                                            Advanced: Direct Permissions (Use Sparingly)
                                                        </button>
                                                    </h2>
                                                    <div id="directPermissions" class="accordion-collapse collapse" data-bs-parent="#permissionsAccordion">
                                                        <div class="accordion-body">
                                                            @foreach ($groupedPermissions as $prefix => $group)
                                                            <div class="mb-4">
                                                                <h6 class="mb-2 border-bottom pb-1">
                                                                    <i class="far fa-folder me-2 text-primary"></i>
                                                                    {{ $group['name'] }}
                                                                </h6>
                                                                <div class="row">
                                                                    @foreach ($group['permissions'] as $permission)
                                                                    <div class="col-md-4 mb-2">
                                                                        <label class="form-check">
                                                                            <input class="form-check-input" name="permissions[]" value="{{ $permission->id }}" type="checkbox">
                                                                            <span class="form-check-label">
                                                                                {{ $permission->name }}
                                                                                @if($permission->description)
                                                                                    <small class="text-muted d-block">{{ $permission->description }}</small>
                                                                                @endif
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer clearfix">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                    <a href="{{ route('users.index') }}" class="btn float-end">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection