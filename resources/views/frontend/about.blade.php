@extends('frontend.layouts.app')

@section('title', 'About Us')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="text-center mb-5">About Bashir Sons</h1>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">Our Story</h3>
                            <p class="card-text">
                                Bashir Sons has been providing reliable transportation services across Pakistan for decades. 
                                We are committed to offering safe, comfortable, and affordable bus travel experiences to our valued customers.
                            </p>
                            <h4 class="mt-4">Our Mission</h4>
                            <p class="card-text">
                                To connect people and places through excellent transportation services while maintaining the highest 
                                standards of safety, comfort, and customer satisfaction.
                            </p>
                            <h4 class="mt-4">Our Values</h4>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle text-success me-2"></i>Safety First</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Customer Satisfaction</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Reliability</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Affordability</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
