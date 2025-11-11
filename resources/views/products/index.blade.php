@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="text-center mb-4 fw-bold text-primary">Select Your Product</h2>

<p class="text-center text-muted mb-3">Browse our available treatments and select one to continue.</p>


    {{-- Search --}}
    <form method="GET" action="{{ url('/products') }}" class="d-flex justify-content-center mb-4">
        <input type="text" name="search" class="form-control w-50 me-2" placeholder="Search products..." value="{{ $search }}">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    {{-- Swiper Carousel --}}
    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            @foreach($products as $product)
                <div class="swiper-slide">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="{{ $product['product']['image'] ?: 'https://via.placeholder.com/300x300?text=No+Image' }}"
                             alt="{{ $product['product']['title'] }}"
                             class="card-img-top"
                             style="height: 220px; object-fit: cover; border-radius: 10px 10px 0 0;">

                        <div class="card-body text-center">
                            <h5 class="fw-bold text-dark">{{ $product['product']['title'] }}</h5>
                            <p class="text-muted small mb-1">{{ $product['description'] }}</p>
                            <p class="fw-semibold text-success mb-3">${{ number_format($product['pricePerUnit'], 2) }}</p>

                            <form method="POST" action="{{ url('/select-product') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                                <input type="hidden" name="name" value="{{ $product['product']['title'] }}">
                                <input type="hidden" name="price" value="{{ $product['pricePerUnit'] }}">
                                <input type="hidden" name="image" value="{{ $product['product']['image'] ?? '' }}"> {{-- ✅ Add this --}}
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-cart-check me-1"></i> Select Product
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Arrows --}}
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>

        {{-- Pagination Dots --}}
        <div class="swiper-pagination"></div>
    </div>
</div>

{{-- Swiper.js CDN --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

{{-- Swiper Styles --}}
<style>
    .swiper {
        width: 100%;
        padding-bottom: 40px;
    }
    .swiper-slide {
        width: 300px;
    }
    .card {
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .swiper-button-next, .swiper-button-prev {
        color: #007bff;
        transition: 0.3s;
    }
    .swiper-button-next:hover, .swiper-button-prev:hover {
        color: #0056b3;
        transform: scale(1.1);
    }
</style>

{{-- Swiper Config --}}
<script>
    const swiper = new Swiper(".mySwiper", {
        slidesPerView: 3,
        spaceBetween: 30,
        loop: true,
        grabCursor: true,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        breakpoints: {
            0:   { slidesPerView: 1 },
            768: { slidesPerView: 2 },
            992: { slidesPerView: 3 }
        }
    });
</script>
@endsection
