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
                <li class="nav-item"><a class="nav-link" href="{{ route('services') }}">Our Services</a></li>
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('bookings') }}">Book your Ticket</a>
                    </li>
                @endauth
                <li class="nav-item"><a class="nav-link" href="{{ route('about-us') }}">About us</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact us</a>
                </li>
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <h6 class="dropdown-header">
                                    <i class="bi bi-person me-2"></i>Account
                                </h6>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person me-2"></i>Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.bookings') }}">
                                    <i class="bi bi-ticket-perforated me-2"></i>My Bookings
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('2fa.show') }}">
                                    <i class="bi bi-shield-check me-2"></i>Two-Factor Authentication
                                </a>
                            </li>
                            @can('access admin panel')
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
                                </a>
                            </li>
                            @endcan
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
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
