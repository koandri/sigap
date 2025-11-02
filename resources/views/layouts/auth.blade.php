<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | SIGaP</title>
    <meta name="title" content="SIGaP" />
    <meta name="author" content="Andri Halim Gunawan" />
    <link rel="apple-touch-icon" sizes="180x180" href="/imgs/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/imgs/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/imgs/favicon-16x16.png">
    <link rel="manifest" href="/imgs/site.webmanifest">
    <link rel="stylesheet" href="/assets/tabler/css/tabler.min.css" />
    <link rel="stylesheet" href="/assets/tabler/css/tabler-flags.min.css" />
    <link rel="stylesheet" href="/assets/tabler/css/tabler-payments.min.css" />
    <link rel="stylesheet" href="/assets/tabler/css/tabler-socials.min.css" />
    <link rel="stylesheet" href="/assets/tabler/css/tabler-vendors.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/assets/css/custom.css" />
    @stack('css')
</head>

<body class="auth-page">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <!-- BEGIN NAVBAR LOGO -->
                <a href="{{ route('home') }}" aria-label="SIGaP">
                    <img src="/imgs/logo.png" alt="SIGaP Logo" class="navbar-brand-image" />
                </a>
                <!-- END NAVBAR LOGO -->
            </div>

            @if (session('status'))
            <div class="alert alert-info alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon far fa-info"></i>&nbsp; &nbsp;Alert!</h5>
                {{ session('status') }}
            </div>
            @endif
            @include('layouts.alerts')

            <div class="card card-md">
                @yield('content')
            </div>
        </div>
    </div>
    
    <script src="/assets/tabler/js/tabler.min.js"></script>
    <script src="https://kit.fontawesome.com/332a1234a1.js" crossorigin="anonymous"></script>
    @stack('scripts')
</body>

</html>