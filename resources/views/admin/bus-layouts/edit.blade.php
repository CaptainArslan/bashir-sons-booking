@extends('admin.layouts.app')

@section('title', 'Edit Bus Layout')

@section('styles')
<style>
    .bus-layout-card {
        border-left: 4px solid #0d6efd;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 8px 8px 0 0;
    }
    
    .card-header-info h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }
    
    .form-control, .form-select {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 6px;
    }
    
    .card-body {
        padding: 1rem !important;
    }
    
    .row {
        margin-bottom: 0.5rem;
    }
    
    .btn {
        border-radius: 6px;
        font-weight: 500;
    }
    
    .bus-layout-info-card {
        border-left: 3px solid #0dcaf0;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .stats-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
    }
    
    .info-box {
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        border-left: 4px solid #2196f3;
        padding: 0.75rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }
    
    .info-box p {
        margin: 0;
        font-size: 0.85rem;
        color: #1976d2;
    }
    
    .calculation-box {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border-left: 4px solid #28a745;
        padding: 0.75rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }
    
    .calculation-box p {
        margin: 0;
        font-size: 0.85rem;
        color: #155724;
    }
    
    .section-divider {
        border-top: 1px solid #e9ecef;
        margin: 1rem 0;
        padding-top: 1rem;
    }
    
    .section-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }
    
    .seat-map-container {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        background: #f8f9fa;
        min-height: 200px;
    }
    
    .seat-map-grid {
        display: grid;
        gap: 0.5rem;
        justify-content: center;
        margin: 1rem 0;
    }
    
    .seat-row {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        align-items: center;
    }
    
    .seat-item {
        width: 40px;
        height: 40px;
        border: 2px solid #6c757d;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }
    
    .seat-item:hover {
        transform: scale(1.1);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    .seat-item.window { background-color: #e3f2fd; border-color: #2196f3; }
    .seat-item.aisle { background-color: #f3e5f5; border-color: #9c27b0; }
    .seat-item.middle { background-color: #e8f5e8; border-color: #4caf50; }
    .seat-item.executive { background-color: #fff3e0; border-color: #ff9800; }
    .seat-item.sleeper { background-color: #e0f2f1; border-color: #009688; }
    .seat-item.semi_sleeper { background-color: #fce4ec; border-color: #e91e63; }
    .seat-item.disabled { background-color: #ffebee; border-color: #f44336; }
    .seat-item.vip { background-color: #f1f8e9; border-color: #8bc34a; }
    
    .seat-item.female-only {
        border-style: dashed;
        border-width: 3px;
    }
    
    .seat-item.unavailable {
        background-color: #f5f5f5 !important;
        border-color: #9e9e9e !important;
        color: #9e9e9e !important;
        cursor: not-allowed;
    }
    
    .aisle-space {
        width: 20px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6rem;
        color: #6c757d;
        font-weight: 600;
    }
    
    .seat-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 6px;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
    }
    
    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        border: 1px solid #6c757d;
    }
    
    .seat-modal .modal-body {
        padding: 1.5rem;
    }
    
    .seat-info-display {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .seat-info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }
    
    .seat-info-item:last-child {
        margin-bottom: 0;
    }
    
    .seat-info-label {
        font-weight: 600;
        color: #495057;
    }
    
    .seat-info-value {
        color: #6c757d;
    }
</style>
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Bus Configuration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.bus-layouts.index') }}">Bus Layouts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Bus Layout</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-xl-8 mx-auto">
            <div class="card bus-layout-card">
                <div class="card-header-info">
                    <h5><i class="bx bx-edit me-2"></i>Edit Bus Layout: {{ $busLayout->name }}</h5>
                </div>
                
                <form action="{{ route('admin.bus-layouts.update', $busLayout->id) }}" method="POST" class="row g-3">
                    @method('PUT')
                    @csrf
                    
                    <div class="card-body">
                        <!-- Info Box -->
                        <div class="info-box">
                            <p><i class="bx bx-info-circle me-1"></i><strong>Note:</strong> Updating the layout configuration will affect all buses using this layout. Please review carefully before saving changes.</p>
                        </div>
                        
                        <!-- Bus Layout Information Card -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card bus-layout-info-card">
                                    <div class="card-body" style="padding: 0.75rem;">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Layout ID:</strong> 
                                                    <span class="badge bg-secondary">{{ $busLayout->id }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Current Status:</strong> 
                                                    <span class="badge bg-{{ $busLayout->status->getStatusColor($busLayout->status->value) }} stats-badge">
                                                        {{ $busLayout->status->getName() }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Current Seats:</strong> 
                                                    <span class="badge bg-success">{{ $busLayout->total_seats }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="mb-1" style="font-size: 0.85rem;">
                                                    <strong>Created:</strong> 
                                                    {{ $busLayout->created_at->format('M d, Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Basic Information -->
                        <div class="section-title">
                            <i class="bx bx-building me-1"></i>Basic Information
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    Layout Name 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name"
                                       name="name" 
                                       placeholder="Enter Layout Name" 
                                       value="{{ old('name', $busLayout->name) }}" 
                                       required
                                       autofocus>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="status" class="form-label">
                                    Status 
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" 
                                        name="status" 
                                        required>
                                    <option value="">Select Status</option>
                                    @foreach (\App\Enums\BusLayoutEnum::cases() as $status)
                                        <option value="{{ $status->value }}" 
                                            {{ old('status', $busLayout->status->value) == $status->value ? 'selected' : '' }}>
                                            {{ $status->getName() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description"
                                          name="description" 
                                          rows="3" 
                                          placeholder="Enter layout description (optional)">{{ old('description', $busLayout->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Seat Configuration -->
                        <div class="section-divider"></div>
                        <div class="section-title">
                            <i class="bx bx-chair me-1"></i>Seat Configuration
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="total_rows" class="form-label">
                                    Total Rows 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('total_rows') is-invalid @enderror" 
                                       id="total_rows"
                                       name="total_rows" 
                                       placeholder="Enter total rows" 
                                       value="{{ old('total_rows', $busLayout->total_rows) }}" 
                                       min="1" 
                                       max="50" 
                                       required>
                                <div class="form-text">Enter number of rows (1-50)</div>
                                @error('total_rows')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="total_columns" class="form-label">
                                    Total Columns 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('total_columns') is-invalid @enderror" 
                                       id="total_columns"
                                       name="total_columns" 
                                       placeholder="Enter total columns" 
                                       value="{{ old('total_columns', $busLayout->total_columns) }}" 
                                       min="1" 
                                       max="10" 
                                       required>
                                <div class="form-text">Enter number of columns (1-10)</div>
                                @error('total_columns')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Seat Calculation Display -->
                        <div class="row">
                            <div class="col-12">
                                <div class="calculation-box" id="seat-calculation">
                                    <p>
                                        <i class="bx bx-calculator me-1"></i>
                                        <strong>New Total Seats:</strong> <span id="total-seats">{{ $busLayout->total_seats }} seats</span>
                                        <span id="calculation-detail" class="ms-2">({{ $busLayout->total_rows }} rows × {{ $busLayout->total_columns }} columns)</span>
                                    </p>
                                    <p class="mt-1 mb-0" style="font-size: 0.8rem;">
                                        <i class="bx bx-history me-1"></i>
                                        <strong>Previous Total:</strong> <span>{{ $busLayout->total_seats }} seats</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Seat Map Configuration -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="section-title mb-0">
                                        <i class="bx bx-chair me-1"></i>Seat Map Configuration
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="generate-seat-map">
                                            <i class="bx bx-refresh me-1"></i>Regenerate Map
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm" id="load-existing-map">
                                            <i class="bx bx-show me-1"></i>Load Current Map
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info" id="seat-map-info" style="display: none;">
                                    <i class="bx bx-info-circle me-1"></i>
                                    <strong>Seat Map Loaded!</strong> You can now customize individual seat properties by clicking on them.
                                </div>
                                
                                <div id="seat-map-container" class="seat-map-container">
                                    <div class="text-center text-muted py-4">
                                        <i class="bx bx-chair" style="font-size: 3rem;"></i>
                                        <p class="mt-2">Click "Load Current Map" to view the existing seat layout, or "Regenerate Map" to create a new layout.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.bus-layouts.index') }}" class="btn btn-light px-4">
                                    <i class="bx bx-arrow-back me-1"></i>Back to List
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.bus-layouts.index') }}" class="btn btn-secondary px-4">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bx bx-save me-1"></i>Update Bus Layout
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Seat Configuration Modal -->
    <div class="modal fade seat-modal" id="seatConfigModal" tabindex="-1" aria-labelledby="seatConfigModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="seatConfigModalLabel">
                        <i class="bx bx-chair me-2"></i>Configure Seat Properties
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="seat-info-display" id="seat-info-display">
                        <!-- Seat information will be populated here -->
                    </div>
                    
                    <form id="seat-config-form">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="seat-type" class="form-label">Seat Type</label>
                                <select class="form-select" id="seat-type" name="seat_type" required>
                                    <option value="">Select Seat Type</option>
                                    @foreach($seatTypes as $seatType)
                                        <option value="{{ $seatType }}">{{ \App\Enums\SeatTypeEnum::getSeatTypeName($seatType) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="seat-gender" class="form-label">Gender Restriction</label>
                                <select class="form-select" id="seat-gender" name="gender">
                                    <option value="">No Restriction</option>
                                    @foreach($genders as $gender)
                                        <option value="{{ $gender }}">{{ \App\Enums\GenderEnum::getGenderName($gender) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="female-only" name="is_reserved_for_female">
                                    <label class="form-check-label" for="female-only">
                                        Reserved for Female Only
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="seat-available" name="is_available" checked>
                                    <label class="form-check-label" for="seat-available">
                                        Seat Available
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="save-seat-config">
                        <i class="bx bx-save me-1"></i>Save Configuration
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalRowsInput = document.getElementById('total_rows');
    const totalColumnsInput = document.getElementById('total_columns');
    const totalSeatsSpan = document.getElementById('total-seats');
    const calculationDetailSpan = document.getElementById('calculation-detail');
    const generateSeatMapBtn = document.getElementById('generate-seat-map');
    const loadExistingMapBtn = document.getElementById('load-existing-map');
    const seatMapContainer = document.getElementById('seat-map-container');
    const seatMapInfo = document.getElementById('seat-map-info');
    const seatConfigModal = new bootstrap.Modal(document.getElementById('seatConfigModal'));
    
    let currentSeatMap = {};
    let selectedSeat = null;
    const existingSeatMap = @json($busLayout->seat_map ?? []);
    
    // Seat type colors mapping
    const seatTypeColors = {
        'window': '#e3f2fd',
        'aisle': '#f3e5f5',
        'middle': '#e8f5e8',
        'executive': '#fff3e0',
        'sleeper': '#e0f2f1',
        'semi_sleeper': '#fce4ec',
        'disabled': '#ffebee',
        'vip': '#f1f8e9'
    };
    
    function updateSeatCalculation() {
        const rows = parseInt(totalRowsInput.value) || 0;
        const columns = parseInt(totalColumnsInput.value) || 0;
        const totalSeats = rows * columns;
        
        if (totalSeats > 0) {
            totalSeatsSpan.textContent = `${totalSeats} seats`;
            calculationDetailSpan.textContent = `(${rows} rows × ${columns} columns)`;
            calculationDetailSpan.style.display = 'inline';
        } else {
            totalSeatsSpan.textContent = `${currentTotalSeats} seats`;
            calculationDetailSpan.textContent = '';
            calculationDetailSpan.style.display = 'none';
        }
    }
    
    function loadExistingSeatMap() {
        if (Object.keys(existingSeatMap).length === 0) {
            alert('No existing seat map found. Please generate a new seat map.');
            return;
        }
        
        currentSeatMap = JSON.parse(JSON.stringify(existingSeatMap));
        renderSeatMap();
        seatMapInfo.style.display = 'block';
        addSeatMapToForm();
    }
    
    function generateSeatMap() {
        const rows = parseInt(totalRowsInput.value) || 0;
        const columns = parseInt(totalColumnsInput.value) || 0;
        
        if (rows === 0 || columns === 0) {
            alert('Please enter valid number of rows and columns');
            return;
        }
        
        // Generate seat map data
        currentSeatMap = {};
        let seatNumber = 1;
        
        for (let row = 0; row < rows; row++) {
            currentSeatMap[row] = {};
            for (let col = 0; col < columns; col++) {
                // Determine seat type based on position
                let seatType = 'middle';
                if (col === 0 || col === columns - 1) {
                    seatType = 'window';
                } else if (col === 1 || col === columns - 2) {
                    seatType = 'aisle';
                }
                
                currentSeatMap[row][col] = {
                    number: seatNumber,
                    type: seatType,
                    gender: null,
                    is_reserved_for_female: false,
                    is_available: true,
                    row: row + 1,
                    column: col + 1
                };
                seatNumber++;
            }
        }
        
        renderSeatMap();
        seatMapInfo.style.display = 'block';
        addSeatMapToForm();
    }
    
    function renderSeatMap() {
        const rows = Object.keys(currentSeatMap).length;
        const columns = Object.keys(currentSeatMap[0] || {}).length;
        
        let html = '<div class="seat-map-grid">';
        
        for (let row = 0; row < rows; row++) {
            html += '<div class="seat-row">';
            
            for (let col = 0; col < columns; col++) {
                const seat = currentSeatMap[row][col];
                const seatClasses = [
                    'seat-item',
                    seat.type,
                    seat.is_reserved_for_female ? 'female-only' : '',
                    !seat.is_available ? 'unavailable' : ''
                ].filter(Boolean).join(' ');
                
                html += `<div class="${seatClasses}" 
                             data-row="${row}" 
                             data-col="${col}" 
                             data-seat-number="${seat.number}"
                             title="Seat ${seat.number} - ${seat.type}">
                            ${seat.number}
                        </div>`;
            }
            
            html += '</div>';
        }
        
        html += '</div>';
        
        // Add legend
        html += '<div class="seat-legend">';
        html += '<div class="legend-item"><div class="legend-color" style="background-color: #e3f2fd;"></div><span>Window</span></div>';
        html += '<div class="legend-item"><div class="legend-color" style="background-color: #f3e5f5;"></div><span>Aisle</span></div>';
        html += '<div class="legend-item"><div class="legend-color" style="background-color: #e8f5e8;"></div><span>Middle</span></div>';
        html += '<div class="legend-item"><div class="legend-color" style="background-color: #fff3e0;"></div><span>Executive</span></div>';
        html += '<div class="legend-item"><div class="legend-color" style="background-color: #fce4ec;"></div><span>Semi-Sleeper</span></div>';
        html += '<div class="legend-item"><div class="legend-color" style="background-color: #e0f2f1;"></div><span>Sleeper</span></div>';
        html += '<div class="legend-item"><div class="legend-color" style="background-color: #ffebee;"></div><span>Disabled</span></div>';
        html += '<div class="legend-item"><div class="legend-color" style="background-color: #f1f8e9;"></div><span>VIP</span></div>';
        html += '<div class="legend-item"><span style="border: 2px dashed #6c757d; padding: 2px 8px; border-radius: 4px;">Female Only</span></div>';
        html += '</div>';
        
        seatMapContainer.innerHTML = html;
        
        // Add click event listeners to seats
        document.querySelectorAll('.seat-item').forEach(seatElement => {
            seatElement.addEventListener('click', function() {
                const row = parseInt(this.dataset.row);
                const col = parseInt(this.dataset.col);
                const seatNumber = parseInt(this.dataset.seatNumber);
                
                selectedSeat = { row, col, seatNumber };
                showSeatConfigModal(currentSeatMap[row][col]);
            });
        });
    }
    
    function showSeatConfigModal(seat) {
        // Populate seat info display
        const seatInfoHtml = `
            <div class="seat-info-item">
                <span class="seat-info-label">Seat Number:</span>
                <span class="seat-info-value">${seat.number}</span>
            </div>
            <div class="seat-info-item">
                <span class="seat-info-label">Position:</span>
                <span class="seat-info-value">Row ${seat.row}, Column ${seat.column}</span>
            </div>
            <div class="seat-info-item">
                <span class="seat-info-label">Current Type:</span>
                <span class="seat-info-value">${seat.type}</span>
            </div>
        `;
        
        document.getElementById('seat-info-display').innerHTML = seatInfoHtml;
        
        // Populate form with current seat data
        document.getElementById('seat-type').value = seat.type;
        document.getElementById('seat-gender').value = seat.gender || '';
        document.getElementById('female-only').checked = seat.is_reserved_for_female;
        document.getElementById('seat-available').checked = seat.is_available;
        
        seatConfigModal.show();
    }
    
    function addSeatMapToForm() {
        // Create hidden input for seat map data
        let seatMapInput = document.getElementById('seat-map-data');
        if (!seatMapInput) {
            seatMapInput = document.createElement('input');
            seatMapInput.type = 'hidden';
            seatMapInput.name = 'seat_map';
            seatMapInput.id = 'seat-map-data';
            document.querySelector('form').appendChild(seatMapInput);
        }
        
        seatMapInput.value = JSON.stringify(currentSeatMap);
    }
    
    // Event listeners
    totalRowsInput.addEventListener('input', updateSeatCalculation);
    totalColumnsInput.addEventListener('input', updateSeatCalculation);
    generateSeatMapBtn.addEventListener('click', generateSeatMap);
    loadExistingMapBtn.addEventListener('click', loadExistingSeatMap);
    
    document.getElementById('save-seat-config').addEventListener('click', function() {
        if (!selectedSeat) return;
        
        const formData = new FormData(document.getElementById('seat-config-form'));
        const seatType = formData.get('seat_type');
        const gender = formData.get('gender');
        const isReservedForFemale = formData.get('is_reserved_for_female') === 'on';
        const isAvailable = formData.get('is_available') === 'on';
        
        // Update seat data
        currentSeatMap[selectedSeat.row][selectedSeat.col] = {
            ...currentSeatMap[selectedSeat.row][selectedSeat.col],
            type: seatType,
            gender: gender || null,
            is_reserved_for_female: isReservedForFemale,
            is_available: isAvailable
        };
        
        // Re-render seat map
        renderSeatMap();
        
        // Update form data
        addSeatMapToForm();
        
        seatConfigModal.hide();
        selectedSeat = null;
    });
    
    // Initial calculation
    updateSeatCalculation();
});
</script>
@endsection
