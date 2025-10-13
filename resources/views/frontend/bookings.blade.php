@extends('frontend.layouts.app')

@section('title', 'Book Your Ticket')

@section('styles')
    <style>
        .seat-btn {
            width: 3.2rem;
            height: 3.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: .25rem;
            font-size: .85rem;
            padding: 0;
            line-height: 1;
            border: 1px solid transparent;
        }

        .available {
            background-color: #e9ecef;
        }

        .selected {
            background-color: #0d6efd;
            color: #fff;
        }

        .booked-male {
            background-color: #0dcaf0;
            color: #fff;
        }

        .booked-female {
            background-color: #e83e8c;
            color: #fff;
        }

        .aisle {
            width: 1.6rem;
        }

        /* gap for aisle */
        .legend-box {
            width: 1.2rem;
            height: 1.2rem;
            display: inline-block;
            border-radius: .25rem;
            border: 1px solid transparent;
        }
    </style>
@endsection

@section('content')

    <section class="booking-1">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-12 my-3">
                    <div class="card widget-card h-100 text-center border-0 p-4" style="height: 140px !important;">
                        <div class="d-flex">
                            <div class="mb-3">
                                <i class="bi bi-bus-front"></i>
                            </div>
                            <div class="info">
                                <h4 class="fw-bold mb-1 text-primary">Executive Class</h4>
                                <p class="location mb-0"><strong>FSD-LHR</strong> <span
                                        class="d-inline ms-3 text-muted">01:00 PM</span></p>
                                <p class="text-muted mb-0">
                                    Refereshments | Movies & Entertainment | Max 30Kg per seat
                                </p>
                            </div>

                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-lg-8 text-center">
                            <h5 class="mb-3">Select Your Seat</h5>
                            <!-- Rows 1–10 -->
                            <!-- each row: [col1][col2][aisle][col4][col5] -->
                            <!-- status classes: available, selected, booked-male, booked-female -->
                            <div class="mb-2 d-flex justify-content-center align-items-center">
                                <div class="me-2"></div>
                                <!-- col1 empty on row1 -->
                                <button class="seat-btn available me-2">4</button>
                                <div class="aisle"></div>
                                <button class="seat-btn available me-2">2</button>
                                <button class="seat-btn available">1</button>
                            </div>

                            <div class="mb-2 d-flex justify-content-center">
                                <button class="seat-btn available me-2">8</button>
                                <button class="seat-btn available me-2">7</button>
                                <div class="aisle"></div>
                                <button class="seat-btn available me-2">6</button>
                                <button class="seat-btn available">5</button>
                            </div>

                            <div class="mb-2 d-flex justify-content-center">
                                <button class="seat-btn available me-2">12</button>
                                <button class="seat-btn available me-2">11</button>
                                <div class="aisle"></div>
                                <button class="seat-btn available me-2">10</button>
                                <button class="seat-btn available">9</button>
                            </div>

                            <div class="mb-2 d-flex justify-content-center">
                                <button class="seat-btn available me-2">16</button>
                                <button class="seat-btn available me-2">15</button>
                                <div class="aisle"></div>
                                <button class="seat-btn available me-2">14</button>
                                <button class="seat-btn available">13</button>
                            </div>

                            <div class="mb-2 d-flex justify-content-center">
                                <button class="seat-btn available me-2">20</button>
                                <button class="seat-btn available me-2">19</button>
                                <div class="aisle"></div>
                                <button class="seat-btn available me-2">18</button>
                                <button class="seat-btn available">17</button>
                            </div>

                            <div class="mb-2 d-flex justify-content-center">
                                <button class="seat-btn available me-2">24</button>
                                <button class="seat-btn available me-2">23</button>
                                <div class="aisle"></div>
                                <button class="seat-btn available me-2">22</button>
                                <button class="seat-btn available">21</button>
                            </div>

                            <div class="mb-2 d-flex justify-content-center">
                                <button class="seat-btn available me-2">28</button>
                                <button class="seat-btn available me-2">27</button>
                                <div class="aisle"></div>
                                <button class="seat-btn available me-2">26</button>
                                <button class="seat-btn available">25</button>
                            </div>

                            <div class="mb-2 d-flex justify-content-center">
                                <button class="seat-btn available me-2">32</button>
                                <button class="seat-btn available me-2">31</button>
                                <div class="aisle"></div>
                                <button class="seat-btn available me-2">30</button>
                                <button class="seat-btn available">29</button>
                            </div>

                            <div class="mb-2 d-flex justify-content-center">
                                <button class="seat-btn available me-2">36</button>
                                <button class="seat-btn available me-2">35</button>
                                <div class="aisle"></div>
                                <button class="seat-btn available me-2">34</button>
                                <button class="seat-btn available">33</button>
                            </div>

                            <div class="mb-2 d-flex justify-content-center">
                                <button class="seat-btn available me-2">40</button>
                                <button class="seat-btn available me-2">39</button>
                                <div class="aisle"></div>
                                <button class="seat-btn available me-2">38</button>
                                <button class="seat-btn available">37</button>
                            </div>

                            <!-- Back row (11) with center seat -->
                            <div class="d-flex justify-content-center">
                                <button class="seat-btn available me-2">45</button>
                                <button class="seat-btn available me-2">44</button>
                                <button class="seat-btn available me-2">43</button>
                                <button class="seat-btn available me-2">42</button>
                                <button class="seat-btn available">41</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-12">
                    <div class="total-bus">
                        <h5>Bus</h5>
                        <p>
                            <span class="locatoin-to">FSD - LHE </span>
                            <span class="status">Onways</span>
                        </p>
                        <hr>
                        <h5>Subtotal</h5>
                        <p>
                            <span class="locatoin-to">Outbound </span>
                            <span class="status">Rs 0</span>
                        </p>
                        <hr>

                        <div class="d-flex">
                            <h5>Total</h5>
                            <span class="status">Rs 0</span>
                        </div>

                    </div>
                    <!-- Legend -->
                    <div class="mt-4 d-flex justify-content-center align-items-center flex-wrap">
                        <div class="d-flex align-items-center me-4">
                            <span class="legend-box available me-1"></span> Available
                        </div>
                        <div class="d-flex align-items-center me-4">
                            <span class="legend-box selected me-1"></span> Selected
                        </div>
                        <div class="d-flex align-items-center me-4">
                            <span class="legend-box booked-male me-1"></span> Male Booked
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="legend-box booked-female me-1"></span> Female Booked
                        </div>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-lg-8">
                    <div class="container my-4">

                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="genderModal" tabindex="-1" aria-labelledby="genderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="genderModalLabel">Select Gender</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="genderForm">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="genderMale"
                                value="male" checked>
                            <label class="form-check-label" for="genderMale">Male</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="genderFemale"
                                value="female">
                            <label class="form-check-label" for="genderFemale">Female</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirmGenderBtn" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

@endsection
