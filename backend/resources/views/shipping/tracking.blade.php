@extends('layouts.app')

@section('title', 'Track Order - Kisora Shop')

@section('content')
<div class="container my-4">
    <a href="{{ route('shipping.index') }}" class="btn btn-outline-primary mb-3">← Back</a>
    
    <h2 class="mb-4">Track Order #{{ $order->order_number }}</h2>
    
    <!-- Order Status Timeline -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-4">Order Status</h5>
            <div class="row">
                <div class="col-md-12">
                    <div class="timeline">
                        @php
                            $statuses = [
                                'pending' => ['Pending', 'Order received'],
                                'processing' => ['Processing', 'Order is being prepared'],
                                'shipped' => ['Shipped', 'Order has been shipped'],
                                'in_transit' => ['In Transit', 'Order is on the way'],
                                'delivered' => ['Delivered', 'Order has been delivered']
                            ];
                            $currentStatus = $order->shipping ? $order->shipping->status : 'pending';
                            $statusOrder = ['pending', 'processing', 'shipped', 'in_transit', 'delivered'];
                            $currentIndex = array_search($currentStatus, $statusOrder);
                        @endphp
                        
                        @foreach($statusOrder as $index => $status)
                            @if(isset($statuses[$status]))
                                <div class="timeline-item mb-4">
                                    <div class="d-flex align-items-start">
                                        <div class="timeline-marker me-3 {{ $index <= $currentIndex ? 'active' : '' }}">
                                            <i class="bi bi-{{ $index <= $currentIndex ? 'check-circle-fill' : 'circle' }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 {{ $index <= $currentIndex ? 'text-primary' : 'text-muted' }}">
                                                {{ $statuses[$status][0] }}
                                            </h6>
                                            <p class="text-muted small mb-0">{{ $statuses[$status][1] }}</p>
                                            @if($index <= $currentIndex && $order->shipping)
                                                @if($status === 'shipped' && $order->shipping->shipped_at)
                                                    <small class="text-muted">{{ $order->shipping->shipped_at->format('d M Y H:i') }}</small>
                                                @elseif($status === 'delivered' && $order->shipping->delivered_at)
                                                    <small class="text-muted">{{ $order->shipping->delivered_at->format('d M Y H:i') }}</small>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    @if($index < count($statusOrder) - 1)
                                        <div class="timeline-line ms-4 {{ $index < $currentIndex ? 'active' : '' }}"></div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Details -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Order Details</h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
                    <p><strong>Order Date:</strong> {{ $order->created_at->format('d M Y H:i') }}</p>
                    <p><strong>Total Amount:</strong> IDR {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                    <p><strong>Order Status:</strong> 
                        <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'shipped' ? 'info' : 'warning') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Shipping Address:</strong></p>
                    <p class="text-muted">{{ $order->shipping_address }}</p>
                    <p><strong>Shipping Method:</strong> {{ $order->shipping_method ?? 'Standard' }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Shipping Information -->
    @if($order->shipping)
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Shipping Information</h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Courier:</strong> {{ $order->shipping->courier ?? 'Not set' }}</p>
                    <p><strong>Tracking Number:</strong> 
                        @if($order->shipping->tracking_number)
                            <span class="badge bg-info">{{ $order->shipping->tracking_number }}</span>
                        @else
                            <span class="text-muted">Not available yet</span>
                        @endif
                    </p>
                    <p><strong>Shipping Status:</strong> 
                        <span class="badge bg-{{ $order->shipping->status === 'delivered' ? 'success' : ($order->shipping->status === 'shipped' ? 'info' : 'warning') }}">
                            {{ ucfirst($order->shipping->status) }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    @if($order->shipping->shipped_at)
                        <p><strong>Shipped At:</strong> {{ $order->shipping->shipped_at->format('d M Y H:i') }}</p>
                    @endif
                    @if($order->shipping->delivered_at)
                        <p><strong>Delivered At:</strong> {{ $order->shipping->delivered_at->format('d M Y H:i') }}</p>
                    @endif
                    @if($order->shipping->notes)
                        <p><strong>Notes:</strong> {{ $order->shipping->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Order Items -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Order Items</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->orderItems as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset($item->product->image) }}" class="rounded me-2" alt="{{ $item->product->name }}" style="width: 60px; height: 60px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/60x60?text=No+Image'">
                                    <div>
                                        <strong>{{ $item->product->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $item->product->category->name ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>IDR {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td><strong>IDR {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <th>IDR {{ number_format($order->total_amount, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-marker {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    font-size: 1.2rem;
}

.timeline-marker.active {
    background: var(--kisora-blue);
    color: white;
}

.timeline-line {
    width: 2px;
    height: 40px;
    background: #e9ecef;
    margin-left: 20px;
    margin-top: -10px;
}

.timeline-line.active {
    background: var(--kisora-blue);
}
</style>
@endsection
