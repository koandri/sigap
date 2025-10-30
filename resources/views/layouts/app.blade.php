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
    <!-- Lightbox2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">
    @stack('css')
</head>

<body class="layout-fluid">
    <div class="page">
        @include('layouts.aside')
        <div class="page-wrapper">
            @yield('content')
            @include('layouts.footer')
        </div>
    </div>
    
    <!-- jQuery (required for Lightbox and other components) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Lightbox2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    
    <script src="/assets/tabler/js/tabler.min.js"></script>
    <script src="https://kit.fontawesome.com/332a1234a1.js" crossorigin="anonymous"></script>
    
    <!-- Lightbox Configuration -->
    <script>
        // Configure Lightbox after page is fully loaded
        window.addEventListener('load', function() {
            if (typeof lightbox !== 'undefined' && typeof lightbox.option === 'function') {
                try {
                    lightbox.option({
                        'resizeDuration': 200,
                        'wrapAround': true,
                        'albumLabel': 'Image %1 of %2',
                        'fadeDuration': 300,
                        'imageFadeDuration': 300,
                        'positionFromTop': 50,
                        'showImageNumberLabel': true,
                        'disableScrolling': true
                    });
                } catch (e) {
                }
            }
        });
    </script>
    
    @stack('scripts')
</body>

</html>