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
                <form action="">
                    <div class="row mt-5">
                        <div class="col-lg-6">
                            <input type="text" class="form-control" placeholder="Full Name:">
                        </div>
                        <div class="col-lg-6">
                            <input type="email" class="form-control" placeholder="Email:">
                        </div>
                        <div class="col-lg-6">
                            <input type="text" class="form-control" placeholder="Phone:">
                        </div>
                        <div class="col-lg-6">
                            <select name="" id="" class="form-select">
                                <option value="" selected disabled>Choose services</option>
                                <option value="">Option 2</option>
                                <option value="">Option 3</option>
                                <option value="">Option 4</option>
                            </select>
                        </div>
                        <div class="col-lg-12">
                            <textarea name="" placeholder="Write messag" class="form-control" rows="4" id=""></textarea>
                        </div>
                        <div class="col-lg-12 text-center">
                            <button class="sub-button">Submit Now <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="map-fixed">

        </div>
    </section>

@endsection
