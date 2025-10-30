@section('title', 'Reset Password')

@extends('layouts.app')

@section('content')
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Login to SIGaP</h2>
                    <form method="POST" action="{{ route('password.update') }}" autocomplete="off" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Email address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $request->email) }}" placeholder="your@email.com" autocomplete="off" required autofocus />
                        </div>
                        <div class="mb-2">
                            <label class="form-label">
                                New Password
                            </label>
                            <div class="input-group input-group-flat">
                                <input type="password" id="password" name="password" class="form-control" placeholder="Your new password" required autocomplete="off" />
                                <span class="input-group-text">
                                    <a href="#" class="link-secondary" title="Show password" onclick="showPassword()">
                                        <i class="far fa-eye"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">
                                Confirm New Password
                            </label>
                            <div class="input-group input-group-flat">
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm your new password" required autocomplete="off" />
                                <span class="input-group-text">
                                    <a href="#" class="link-secondary" title="Show password" onclick="showPassword2()">
                                        <i class="far fa-eye"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                        </div>
                    </form>
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

    function showPassword2() {
        var x = document.getElementById("password_confirmation");
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }
    }
</script>
@endpush
