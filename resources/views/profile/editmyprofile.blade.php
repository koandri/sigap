@section('title', 'Edit My Profile')

@extends('layouts.app')

@section('title', 'Blank')

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

                    @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updateProfileInformation()))
                    <div class="row row-deck row-cards">
                      @include('profile.update-profile-information-form')
                    </div>
                    @endif

                    @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                    <hr />
                    <div class="row row-deck row-cards">
                      @include('profile.update-password-form')
                    </div>
                    @endif

                    @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::twoFactorAuthentication()))
                    <hr />
                    <div class="row row-deck row-cards">
                      @include('profile.two-factor-authentication-form')
                    </div>
                    @endif
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection