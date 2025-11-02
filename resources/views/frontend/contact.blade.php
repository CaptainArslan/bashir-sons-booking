@extends('frontend.layouts.app')

@section('title', 'Contact Us')

@section('content')

    <section class="contactus">
        <div class="container">
            <div class="row">
                <div class="box-contactus">
                    <div class="col-lg-4 col-md-4 col-sm-4 col-12">
                        <div class="d-flex">
                            <div class="icon">
                                <span class="circle">
                                    <img src="{{ asset('frontend/assets/img/Icon (1).svg') }}" alt="">
                                </span>
                            </div>
                            <div class="text">
                                <small>Phone:</small>
                                <h4>041 111 737 737</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-12">
                        <div class="d-flex">
                            <div class="icon">
                                <span class="circle">
                                    <img src="{{ asset('frontend/assets/img/Icon (1).svg') }}" alt="">
                                </span>
                            </div>
                            <div class="text">
                                <small>E-mail:</small>
                                <h4>info@bashirsons.com</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-12">
                        <div class="d-flex">
                            <div class="icon">
                                <span class="circle">
                                    <img src="{{ asset('frontend/assets/img/Icon (1).svg') }}" alt="">
                                </span>
                            </div>
                            <div class="text">
                                <small>Address:</small>
                                <h4>Nadir Bus Terminal,
                                    Jinnah Colony,
                                    Faisalabad</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section id="form-box-contactus">
        <div class="container">
            <div class="text-center">
                <span class="left-border-button">Contact us</span>
            </div>
            <div class="contact-bg">
                <h1 class="text-center">Get in Touch</h1>
                <form action="{{ route('enquiry.submit') }}" method="POST">
                    @csrf

                    {{-- Include the alert partial for errors & success --}}
                    @include('frontend.partials.alerts')

                    <div class="row mt-5">
                        <div class="col-lg-6 mb-3">
                            <input type="text" name="name" value="{{ old('name') }}"
                                class="form-control @error('name') is-invalid @enderror" placeholder="Full Name:">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-6 mb-3">
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror" placeholder="Email:">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-6 mb-3">
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                class="form-control @error('phone') is-invalid @enderror" placeholder="0317-7777777" maxlength="12">
                            <small class="text-muted">Format: XXXX-XXXXXXX (e.g., 0317-7777777)</small>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-6 mb-3">
                            <select name="service" class="form-select @error('service') is-invalid @enderror">
                                <option value="" selected disabled>Choose service</option>
                                <option value="Transport" {{ old('service') == 'Transport' ? 'selected' : '' }}>Transport
                                </option>
                                <option value="Logistics" {{ old('service') == 'Logistics' ? 'selected' : '' }}>Logistics
                                </option>
                                <option value="Freight" {{ old('service') == 'Freight' ? 'selected' : '' }}>Freight
                                </option>
                            </select>
                            @error('service')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-12 mb-3">
                            <textarea name="message" class="form-control @error('message') is-invalid @enderror" placeholder="Write message"
                                rows="4">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-12 text-center">
                            <button type="submit" class="sub-button">Submit Now <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <div class="map-fixed">

        </div>
    </section>

@endsection
