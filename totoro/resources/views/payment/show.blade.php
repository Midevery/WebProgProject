@extends('layouts.app')

@section('title', 'Payment - Kisora Shop')

@section('content')
@php
    $paymentBackUrl = url()->previous();
    if (!$paymentBackUrl || $paymentBackUrl === url()->current()) {
        $paymentBackUrl = route('payment.index');
    }
@endphp
<div class="container my-4">
    <a href="{{ $paymentBackUrl }}" class="btn btn-outline-primary mb-3">← Back</a>
    
    <div class="card">
        <div class="card-body">
            <h3 class="mb-4">Product Price Details</h3>
            
            <div class="mb-4">
                <h5>Product Price Details</h5>
                <p><strong>Total Item:</strong> {{ $order->orderItems->sum('quantity') }}</p>
                @foreach($order->orderItems as $item)
                <p>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }} = IDR {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                @endforeach
            </div>
            
            <div class="mb-4">
                <h5>Shipping Option</h5>
                <p class="mb-1"><strong>Method:</strong> {{ $shippingDetails['label'] }}</p>
                <p class="mb-1"><strong>Shipping Price:</strong> IDR {{ number_format($shippingPrice, 0, ',', '.') }}</p>
                <p class="mb-2"><strong>Estimate:</strong> {{ $shippingDetails['eta'] }}</p>
            </div>
            
            <div class="mb-4">
                <h5>Order Summary</h5>
                <div class="d-flex justify-content-between">
                    <span>Subtotal</span>
                    <strong>IDR {{ number_format($subtotal, 0, ',', '.') }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Admin Fee</span>
                    <strong>IDR {{ number_format($adminFee, 0, ',', '.') }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Tax ({{ $taxRate * 100 }}%)</span>
                    <strong>IDR {{ number_format($taxAmount, 0, ',', '.') }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Shipping</span>
                    <strong>IDR {{ number_format($shippingPrice, 0, ',', '.') }}</strong>
                </div>
                @if($voucherAmount > 0)
                <div class="d-flex justify-content-between">
                    <span>Voucher</span>
                    <strong>- IDR {{ number_format($voucherAmount, 0, ',', '.') }}</strong>
                </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between">
                    <span><strong>Total Payment:</strong></span>
                    <strong>IDR {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                </div>
            </div>
            
            <div class="mb-4">
                <h5>Pay with:</h5>
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>Card payment is currently unavailable. Please use Transfer or Cash.
                </div>
                <div class="btn-group mb-3" role="group">
                    <input type="radio" class="btn-check" name="payment_method" id="transfer" value="transfer" checked>
                    <label class="btn btn-outline-primary" for="transfer"><i class="bi bi-bank me-2"></i>Transfer</label>
                    
                    <input type="radio" class="btn-check" name="payment_method" id="cash" value="cash">
                    <label class="btn btn-outline-primary" for="cash"><i class="bi bi-cash-coin me-2"></i>Cash</label>
                </div>
                
                <div id="transferDetails" class="mt-3">
                    <div class="alert alert-warning">
                        <strong>Transfer Instructions:</strong><br>
                        Please transfer to:<br>
                        Bank: BCA<br>
                        Account Number: 1234567890<br>
                        Account Name: Kisora Shop<br>
                        <small class="text-muted">Please include order number in transfer description</small>
                    </div>
                </div>
                
                <div id="cashDetails" class="mt-3" style="display: none;">
                    <div class="alert alert-warning">
                        <strong>Cash Payment:</strong><br>
                        Please prepare exact cash amount. Payment will be collected upon delivery.
                    </div>
                </div>
            </div>
            
            <form action="{{ route('payment.process', $order->id) }}" method="POST">
                @csrf
                <input type="hidden" name="payment_method" id="payment_method_input" value="transfer">
                <button type="submit" class="btn btn-primary btn-lg">Pay</button>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('payment_method_input').value = this.value;
        if (this.value === 'transfer') {
            document.getElementById('transferDetails').style.display = 'block';
            document.getElementById('cashDetails').style.display = 'none';
        } else if (this.value === 'cash') {
            document.getElementById('transferDetails').style.display = 'none';
            document.getElementById('cashDetails').style.display = 'block';
        }
    });
});
</script>
@endsection


