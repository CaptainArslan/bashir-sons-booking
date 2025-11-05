@extends('admin.layouts.app')

@section('title', 'Booking Console')

@section('content')
    @livewire('admin.booking-console')
@endsection

@section('scripts')
    <script>
        // Initialize Select2 for dropdowns after Livewire loads
        document.addEventListener('livewire:init', () => {
            Livewire.on('trip-loaded', () => {
                if (typeof $ !== 'undefined' && $.fn.select2) {
                    $('.select2').select2({
                        width: 'resolve'
                    });
                }
            });
        });
    </script>
@endsection
