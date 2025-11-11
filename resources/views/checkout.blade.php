@extends('layouts.app')

@section('content')
<div class="container py-5">
    @if(session('error'))
    <div class="alert alert-danger text-center">
        {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success text-center">
        {{ session('success') }}
        @if(session('order_id'))
            <p class="mt-2">Telegra Order ID: <strong>{{ session('order_id') }}</strong></p>
        @endif
    </div>
@endif

    <div class="row g-4">
        {{-- 🧾 Order Summary --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-semibold">
                    <i class="bi bi-cart-check me-2"></i> Order Summary
                </div>
                <div class="card-body text-center">
                    <img src="{{ $product['image'] ?? 'https://via.placeholder.com/200x200?text=No+Image' }}"
                         alt="{{ $product['name'] }}"
                         class="img-fluid rounded mb-3" style="max-height: 160px;">
                    <h5 class="fw-bold">{{ $product['name'] }}</h5>
                    <p class="text-muted mb-1">{{ $product['id'] }}</p>
                    <h6 class="text-success fw-semibold mb-3">
                        $<span id="product-price">{{ number_format($product['price'], 2) }}</span>
                    </h6>
                    <hr>

                    {{-- Dynamic Summary --}}
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>$<span id="subtotal">{{ number_format($subtotal, 2) }}</span></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 align-items-center">
                        <span>Shipping</span>
                        <select id="shipping" class="form-select form-select-sm w-auto">
                            <option value="10" selected>Standard - $10.00</option>
                            <option value="25">Expedited - $25.00</option>
                            <option value="0">Free Shipping</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Discount</span>
                        <span class="text-danger">- $<span id="discount">0.00</span></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span class="text-success">$<span id="total">{{ number_format($total, 2) }}</span></span>
                    </div>

                    {{-- Optional coupon field --}}
                    <div class="mt-3">
                        <input type="text" id="coupon" class="form-control form-control-sm" placeholder="Enter coupon code">
                        <button type="button" id="applyCoupon" class="btn btn-outline-primary btn-sm mt-2">
                            Apply Coupon
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🧍 Checkout Form --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-semibold">
                    Checkout Information
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('checkout.process') }}">
                        @csrf

                        <h6 class="fw-bold mb-3 text-secondary">Customer Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                            <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender" required>
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>

                        </div>

                        <h6 class="fw-bold mt-4 mb-3 text-secondary">Shipping Address</h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <input type="text" class="form-control" name="state" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ZIP Code</label>
                                <input type="text" class="form-control" name="zip" required>
                            </div>
                        </div>

                        <h6 class="fw-bold mt-4 mb-3 text-secondary">Payment Information</h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Card Number</label>
                                <input type="text" class="form-control" placeholder="Card Number" required maxlength="19">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expiration (MM/YY)</label>
                                <input type="text" class="form-control" placeholder="MM/YY" required maxlength="5">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CVV</label>
                                <input type="text" class="form-control" placeholder="123" required maxlength="4">
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-success btn-lg px-5 py-2">
                                <i class="bi bi-lock-fill me-2"></i> Place Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Styling --}}
<style>
    body { background-color: #f8f9fa; }
    .card { border-radius: 15px; }
    .btn-success {
        background: linear-gradient(90deg, #28a745, #218838);
        border: none;
        transition: all 0.3s ease;
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    select, input { cursor: pointer; }
</style>

{{-- ✅ Dynamic JS --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    const subtotal = parseFloat(document.getElementById("subtotal").innerText);
    const shippingSelect = document.getElementById("shipping");
    const discountEl = document.getElementById("discount");
    const totalEl = document.getElementById("total");
    const couponInput = document.getElementById("coupon");
    const applyCouponBtn = document.getElementById("applyCoupon");

    function calculateTotal() {
        const shipping = parseFloat(shippingSelect.value);
        const discount = parseFloat(discountEl.innerText);
        const total = subtotal + shipping - discount;
        totalEl.innerText = total.toFixed(2);
    }

    // Update total on shipping change
    shippingSelect.addEventListener("change", calculateTotal);

    // Apply simple coupon logic
    applyCouponBtn.addEventListener("click", () => {
        const code = couponInput.value.trim().toUpperCase();
        let discount = 0;

        if (code === "SAVE10") discount = 10;
        else if (code === "FREESHIP") {
            discount = parseFloat(shippingSelect.value);
            shippingSelect.value = 0;
        } else if (code !== "") {
            alert("Invalid coupon code!");
        }

        discountEl.innerText = discount.toFixed(2);
        calculateTotal();
    });
});
</script>
@endsection
