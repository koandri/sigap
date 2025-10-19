@section('title', 'Login')

@extends('layouts.auth')

@section('content')
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Login to SIGaP</h2>
                    <form method="POST" action="{{ route('login') }}" autocomplete="off" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Email address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="your@email.com" autocomplete="off" required autofocus />
                        </div>
                        <div class="mb-2">
                            <label class="form-label">
                                Password
                                <span class="form-label-description"><a href="{{ route('password.request') }}">I forgot password</a></span>
                            </label>
                            <div class="input-group input-group-flat">
                                <input type="password" id="password" name="password" class="form-control" placeholder="Your password" required autocomplete="off" />
                                <span class="input-group-text">
                                    <a href="#" class="link-secondary" title="Show password" onclick="showPassword()">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-check">
                                <input type="checkbox" name="remember" class="form-check-input" />
                                <span class="form-check-label">Remember me</span>
                            </label>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">Sign in</button>
                        </div>
                    </form>
                </div>
                <div class="hr-text">or</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <a href="{{ route('keycloak.login') }}" class="btn btn-4 w-100">
                                <i class="fa-kit fa-keycloak"></i> &nbsp;Login with Keycloak
                            </a>
                        </div>
                    </div>
                </div>
@endsection

@push('scripts')
<script>
    function showPassword() {
    var x = document.getElementById("password");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
    }
</script>
@endpush