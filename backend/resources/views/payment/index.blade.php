@extends('layouts.app')

@section('title', 'Payment - Kisora Shop')

@section('content')
<div class="container my-4">
    <a href="{{ route('cart.index') }}" class="btn btn-outline-primary mb-3">← Back</a>
    
    <div class="card">
        <div class="card-body">
            <h3 class="mb-4">Product Price Details</h3>
            
            @if($isPartialSelection)
            <div class="alert alert-info">
                You are currently checking out {{ count($selectedCartIds) }} selected cart item(s).
            </div>
            @endif
            
            <div class="row g-4">
                <div class="col-md-7">
                    @foreach($carts as $cart)
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <img src="{{ asset($cart->product->image) }}" class="img-fluid rounded" alt="{{ $cart->product->name }}" onerror="this.src='https://via.placeholder.com/200x200?text=No+Image'">
                                </div>
                                <div class="col-md-9">
                                    @if($cart->product->stock > 0)
                                        <span class="product-badge badge-ready">Ready Stock</span>
                                    @else
                                        <span class="product-badge badge-preorder">Pre-Order</span>
                                    @endif
                                    <h5 class="mt-2">{{ $cart->product->name }} by {{ $cart->product->artist->name ?? 'Unknown' }}</h5>
                                    <p class="text-muted mb-1">Character: {{ $cart->product->name }}</p>
                                    <p class="text-muted mb-1">Series: {{ $cart->product->category->name ?? 'Unknown' }}</p>
                                    <p class="text-muted mb-1">Illustrator: {{ $cart->product->artist->name ?? 'Unknown' }}</p>
                                    <form action="{{ route('cart.update', $cart->id) }}" method="POST" class="d-flex align-items-center gap-2 mt-3" style="max-width: 220px;">
                                        @csrf
                                        @method('PUT')
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary quantity-btn" type="button" data-action="decrease" data-target="payQty{{ $cart->id }}">-</button>
                                            <input type="number" class="form-control text-center payment-qty" id="payQty{{ $cart->id }}" name="quantity" value="{{ $cart->quantity }}" min="1">
                                            <button class="btn btn-outline-secondary quantity-btn" type="button" data-action="increase" data-target="payQty{{ $cart->id }}">+</button>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
                                    </form>
                                    <p class="product-price mt-2">IDR {{ number_format($cart->product->price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="col-md-5">
                    <form action="{{ route('payment.checkout') }}" method="POST" id="paymentCheckoutForm" class="card h-100">
                        @csrf
                        <input type="hidden" name="payment_method" value="transfer">
                        @foreach($selectedCartIds as $selectedId)
                            <input type="hidden" name="selected[]" value="{{ $selectedId }}">
                        @endforeach
                        <div class="card-body">
                            <h5>Shipping Method</h5>
                            <p class="text-muted small">Choose a courier speed that fits your schedule.</p>
                            @foreach($shippingOptions as $optionKey => $option)
                            <div class="form-check mb-2">
                                <input class="form-check-input shipping-option" type="radio" name="shipping_method" id="ship{{ $optionKey }}" value="{{ $optionKey }}" data-price="{{ $option['price'] }}" data-label="{{ $option['label'] }}" data-eta="{{ $option['eta'] }}" {{ $optionKey === $currentShipping ? 'checked' : '' }}>
                                <label class="form-check-label w-100" for="ship{{ $optionKey }}">
                                    <div class="d-flex justify-content-between">
                                        <span>{{ $option['label'] }} <small class="text-muted d-block">{{ $option['eta'] }}</small></span>
                                        <strong>IDR {{ number_format($option['price'], 0, ',', '.') }}</strong>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                            
                            <hr>
                            <h5 class="mb-3">Order Summary</h5>
                            <div class="d-flex justify-content-between">
                                <span>Subtotal ({{ $carts->sum('quantity') }} items)</span>
                                <strong id="summarySubtotal" data-value="{{ $subtotal }}">IDR {{ number_format($subtotal, 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Admin Fee</span>
                                <strong id="summaryAdminFee" data-value="{{ $adminFee }}">IDR {{ number_format($adminFee, 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Tax ({{ $taxRate * 100 }}%)</span>
                                <strong id="summaryTax" data-value="{{ $ppn }}">IDR {{ number_format($ppn, 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Shipping (<span id="summaryShippingLabel">{{ $shippingOptions[$currentShipping]['label'] }}</span>)</span>
                                <strong id="summaryShipping" data-value="{{ $shippingPrice }}">IDR {{ number_format($shippingPrice, 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <span>Total</span>
                                <strong id="summaryTotal">IDR {{ number_format($total, 0, ',', '.') }}</strong>
                            </div>
                            <small class="text-muted d-block mt-2">Tax is calculated from the product subtotal before shipping.</small>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <button type="submit" class="btn btn-danger btn-lg w-100">Check Out</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.target;
            const targetInput = document.getElementById(targetId);
            if (!targetInput) return;
            const delta = button.dataset.action === 'increase' ? 1 : -1;
            const nextValue = Math.max(1, Number(targetInput.value || 1) + delta);
            targetInput.value = nextValue;
        });
    });

    const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
    const summaryShippingEl = document.getElementById('summaryShipping');
    const summaryTotalEl = document.getElementById('summaryTotal');
    const summaryTaxEl = document.getElementById('summaryTax');
    const summarySubtotalEl = document.getElementById('summarySubtotal');
    const summaryAdminFeeEl = document.getElementById('summaryAdminFee');
    const summaryShippingLabelEl = document.getElementById('summaryShippingLabel');

    if (shippingRadios.length && summaryShippingEl && summaryTotalEl && summarySubtotalEl && summaryAdminFeeEl && summaryTaxEl) {
        const subtotalAmount = Number(summarySubtotalEl.dataset.value);
        const adminFeeAmount = Number(summaryAdminFeeEl.dataset.value);
        const taxRate = Number(summaryTaxEl.dataset.value) / subtotalAmount || 0.1;

        const formatCurrency = (value) => {
            return 'IDR ' + Number(value).toLocaleString('id-ID');
        };

        const updateTotals = () => {
            const selected = document.querySelector('input[name="shipping_method"]:checked');
            const shippingPrice = selected ? Number(selected.dataset.price) || 0 : 0;
            const taxAmount = subtotalAmount * taxRate;
            const total = subtotalAmount + adminFeeAmount + taxAmount + shippingPrice;

            summaryShippingEl.textContent = formatCurrency(shippingPrice);
            summaryTaxEl.textContent = formatCurrency(taxAmount);
            summaryTotalEl.textContent = formatCurrency(total);
            if (summaryShippingLabelEl && selected) {
                summaryShippingLabelEl.textContent = selected.dataset.label;
            }
        };

        shippingRadios.forEach(radio => {
            radio.addEventListener('change', updateTotals);
        });

        updateTotals();
    }
});
</script>
@endpush


