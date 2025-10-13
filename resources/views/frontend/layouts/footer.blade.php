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
                        <a href="{{ route('about-us') }}">About us</a>
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
