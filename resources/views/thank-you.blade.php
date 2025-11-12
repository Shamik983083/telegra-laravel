@extends('layouts.app')

@section('content')
<div class="container text-center py-5">
    <div class="card shadow-sm border-0 mx-auto p-5" style="max-width: 600px;">
        <div class="mb-4">
            <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png"
                 alt="Success"
                 width="100"
                 class="mb-3">
            <h2 class="fw-bold text-success">Thank You!</h2>
        </div>

        <p class="text-muted mb-4">
            Your questionnaire has been successfully submitted.
            Our medical team will review your answers and follow up shortly.
        </p>

        <div class="d-flex justify-content-center">
            <a href="{{ url('/products') }}" class="btn btn-outline-primary me-2 px-4">
                Back to Products
            </a>
            <!-- <a href="{{ url('/') }}" class="btn btn-primary px-4">
                Go to Home
            </a> -->
        </div>
    </div>
</div>

<style>
    body {
        background: #f9fafc;
    }
    .card {
        animation: fadeIn 0.4s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
