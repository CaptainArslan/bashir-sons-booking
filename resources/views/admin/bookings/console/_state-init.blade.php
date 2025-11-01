{{-- State Management and Initialization --}}

// ========================================
// STATE MANAGEMENT
// ========================================
let appState = {
    isAdmin: {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }},
    userId: {{ auth()->user()->id }},
    userTerminalId: {{ auth()->user()->terminal_id ?? 'null' }},
    terminals: [],
    routeStops: [],
    timetableStops: [],
    tripData: null,
    seatMap: {},
    selectedSeats: {},
    passengerInfo: {}, // Store passenger details
    pendingSeat: null,
    tripLoaded: false,
    fareData: null,
    baseFare: 0,
    discountAmount: 0, // Store discount amount for booking
    lockedSeats: {}, // Track locked seats: {seatNumber: userId}
    echoChannel: null, // Echo channel for this trip
};

// ========================================
// INITIALIZATION
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    fetchTerminals();
    setupWebSocket();
    togglePaymentFields(); // Initialize payment fields visibility
    updatePassengerForms(); // Initialize passenger forms with 1 mandatory passenger
});

