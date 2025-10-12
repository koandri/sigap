@section('title', 'Verify Email')

@extends('layouts.auth')

@section('content')
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Verify Your Email Address</h2>
                    <p class="text-secondary mb-4">
                        {{ __('Before proceeding, please check your email for a verification link.') }}
                    </p>
                    <form method="POST" action="{{ route('verification.resend') }}" autocomplete="off" novalidate>
                        @csrf
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">Resend Verification Email</button>
                        </div>
                    </form>
                </div>
@endsection

