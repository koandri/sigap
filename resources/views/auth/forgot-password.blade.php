@section('title', 'Forgot Password')

@extends('layouts.auth')

@section('content')
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Forgot Your Password?</h2>
                    <p class="text-secondary mb-4">Enter your email address and your password will be reset and emailed to you.</p>
                    <form method="POST" action="{{ route('password.email') }}" autocomplete="off" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Email address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="your@email.com" autocomplete="off" required autofocus />
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">Send Me Reset Password Link</button>
                        </div>
                    </form>
                </div>
@endsection
