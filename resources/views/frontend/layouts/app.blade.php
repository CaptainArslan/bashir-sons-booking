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

    @include('frontend.layouts.navbar')

    @yield('content')

    @include('frontend.layouts.footer')

    <script src="{{ asset('frontend/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/script.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/sweetalert2.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
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

    <script>
        // Initialize Input Masking for CNIC and Phone in Frontend
        $(document).ready(function() {
            // CNIC Mask: Format 34101-1111111-1 (5 digits - 7 digits - 1 digit)
            $('input[name="cnic"], input[id="cnic"], input[name="driver_cnic"], input[id="driverCnic"]').each(function() {
                if ($(this).attr('type') === 'number') {
                    $(this).attr('type', 'text');
                }
                $(this).inputmask('99999-9999999-9', {
                    placeholder: '_',
                    clearMaskOnLostFocus: false,
                    showMaskOnHover: true,
                    showMaskOnFocus: true
                });
            });

            // Phone Mask: Format 0317-7777777 (4 digits - 7 digits)
            $('input[name="phone"], input[id="phone"], input[name="driver_phone"], input[id="driverPhone"], input[name="host_phone"], input[id="hostPhone"]').each(function() {
                if ($(this).attr('type') === 'number' || $(this).attr('type') === 'tel') {
                    $(this).attr('type', 'text');
                }
                $(this).inputmask('9999-9999999', {
                    placeholder: '_',
                    clearMaskOnLostFocus: false,
                    showMaskOnHover: true,
                    showMaskOnFocus: true
                });
            });
        });
    </script>

</body>

</html>
