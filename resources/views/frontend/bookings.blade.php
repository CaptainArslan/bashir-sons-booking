@extends('frontend.layouts.app')

@section('title', 'Book Your Ticket')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center mb-0">Book Your Ticket</h3>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="from" class="form-label">From</label>
                                <select class="form-select" id="from" required>
                                    <option value="">Select City</option>
                                    <option value="faisalabad">Faisalabad</option>
                                    <option value="lahore">Lahore</option>
                                    <option value="karachi">Karachi</option>
                                    <option value="islamabad">Islamabad</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="to" class="form-label">To</label>
                                <select class="form-select" id="to" required>
                                    <option value="">Select City</option>
                                    <option value="faisalabad">Faisalabad</option>
                                    <option value="lahore">Lahore</option>
                                    <option value="karachi">Karachi</option>
                                    <option value="islamabad">Islamabad</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="departure" class="form-label">Departure Date</label>
                                <input type="date" class="form-control" id="departure" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="passengers" class="form-label">Number of Passengers</label>
                                <select class="form-select" id="passengers" required>
                                    <option value="1">1 Passenger</option>
                                    <option value="2">2 Passengers</option>
                                    <option value="3">3 Passengers</option>
                                    <option value="4">4 Passengers</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">Search Buses</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
