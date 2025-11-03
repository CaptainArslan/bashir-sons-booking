<style>
    /* Responsive Layout Styles */
    @media (max-width: 1199px) {

        .col-lg-3,
        .col-lg-5,
        .col-lg-4 {
            margin-bottom: 1.5rem;
        }
    }

    @media (max-width: 767px) {
        .col-md-6 {
            font-size: 0.9rem;
        }

        .form-control-sm {
            font-size: 0.8rem !important;
        }

        .small {
            font-size: 0.75rem !important;
        }
    }

    /* Seat map styling - Clean Modern Design like Image */
    .seat-map-container {
        background: #F8FAFC;
        padding: 1.5rem;
        border-radius: 12px;
        min-height: 500px;
    }

    .seat-row-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.75rem;
    }

    .seat-pair-left,
    .seat-pair-right {
        display: flex;
        gap: 0.5rem;
    }

    .seat-aisle {
        width: 40px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 0.7rem;
    }

    .seat-grid {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        padding: 0;
    }

    /* Seat button styling - Clean Design */
    .seat-btn {
        min-width: 45px;
        min-height: 45px;
        width: 45px;
        height: 45px;
        font-size: 0.85rem;
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
        transform: scale(1.05);
        border-color: #94a3b8;
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

    /* Scrollable areas */
    .scrollable-content {
        max-height: calc(100vh - 300px);
        overflow-y: auto;
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

    /* Seat map legend - horizontal flex layout */
    .seat-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
        width: 100%;
    }

    .seat-legend .badge {
        flex-shrink: 0;
        white-space: nowrap;
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
</style>

