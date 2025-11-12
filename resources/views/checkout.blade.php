@extends('layouts.app')

@section('content')
{{-- ✅ Show validation + custom errors --}}
@if ($errors->any())
    <div class="alert alert-danger text-start" style="white-space: pre-wrap;">
        <strong>⚠️ Please fix the following errors:</strong>
        <ul class="mt-2 mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger text-start" style="white-space: pre-wrap;">
        <strong>❌ Error:</strong> {{ session('error') }}
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
                    <img src="{{ $product['image'] ?? asset('images/no_image.jpg') }}"
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
                        <select id="shipping" name="shipping_dropdown" class="form-select form-select-sm w-auto">
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
            <input type="hidden" id="hidden-shipping" name="shipping" value="10">

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
        <select class="form-select" name="state" required>
            <option value="">Select State</option>
            <option value="AL">Alabama</option>
            <option value="AK">Alaska</option>
            <option value="AZ">Arizona</option>
            <option value="AR">Arkansas</option>
            <option value="CA">California</option>
            <option value="CO">Colorado</option>
            <option value="CT">Connecticut</option>
            <option value="DE">Delaware</option>
            <option value="FL">Florida</option>
            <option value="GA">Georgia</option>
            <option value="HI">Hawaii</option>
            <option value="ID">Idaho</option>
            <option value="IL">Illinois</option>
            <option value="IN">Indiana</option>
            <option value="IA">Iowa</option>
            <option value="KS">Kansas</option>
            <option value="KY">Kentucky</option>
            <option value="LA">Louisiana</option>
            <option value="ME">Maine</option>
            <option value="MD">Maryland</option>
            <option value="MA">Massachusetts</option>
            <option value="MI">Michigan</option>
            <option value="MN">Minnesota</option>
            <option value="MS">Mississippi</option>
            <option value="MO">Missouri</option>
            <option value="MT">Montana</option>
            <option value="NE">Nebraska</option>
            <option value="NV">Nevada</option>
            <option value="NH">New Hampshire</option>
            <option value="NJ">New Jersey</option>
            <option value="NM">New Mexico</option>
            <option value="NY">New York</option>
            <option value="NC">North Carolina</option>
            <option value="ND">North Dakota</option>
            <option value="OH">Ohio</option>
            <option value="OK">Oklahoma</option>
            <option value="OR">Oregon</option>
            <option value="PA">Pennsylvania</option>
            <option value="RI">Rhode Island</option>
            <option value="SC">South Carolina</option>
            <option value="SD">South Dakota</option>
            <option value="TN">Tennessee</option>
            <option value="TX">Texas</option>
            <option value="UT">Utah</option>
            <option value="VT">Vermont</option>
            <option value="VA">Virginia</option>
            <option value="WA">Washington</option>
            <option value="WV">West Virginia</option>
            <option value="WI">Wisconsin</option>
            <option value="WY">Wyoming</option>
        </select>
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
        <input type="text" class="form-control" placeholder="Card Number" name="cardnumber" required maxlength="19">
    </div>

    <div class="col-md-4">
        <label class="form-label">Expiration Month</label>
        <select class="form-select" name="expmonth" required>
            <option value="">MM</option>
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
            @endfor
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Expiration Year</label>
        <select class="form-select" name="expyear" required>
            <option value="">YYYY</option>
            @for($y = date('Y'); $y <= date('Y') + 10; $y++)
                <option value="{{ $y }}">{{ $y }}</option>
            @endfor
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">CVV</label>
        <input type="text" class="form-control" placeholder="123" name="cvv" required maxlength="3">
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

<!-- 🔄 Loader Overlay -->
<div id="loader-overlay" style="
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(255, 255, 255, 0.85);
    z-index: 9999;
    text-align: center;
    align-items: center;
    justify-content: center;
    flex-direction: column;
">
    <div class="spinner-border text-success" style="width: 4rem; height: 4rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-3 fw-semibold text-dark">Processing your order, please wait...</p>
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
    #loader-overlay {
    transition: opacity 0.3s ease;
}
#loader-overlay[style*="display: flex"] {
    opacity: 1;
}

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
    shippingSelect.addEventListener("change", () => {
    document.getElementById("hidden-shipping").value = shippingSelect.value;
    calculateTotal();
});

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
// ✅ Show loader when form is submitted
document.querySelector('form').addEventListener('submit', function (e) {
    // Optional: disable button to prevent multiple clicks
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Processing...';

    // Show overlay
    document.getElementById('loader-overlay').style.display = 'flex';
});

</script>
@endsection
