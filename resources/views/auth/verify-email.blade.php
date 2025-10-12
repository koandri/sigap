@section('title', 'Verify Email')

@extends('layouts.app')

@section('content')
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Login to SIGaP</h2>
                    <p class="text-secondary mb-4">
                        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                    </p>
                    <form method="POST" action="{{ route('verification.send') }}" autocomplete="off" novalidate>
                        @csrf
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">{{ __('Resend Verification Email') }}</button>
                        </div>
                    </form>
                </div>
@endsection
