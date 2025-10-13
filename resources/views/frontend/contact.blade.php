@extends('frontend.layouts.app')

@section('title', 'Contact Us')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="text-center mb-5">Contact Us</h1>
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="card-title">Get in Touch</h3>
                            <div class="contact-info">
                                <div class="mb-3">
                                    <i class="bi bi-telephone-fill text-primary me-2"></i>
                                    <strong>UAN:</strong> 041-111-737-737
                                </div>
                                <div class="mb-3">
                                    <i class="bi bi-envelope-fill text-primary me-2"></i>
                                    <strong>Email:</strong> info@bashirsonsgroup.com
                                </div>
                                <div class="mb-3">
                                    <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                                    <strong>Head Office:</strong><br>
                                    Bashir Sons Office<br>
                                    P-68, Pakimari,<br>
                                    Behind General Bus Stand,<br>
                                    Faisalabad - Pakistan.
                                </div>
                                <div class="mb-3">
                                    <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                                    <strong>Sub Office:</strong><br>
                                    Bashir Sons Office<br>
                                    Nadir Bus Terminal,<br>
                                    Jinnah Colony,<br>
                                    Faisalabad - Pakistan.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="card-title">Send us a Message</h3>
                            <form>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone">
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
