@extends('layouts.app')

@section('title', 'Track Order - Kisora Shop')

@section('content')
<div class="container my-4">
    <h2 class="mb-4">My Orders</h2>
    
    @forelse($orders as $order)
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5>Order #{{ $order->order_number }}</h5>
                    <p class="text-muted mb-1">Date: {{ $order->created_at->format('d M Y') }}</p>
                    <p class="text-muted mb-1">Total: IDR {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                    <p class="mb-0">
                        <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'shipped' ? 'info' : 'warning') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('shipping.tracking', $order->id) }}" class="btn btn-primary">View Details</a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-box-seam" style="font-size: 4rem; color: #ccc;"></i>
            <p class="text-muted mt-3">No orders yet</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary">Start Shopping</a>
        </div>
    </div>
    @endforelse
</div>
@endsection

