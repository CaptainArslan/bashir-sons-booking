<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>

    <!-- Bootstrap CSS -->
    <link href="{{ asset('frontend/assets/css/bootstrap.min.css') }}" rel="stylesheet">

    <!--<link rel="stylesheet" href="{{ asset('frontend/assets/css/bootstrap-icons.min.css') }}">-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="{{ asset('frontend/assets/css/style.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/responsive.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/sweetalert2.min.css') }}">
    <script src="{{ asset('frontend/assets/js/jquery-3.7.1.js') }}"></script>
    @yield('styles')
</head>

<body>
    <div class="top-bar text-center py-1 bg-light text-theme">
        Book Your Tickets Now! Call UAN: 041-111-737-737
    </div>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ asset('frontend/assets/img/logo 1.png') }}" alt="Logo" />
            </a>
            <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('services') }}">Our Services</a>
                    </li>


                    <li class="nav-item">
                        @auth
                            <a class="nav-link" href="{{ route('bookings') }}">Book your Ticket</a>
                        @endauth
                    </li>

                    <li class="nav-item"><a class="nav-link" href="{{ route('about-us') }}">About us</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact
                            us</a>
                    </li>

                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                    @endauth
                </ul>
                <span class="navbar-text ms-lg-3 bg-light text-theme h4 p-2 rounded uan-number">
                    UAN: 041 111 737 737
                </span>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer>
        <!-- Top section (dark blue background) -->
        <div class="footer-top">
            <div class="container py-5">
                <div class="row gx-4 gy-4">
                    <!-- ===== Column 1: Support ===== -->
                    <div class="col-md-4">
                        <h5 class="footer-title text-white">Support</h5>
                        <div class="footer-links">
                            <a href="{{ route('booking') }}">Book Your Ticket</a>
                            <a href="#">Privacy Policy</a>
                            <a href="{{ route('contact') }}">Contact us</a>
                            <a href="#">FAQs</a>
                            <a href="{{ route('about') }}">About us</a>
                        </div>
                    </div>

                    <!-- ===== Column 2: Visit Us ===== -->
                    <div class="col-md-4">
                        <h5 class="footer-title text-white">Visit Us</h5>
                        <div class="footer-address text-white">
                            <!-- Head Office -->
                            <p class="fw-semibold mb-1">Head Office:</p>
                            <p class="mb-2">
                                Bashir Sons Office<br />
                                P-68, Pakimari,<br />
                                Behind General Bus Stand,<br />
                                Faisalabad - Pakistan.
                            </p>

                            <!-- Sub Office -->
                            <p class="fw-semibold mb-1">Sub Office:</p>
                            <p>
                                Bashir Sons Office<br />
                                Nadir Bus Terminal,<br />
                                Jinnah Colony,<br />
                                Faisalabad - Pakistan.
                            </p>
                        </div>
                    </div>

                    <!-- ===== Column 3: Contact / App / Social ===== -->
                    <div class="col-md-4 text-md-end">
                        <!-- Phone -->
                        <div class="footer-phone text-white">
                            UAN 041 111 737 737
                        </div>

                        <!-- Email -->
                        <div class="footer-email text-white">
                            info@bashirsonsgroup.com
                        </div>

                        <!-- Install App Text -->
                        <div class="footer-app-text text-white">
                            Install Our App for Easy Booking!
                        </div>
                        <!-- Google Play Badge (replace src with your badge) -->
                        <a href="#" target="_blank" rel="noopener" class="d-inline-block mb-3">
                            <img src="{{ asset('frontend/assets/img/Google_Play_Store_badge_EN.svg') }}"
                                alt="Get it on Google Play" class="img-fluid google-play-badge" />
                        </a>

                        <!-- Social Icons -->
                        <div class="social-icons">
                            <a href="#" aria-label="Facebook">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" aria-label="Twitter">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="#" aria-label="TikTok">
                                <i class="bi bi-tiktok"></i>
                            </a>
                            <a href="#" aria-label="Instagram">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="#" aria-label="LinkedIn">
                                <i class="bi bi-linkedin"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container -->
        </div>
        <!-- /.footer-top -->

        <!-- Divider -->
        <div class="container-fluid px-0">
            <hr class="border-secondary m-0" />
        </div>

        <!-- Bottom copyright bar -->
        <div class="footer-bottom">
            <div class="container py-3">
                <small>Copyright © {{ date('Y') }}, Bashir Sons. All rights reserved.</small>
            </div>
        </div>
    </footer>

    <script src="{{ asset('frontend/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/script.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/sweetalert2.min.js') }}"></script>
    @yield('scripts')
    <script>
        @if (Session::has('message'))
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });

            var type = "{{ Session::get('alert-type', 'info') }}";
            switch (type) {
                case 'info':
                    Toast.fire({
                        icon: 'info',
                        title: "{{ Session::get('message') }}"
                    });
                    break;

                case 'success':
                    Toast.fire({
                        icon: 'success',
                        title: "{{ Session::get('message') }}"
                    });
                    break;

                case 'warning':
                    Toast.fire({
                        icon: 'warning',
                        title: "{{ Session::get('message') }}"
                    });
                    break;

                case 'error':
                    Toast.fire({
                        icon: 'error',
                        title: "{{ Session::get('message') }}"
                    });
                    break;
            }
        @endif
    </script>


</body>

</html>
