@if(session()->has('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <div class="alert-icon">
                                <i class="fa-regular fa-thumbs-up"></i>
                            </div>
                            <div>
                                <h4 class="alert-heading">Well done!</h4>
                                <div class="alert-description">
                                    {{ Session::get('success') }}
                                </div>
                            </div>
                        </div>
@endif

@if(session()->has('warning'))
                        <div class="alert alert-warning alert-dismissible" role="alert">
                            <div class="alert-icon">
                                <i class="fa-regular fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <h4 class="alert-heading">Warning!</h4>
                                <div class="alert-description">
                                    {{ Session::get('warning') }}
                                </div>
                            </div>
                        </div>
@endif

@if(session()->has('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <div class="alert-icon">
                                <i class="fa-regular fa-octagon-exclamation"></i>
                            </div>
                            <div>
                                <h4 class="alert-heading">Error!</h4>
                                <div class="alert-description">
                                    {{ Session::get('error') }}
                                </div>
                            </div>
                        </div>
@endif

@if(session()->has('info'))
                        <div class="alert alert-info alert-dismissible" role="alert">
                            <div class="alert-icon">
                                <i class="fa-regular fa-circle-info"></i>
                            </div>
                            <div>
                                <h4 class="alert-heading">Info</h4>
                                <div class="alert-description">
                                    {{ Session::get('info') }}
                                </div>
                            </div>
                        </div>
@endif

@if ($errors && $errors->any())
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <div class="alert-icon">
                                <i class="fa-regular fa-octagon-exclamation"></i>
                            </div>
                            <div>
                                <h4 class="alert-heading">Error!</h4>
                                <div class="alert-description">
                                    <ul class="alert-list">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
@endif

@if (session('resent'))
                        <div class="alert alert-info alert-dismissible" role="alert">
                            <div class="alert-icon">
                                <i class="fa-regular fa-circle-info"></i>
                            </div>
                            <div>
                                <h4 class="alert-heading">Info!</h4>
                                <div class="alert-description">
                                    {{ __('A fresh verification link has been sent to your email address.') }}
                                </div>
                            </div>
                        </div>
@endif


@if (session('status'))
                        <div class="alert alert-info alert-dismissible" role="alert">
                            <div class="alert-icon">
                                <i class="fa-regular fa-circle-info"></i>
                            </div>
                            <div>
                                <h4 class="alert-heading">Info!</h4>
                                <div class="alert-description">
                                    {{ session('status') }}
                                </div>
                            </div>
                        </div>
@endif