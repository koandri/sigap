@section('title', '2FA Challenge')

@extends('layouts.auth')

@section('content')
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Authenticate Your Account</h2>
                    <form method="POST" action="{{ route('two-factor.login') }}" autocomplete="off" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" class="form-control"  autofocus autocomplete="one-time-code" required autofocus />
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">Sign in</button>
                        </div>
                    </form>
                </div>
@endsection

