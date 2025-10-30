@section('title', 'Confirm Password')

@extends('layouts.auth')

@section('content')
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Confirm Password</h2>
                    <form method="POST" action="{{ route('password.confirm') }}" autocomplete="off" novalidate>
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">
                                Password
                            </label>
                            <div class="input-group input-group-flat">
                                <input type="password" id="password" name="password" class="form-control" placeholder="Your password" required autocomplete="off" />
                                <span class="input-group-text">
                                    <a href="#" class="link-secondary" title="Show password" onclick="showPassword()">
                                        <i class="far fa-eye"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">Confirm</button>
                        </div>
                    </form>
                </div>
@endsection
