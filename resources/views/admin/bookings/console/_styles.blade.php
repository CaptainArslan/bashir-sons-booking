<style>
    /* Responsive Layout Styles */
    /* For screens smaller than 1440px: Seat Map and Booking Summary side by side (col-6 each), Passenger List below (col-12) */
    @media (max-width: 1439.98px) {
        .booking-console-row .col-xxl-3,
        .booking-console-row .col-xxl-5 {
            flex: 0 0 auto;
            width: 50%;
        }
        
        .booking-console-row .col-xxl-4 {
            flex: 0 0 auto;
            width: 100%;
            margin-top: 1rem;
        }
    }

    @media (max-width: 1199px) {
        .col-lg-3,
        .col-lg-5,
        .col-lg-4 {
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 767px) {
        .col-md-6 {
            font-size: 0.875rem;
        }

        .form-control-sm {
            font-size: 0.8rem !important;
        }

        .small {
            font-size: 0.75rem !important;
        }

        .seat-map-container {
            padding: 0.75rem !important;
        }

        .seat-btn {
            min-width: 38px !important;
            min-height: 38px !important;
            width: 38px !important;
            height: 38px !important;
            font-size: 0.75rem !important;
        }

        .seat-aisle {
            width: 30px !important;
            font-size: 0.65rem !important;
        }
    }

    @media (max-width: 576px) {
        .seat-btn {
            min-width: 32px !important;
            min-height: 32px !important;
            width: 32px !important;
            height: 32px !important;
            font-size: 0.7rem !important;
        }

        .seat-aisle {
            width: 24px !important;
            font-size: 0.6rem !important;
        }

        .seat-gender-badge {
            width: 14px !important;
            height: 14px !important;
            font-size: 0.6rem !important;
        }
    }

    /* Seat map styling - Minimal Clean Design */
    .seat-map-container {
        background: #ffffff;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        min-height: auto;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .seat-row-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .seat-pair-left,
    .seat-pair-right {
        display: flex;
        gap: 0.375rem;
    }

    .seat-aisle {
        width: 32px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 0.65rem;
        font-weight: 500;
    }

    .seat-grid {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        padding: 0;
    }

    /* Seat button styling - Compact Clean Design */
    .seat-btn {
        min-width: 42px;
        min-height: 42px;
        width: 42px;
        height: 42px;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    /* Gender badge styling - Top right corner */
    .seat-gender-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        line-height: 1;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        z-index: 10;
    }

    .seat-gender-badge.male-badge {
        background: #3B82F6;
    }

    .seat-gender-badge.female-badge {
        background: #EC4899;
    }

    .seat-btn:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .seat-btn:disabled {
        cursor: not-allowed;
        opacity: 0.9;
    }

    /* Seat status colors - Matching Image */
    .seat-available {
        background: #E2E8F0;
        color: #334155;
        border-color: #cbd5e1;
    }

    .seat-selected {
        background: #3B82F6;
        color: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
    }

    .seat-booked-male {
        background: #22D3EE;
        color: #ffffff;
        border-color: #06b6d4;
    }

    .seat-booked-female {
        background: #EC4899;
        color: #ffffff;
        border-color: #db2777;
    }

    .seat-held {
        background: #fbbf24;
        color: #78350f;
        border-color: #f59e0b;
    }

    /* Card body compact padding */
    .card-body.p-2 {
        padding: 0.5rem !important;
    }

    /* Compact card header */
    .card-header {
        padding: 0.75rem 1rem !important;
    }

    /* Scrollable areas */
    .scrollable-content {
        max-height: calc(100vh - 300px);
        overflow-y: auto;
    }

    @media (max-width: 1440px) {
        .scrollable-content {
            max-height: calc(100vh - 280px);
        }
    }

    @media (max-width: 767px) {
        .scrollable-content {
            max-height: calc(100vh - 250px);
        }

        .card-header {
            padding: 0.5rem 0.75rem !important;
        }
    }

    /* Badge sizing */
    .badge.small {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }

    /* Alert sizing */
    .alert.small {
        padding: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }

    /* Form label sizing */
    .form-label.small {
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
    }

    /* Passenger info container */
    #passengerInfoContainer {
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 0, 0, 0.2) rgba(0, 0, 0, 0.1);
    }

    #passengerInfoContainer::-webkit-scrollbar {
        width: 6px;
    }

    #passengerInfoContainer::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.1);
    }

    #passengerInfoContainer::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 3px;
    }

    /* Seat map legend - compact horizontal layout at bottom */
    .seat-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        justify-content: center;
        align-items: center;
        width: 100%;
        padding: 0.75rem 0.5rem 0;
        margin-top: 0.75rem;
        border-top: 1px solid #e5e7eb;
    }

    .seat-legend-item {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.75rem;
        color: #4b5563;
    }

    .seat-legend-indicator {
        width: 14px;
        height: 14px;
        border-radius: 3px;
        border: 1px solid;
        flex-shrink: 0;
    }

    /* Legend indicators match exact seat colors */
    .seat-legend-indicator.available {
        background: #E2E8F0;
        border-color: #cbd5e1;
    }

    .seat-legend-indicator.selected {
        background: #3B82F6;
        border-color: #2563eb;
    }

    .seat-legend-indicator.booked-male {
        background: #22D3EE;
        border-color: #06b6d4;
    }

    .seat-legend-indicator.booked-female {
        background: #EC4899;
        border-color: #db2777;
    }

    .seat-legend-indicator.held {
        background: #fbbf24;
        border-color: #f59e0b;
    }

    @media (max-width: 767px) {
        .seat-legend {
            gap: 0.5rem;
            padding: 0.5rem 0.25rem 0;
            margin-top: 0.5rem;
        }

        .seat-legend-item {
            font-size: 0.7rem;
            gap: 0.25rem;
        }

        .seat-legend-indicator {
            width: 12px;
            height: 12px;
        }
    }

    /* Print button styling */
    #printPassengerListBtn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    @media print {
        body * {
            visibility: hidden;
        }
        .print-passenger-table,
        .print-passenger-table * {
            visibility: visible;
        }
        .print-passenger-table {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }

    /* Error field highlighting */
    .is-invalid,
    .border-danger {
        border-color: #dc3545 !important;
        border-width: 2px !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        animation: errorPulse 0.5s ease-in-out;
    }

    @keyframes errorPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }
        50% {
            box-shadow: 0 0 0 0.3rem rgba(220, 53, 69, 0.4);
        }
        100% {
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
    }

    /* Remove error highlight on input */
    input.is-invalid:focus,
    select.is-invalid:focus,
    textarea.is-invalid:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
</style>

