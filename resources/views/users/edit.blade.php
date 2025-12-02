@section('title', 'Edit User')

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
                            <form class="card" action="{{ route('users.update', $user) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="card-header">
                                    <h3 class="card-title">@yield('title')</h3>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label required">Name</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" min="10" max="100" maxlength="100" required />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="email" class="col-sm-2 col-form-label required">Email</label>
                                        <div class="col-sm-10">
                                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" min="10" max="100" maxlength="100" required />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label required">Mobile Phone No</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="mobilephone_no" class="form-control" value="{{ old('mobilephone_no', $user->mobilephone_no) }}" min="11" max="16" maxlength="16" required />
                                            <div class="form-text">Must start with 628xxxx</div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label">Manager</label>
                                        <div class="col">
                                            <select class="form-select" name="manager_id">
                                                <option value=""></option>
                                                @foreach ($managers as $manager)
                                                @if ($manager['value'] == $user->manager_id)
                                                <option value="{{$manager['value']}}" selected>{{$manager['label']}}</option>
                                                @else
                                                <option value="{{$manager['value']}}">{{$manager['label']}}</option>
                                                @endif
                                                @endforeach
                                            </select>
                                        </div> 
                                    </div>
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label">Locations</label>
                                        <div class="col-sm-10">
                                            @foreach ($locations as $location)
                                            <label class="form-check">
                                                @if (isset($user->locations))
                                                @if (in_array($location['value'], $user->locations))
                                                <input class="form-check-input" name="locations[]" type="checkbox" value="{{ $location['value'] }}" checked>
                                                @else
                                                <input class="form-check-input" name="locations[]" type="checkbox" value="{{ $location['value'] }}">
                                                @endif
                                                @else
                                                <input class="form-check-input" name="locations[]" type="checkbox" value="{{ $location['value'] }}">
                                                @endif
                                                <span class="form-check-label">{{ $location['label'] }}</span>                                
                                            </label>
                                            @endforeach
                                        </div> 
                                    </div>
                                    <div class="row mb-3">
                                        <label for="departments" class="col-sm-2 col-form-label">Departments</label>
                                        <div class="col-sm-10">
                                            @foreach ($user->departments()->orderBy('name')->get() as $user_department)
                                            <div class="col-3">
                                                <label class="form-check">
                                                    <input class="form-check-input" name="departments[]" type="checkbox" value="{{ $user_department->id }}" checked>
                                                    <span class="form-check-label">{{ $user_department->name }}</span>
                                                </label>
                                            </div>
                                            @endforeach
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
                                            @foreach ($user->roles()->orderBy('name')->get() as $user_role)
                                            <div class="col-3">
                                                <label class="form-check">
                                                    <input class="form-check-input" name="roles[]" type="checkbox" value="{{ $user_role->id }}" checked>
                                                    <span class="form-check-label">{{ $user_role->name }}</span>
                                                </label>
                                            </div>
                                            @endforeach
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
                                                        <button class="accordion-button {{ $user->permissions()->count() > 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#directPermissions" aria-expanded="{{ $user->permissions()->count() > 0 ? 'true' : 'false' }}" aria-controls="directPermissions">
                                                            <i class="far fa-exclamation-triangle me-2 text-warning"></i>
                                                            Advanced: Direct Permissions (Use Sparingly)
                                                            @if($user->permissions()->count() > 0)
                                                                <span class="badge bg-warning ms-2">{{ $user->permissions()->count() }}</span>
                                                            @endif
                                                        </button>
                                                    </h2>
                                                    <div id="directPermissions" class="accordion-collapse collapse {{ $user->permissions()->count() > 0 ? 'show' : '' }}" data-bs-parent="#permissionsAccordion">
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
                                                                            <input class="form-check-input" name="permissions[]" value="{{ $permission->id }}" type="checkbox" 
                                                                                {{ in_array($permission->id, old('permissions', $user->permissions->pluck('id')->toArray())) ? 'checked' : '' }}>
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
                                    <div class="row mb-3">
                                        <label for="active" class="col-sm-2 col-form-label required">Active?</label>
                                        <div class="col-sm-10">
                                            <select class="form-select" name="active" required>
                                                <option value="1" @selected(old('active', $user->active) == 1)>Yes</option>
                                                <option value="1" @selected(old('active', $user->active) == 0)>No</option>
                                            </select>
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