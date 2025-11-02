<script>
    {{-- Passenger Management Functions --}}

    // ========================================
    // UPDATE PASSENGER FORMS
    // ========================================
    function updatePassengerForms() {
        const container = document.getElementById('passengerInfoContainer');

        // Ensure at least 1 mandatory passenger exists
        if (!appState.passengerInfo['passenger_1']) {
            appState.passengerInfo['passenger_1'] = {
                type: 'mandatory',
                name: '',
                age: '',
                gender: '',
                cnic: '',
                phone: '',
                email: ''
            };
        }

        // Generate forms
        let html = '';
        const passengers = Object.keys(appState.passengerInfo).sort((a, b) => {
            // Mandatory first, then extras
            if (appState.passengerInfo[a].type === 'mandatory') return -1;
            if (appState.passengerInfo[b].type === 'mandatory') return 1;
            return a.localeCompare(b);
        });

        passengers.forEach((passengerId, index) => {
            const info = appState.passengerInfo[passengerId];
            const isMandatory = info.type === 'mandatory';
            const passengerNumber = index + 1;

            html += `
                <div class="card mb-3 border-2 ${isMandatory ? '' : 'border-warning'}" style="border-color: ${isMandatory ? '#e9ecef' : '#ffc107'};">
                    <div class="card-header" style="background-color: ${isMandatory ? '#f8f9fa' : '#fff3cd'};">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                ${isMandatory ? '<i class="fas fa-user"></i> Passenger 1 <span class="badge bg-danger ms-2">Required</span>' : `<i class="fas fa-user-plus"></i> Passenger ${passengerNumber}`}
                            </h6>
                            ${!isMandatory ? `<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeExtraPassenger('${passengerId}')">
                                                            <i class="fas fa-trash"></i> Remove
                                                        </button>` : ''}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Name *</label>
                                <input type="text" class="form-control form-control-sm" 
                                    value="${info.name || ''}" 
                                    onchange="updatePassengerField('${passengerId}', 'name', this.value)"
                                    placeholder="Full Name" maxlength="100" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Gender *</label>
                                <select class="form-control form-control-sm" onchange="updatePassengerField('${passengerId}', 'gender', this.value)" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" ${info.gender === 'male' ? 'selected' : ''}>👨 Male</option>
                                    <option value="female" ${info.gender === 'female' ? 'selected' : ''}>👩 Female</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Age</label>
                                <input type="number" class="form-control form-control-sm" 
                                    value="${info.age || ''}" 
                                    onchange="updatePassengerField('${passengerId}', 'age', this.value)"
                                    placeholder="Age" min="1" max="120">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">CNIC</label>
                                <input type="text" class="form-control form-control-sm" 
                                    value="${info.cnic || ''}" 
                                    onchange="updatePassengerField('${passengerId}', 'cnic', this.value)"
                                    placeholder="CNIC / ID Number" maxlength="20">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Phone</label>
                                <input type="tel" class="form-control form-control-sm" 
                                    value="${info.phone || ''}" 
                                    onchange="updatePassengerField('${passengerId}', 'phone', this.value)"
                                    placeholder="Phone Number" maxlength="20">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Email</label>
                                <input type="email" class="form-control form-control-sm" 
                                    value="${info.email || ''}" 
                                    onchange="updatePassengerField('${passengerId}', 'email', this.value)"
                                    placeholder="Email Address" maxlength="100">
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        document.getElementById('addPassengerBtn').style.display = 'inline-block';
    }

    // ========================================
    // UPDATE PASSENGER FIELD
    // ========================================
    function updatePassengerField(key, field, value) {
        if (appState.passengerInfo[key]) {
            appState.passengerInfo[key][field] = value;
        }
    }

    // ========================================
    // ADD EXTRA PASSENGER
    // ========================================
    function addExtraPassenger() {
        // Generate unique ID for new passenger
        const timestamp = Date.now();
        const passengerId = `passenger_extra_${timestamp}`;

        appState.passengerInfo[passengerId] = {
            type: 'extra',
            name: '',
            age: '',
            gender: '',
            cnic: '',
            phone: '',
            email: ''
        };

        updatePassengerForms();

        // Scroll to the newly added passenger form
        setTimeout(() => {
            const container = document.getElementById('passengerInfoContainer');
            container.scrollTop = container.scrollHeight;
        }, 100);
    }

    // ========================================
    // REMOVE EXTRA PASSENGER
    // ========================================
    function removeExtraPassenger(passengerId) {
        // Don't allow removing the mandatory passenger
        if (appState.passengerInfo[passengerId]?.type === 'mandatory') {
            Swal.fire({
                icon: 'warning',
                title: 'Cannot Remove',
                text: 'At least one passenger information is required.',
                confirmButtonColor: '#ffc107'
            });
            return;
        }

        delete appState.passengerInfo[passengerId];
        updatePassengerForms();
    }

    // ========================================
    // VALIDATE PASSENGER INFORMATION
    // ========================================
    function validatePassengerInfo() {
        const passengers = Object.keys(appState.passengerInfo);

        if (passengers.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Passenger Information',
                text: 'At least one passenger information is required.',
                confirmButtonColor: '#ffc107'
            });
            return false;
        }

        // Validate all passengers
        for (let passengerId of passengers) {
            const info = appState.passengerInfo[passengerId];

            if (!info.name || info.name.trim() === '') {
                const passengerNum = info.type === 'mandatory' ? '1' : 'extra';
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: `Passenger ${passengerNum}: Please enter passenger name`,
                    confirmButtonColor: '#ffc107'
                });
                return false;
            }

            if (!info.gender || info.gender === '') {
                const passengerNum = info.type === 'mandatory' ? '1' : 'extra';
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: `Passenger ${passengerNum}: Please select gender`,
                    confirmButtonColor: '#ffc107'
                });
                return false;
            }
        }

        return true;
    }
</script>
