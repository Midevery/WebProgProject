@extends('layouts.app')

@section('title', 'Your Cart - Kisora Shop')

@section('content')
<div class="container my-4">
    <a href="{{ route('home') }}" class="btn btn-outline-primary mb-3">← Back</a>
    
    <h2 class="mb-4">Your Cart</h2>
    
    @if($carts->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-cart-x" style="font-size: 4rem; color: #ccc;"></i>
            <p class="text-muted mt-3">Your cart is empty</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary">Start Shopping</a>
        </div>
    </div>
    @else
    @php
        $initialSubtotal = $carts->sum(function ($c) {
            return $c->product->price * $c->quantity;
        });
        $initialItemCount = $carts->sum('quantity');
    @endphp
    <div class="row">
        <div class="col-lg-8">
            <p class="text-muted mb-3">Select the items you want to purchase now. You can adjust the quantity directly from your cart.</p>
            @foreach($carts as $cart)
            <div class="card mb-3" data-cart-id="{{ $cart->id }}">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-1 col-2 text-center">
                            <div class="form-check">
                                <input
                                    class="form-check-input cart-select"
                                    type="checkbox"
                                    value="{{ $cart->id }}"
                                    id="select{{ $cart->id }}"
                                    data-cart-id="{{ $cart->id }}"
                                    data-price="{{ $cart->product->price }}"
                                    data-quantity="{{ $cart->quantity }}"
                                    checked
                                >
                            </div>
                        </div>
                        <div class="col-md-2 col-4">
                            <img src="{{ asset($cart->product->image) }}" class="img-fluid rounded" alt="{{ $cart->product->name }}" onerror="this.src='https://via.placeholder.com/150x150?text=No+Image'">
                        </div>
                        <div class="col-md-5">
                            @if($cart->product->stock > 0)
                                <span class="product-badge badge-ready">Ready Stock</span>
                            @else
                                <span class="product-badge badge-preorder">Pre-Order</span>
                            @endif
                            <h5 class="mt-2">{{ $cart->product->name }}</h5>
                            <p class="text-muted mb-1">Poster (60 x 40 cm)</p>
                            <p class="text-muted mb-1">by {{ $cart->product->artist->name ?? 'Unknown' }}</p>
                            <p class="product-price mb-0">IDR {{ number_format($cart->product->price, 0, ',', '.') }}</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <form action="{{ route('cart.update', $cart->id) }}" method="POST" class="cart-quantity-form">
                                @csrf
                                @method('PUT')
                                <div class="input-group input-group-sm mb-2" style="max-width: 200px; margin-left: auto;">
                                    <button class="btn btn-outline-secondary quantity-btn" type="button" data-action="decrease" data-target="qty{{ $cart->id }}">-</button>
                                    <input
                                        type="number"
                                        class="form-control text-center cart-qty-input"
                                        name="quantity"
                                        id="qty{{ $cart->id }}"
                                        value="{{ $cart->quantity }}"
                                        min="1"
                                        data-cart-id="{{ $cart->id }}"
                                    >
                                    <button class="btn btn-outline-secondary quantity-btn" type="button" data-action="increase" data-target="qty{{ $cart->id }}">+</button>
                                </div>
                                <button type="submit" class="btn btn-sm btn-outline-primary">Update Qty</button>
                            </form>
                            <div class="d-flex flex-wrap gap-2 justify-content-end mt-2">
                                <a href="{{ route('products.show', $cart->product->id) }}" class="btn btn-sm btn-primary">Go to Detail</a>
                                <form action="{{ route('cart.destroy', $cart->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 90px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title mb-0">Order Summary</h5>
                        <button type="button" class="btn btn-link btn-sm p-0" id="toggleAllSelection" data-checked="true">Deselect All</button>
                    </div>
                    <p class="text-muted small">Only selected items will proceed to payment.</p>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Selected Items:</span>
                        <strong id="selectedItems">{{ $initialItemCount }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Subtotal:</span>
                        <strong id="selectedSubtotal">IDR {{ number_format($initialSubtotal, 0, ',', '.') }}</strong>
                    </div>
                    <form method="GET" action="{{ route('payment.index') }}" id="checkoutForm">
                        <div id="selectedInputs"></div>
                        <button type="submit" class="btn btn-primary w-100" id="checkoutButton">Purchase Selected</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = Array.from(document.querySelectorAll('.cart-select'));
    if (!checkboxes.length) {
        return;
    }

    const subtotalEl = document.getElementById('selectedSubtotal');
    const itemsEl = document.getElementById('selectedItems');
    const selectedInputs = document.getElementById('selectedInputs');
    const checkoutButton = document.getElementById('checkoutButton');
    const toggleAllBtn = document.getElementById('toggleAllSelection');
    const checkoutForm = document.getElementById('checkoutForm');

    const formatCurrency = (value) => {
        return 'IDR ' + Number(value).toLocaleString('id-ID');
    };

    function updateHiddenInputs(selectedIds) {
        selectedInputs.innerHTML = '';
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected[]';
            input.value = id;
            selectedInputs.appendChild(input);
        });
    }

    function computeSelection() {
        let subtotal = 0;
        let totalItems = 0;
        const selectedIds = [];

        checkboxes.forEach(cb => {
            if (cb.checked) {
                const cartId = cb.dataset.cartId;
                const price = Number(cb.dataset.price) || 0;
                const qtyInput = document.querySelector(`.cart-qty-input[data-cart-id="${cartId}"]`);
                const quantity = qtyInput ? Number(qtyInput.value) : Number(cb.dataset.quantity) || 0;
                subtotal += price * quantity;
                totalItems += quantity;
                selectedIds.push(cb.value);
            }
        });

        subtotalEl.textContent = formatCurrency(subtotal);
        itemsEl.textContent = totalItems;
        updateHiddenInputs(selectedIds);
        checkoutButton.disabled = selectedIds.length === 0;

        if (toggleAllBtn) {
            const allChecked = selectedIds.length === checkboxes.length;
            toggleAllBtn.dataset.checked = allChecked ? 'true' : 'false';
            toggleAllBtn.textContent = allChecked ? 'Deselect All' : 'Select All';
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', computeSelection);
    });

    document.querySelectorAll('.cart-qty-input').forEach(input => {
        input.addEventListener('input', () => {
            const checkbox = document.querySelector(`.cart-select[data-cart-id="${input.dataset.cartId}"]`);
            if (checkbox) {
                checkbox.dataset.quantity = input.value;
            }
            computeSelection();
        });
    });

    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.target;
            const targetInput = document.getElementById(targetId);
            if (!targetInput) return;
            const step = button.dataset.action === 'increase' ? 1 : -1;
            const newValue = Math.max(1, Number(targetInput.value) + step);
            targetInput.value = newValue;
            targetInput.dispatchEvent(new Event('input'));
        });
    });

    if (toggleAllBtn) {
        toggleAllBtn.addEventListener('click', () => {
            const shouldSelectAll = toggleAllBtn.dataset.checked !== 'true';
            checkboxes.forEach(cb => cb.checked = shouldSelectAll);
            computeSelection();
        });
    }

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', (event) => {
            if (!selectedInputs.children.length) {
                event.preventDefault();
                alert('Please select at least one item to continue to payment.');
            }
        });
    }

    computeSelection();
});
</script>
@endpush


