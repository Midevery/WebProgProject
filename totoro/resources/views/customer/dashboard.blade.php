@extends('layouts.app')

@section('title', 'Dashboard - Kisora Shop')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const wishlistForms = document.querySelectorAll('.wishlist-form');
    
    wishlistForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const button = this.querySelector('.wishlist-btn');
            const icon = button.querySelector('i');
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'added') {
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill');
                    button.classList.add('active');
                } else if (data.status === 'removed') {
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.add('bi-heart');
                    button.classList.remove('active');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.location.reload();
            });
        });
    });
});
</script>
@endpush

@section('content')
<div class="container-fluid my-4">
    <div class="row">
        <!-- Left Sidebar - User Summary Cards -->
        <div class="col-md-3 mb-4">
            <!-- Order in Progress -->
            <div class="card mb-3 dashboard-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-box-arrow-up" style="font-size: 2rem; color: var(--kisora-blue);"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small">Order in Progress</p>
                            <h4 class="mb-0">{{ $ordersInProgress }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total Spending This Month -->
            <div class="card mb-3 dashboard-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-currency-dollar" style="font-size: 2rem; color: var(--kisora-blue);"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small">Total Spending This Month</p>
                            <h4 class="mb-0">IDR {{ number_format($totalSpending, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reward Point -->
            <div class="card mb-3 dashboard-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-star-fill" style="font-size: 2rem; color: var(--kisora-blue);"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small">Reward Point</p>
                            <h4 class="mb-0">{{ $rewardPoints }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Active Voucher -->
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-percent" style="font-size: 2rem; color: var(--kisora-blue);"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small">Active Voucher</p>
                            <h4 class="mb-0">{{ $activeVouchers }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="col-md-9">
            <!-- Recommended For You -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="mb-4">Recommended For You</h4>
                    <div class="row g-3">
                        @foreach($recommendedProducts as $product)
                        <div class="col-6 col-md-3">
                            <div class="product-card">
                                <a href="{{ route('products.show', $product->id) }}" class="text-decoration-none text-dark">
                                    <div class="position-relative">
                                        <img src="{{ asset($product->image) }}" class="product-image" alt="{{ $product->name }}" onerror="this.src='https://via.placeholder.com/200x200?text=No+Image'">
                                        @if($product->stock > 0)
                                            <span class="product-badge badge-ready">Ready Stock</span>
                                        @else
                                            <span class="product-badge badge-preorder">Pre-Order</span>
                                        @endif
                                    </div>
                                    <div class="p-3">
                                        <h6 class="mb-1 small">{{ $product->name }}</h6>
                                        <p class="product-price mb-2">IDR {{ number_format($product->price, 0, ',', '.') }}</p>
                                        <div class="d-flex gap-2">
                                            @auth
                                            <form action="{{ route('wishlist.toggle') }}" method="POST" class="wishlist-form flex-grow-1">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                @php
                                                    $inWishlist = in_array($product->id, $wishlistProductIds ?? []);
                                                @endphp
                                                <button type="submit" class="btn btn-sm btn-outline-danger w-100 wishlist-btn {{ $inWishlist ? 'active' : '' }}">
                                                    <i class="bi bi-heart{{ $inWishlist ? '-fill' : '' }}"></i>
                                                </button>
                                            </form>
                                            @else
                                            <a href="{{ route('signin') }}" class="btn btn-sm btn-outline-danger w-100">
                                                <i class="bi bi-heart"></i>
                                            </a>
                                            @endauth
                                            <form action="{{ route('cart.store') }}" method="POST" class="flex-grow-1">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                                    <i class="bi bi-cart3"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Order Statistics -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-4">Order Statistics</h5>
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="text-center me-4">
                                    <div class="mb-3">
                                        @php
                                            $totalOrders = $ongoingCount + $cancelledCount + $deliveredCount;
                                            if ($totalOrders > 0) {
                                                $ongoingPercent = ($ongoingCount / $totalOrders) * 360;
                                                $cancelledPercent = ($cancelledCount / $totalOrders) * 360;
                                                $deliveredPercent = ($deliveredCount / $totalOrders) * 360;
                                                
                                                $ongoingEnd = $ongoingPercent;
                                                $cancelledStart = $ongoingEnd;
                                                $cancelledEnd = $cancelledStart + $cancelledPercent;
                                                $deliveredStart = $cancelledEnd;
                                            } else {
                                                // Default: show empty circle with gray background
                                                $ongoingEnd = 0;
                                                $cancelledStart = 0;
                                                $cancelledEnd = 0;
                                                $deliveredStart = 0;
                                            }
                                        @endphp
                                        @if($totalOrders > 0)
                                        <div style="width: 150px; height: 150px; border-radius: 50%; background: conic-gradient(
                                            #1E88E5 0deg {{ $ongoingEnd }}deg,
                                            #90CAF9 {{ $cancelledStart }}deg {{ $cancelledEnd }}deg,
                                            #64B5F6 {{ $deliveredStart }}deg 360deg
                                        );"></div>
                                        @else
                                        <div style="width: 150px; height: 150px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                                            <small class="text-muted">No orders yet</small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-2">
                                        <span class="badge" style="background-color: #1E88E5; width: 20px; height: 20px; display: inline-block;"></span>
                                        <span class="ms-2">Ongoing ({{ $ongoingCount }})</span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="badge" style="background-color: #90CAF9; width: 20px; height: 20px; display: inline-block;"></span>
                                        <span class="ms-2">Cancelled ({{ $cancelledCount }})</span>
                                    </div>
                                    <div>
                                        <span class="badge" style="background-color: #64B5F6; width: 20px; height: 20px; display: inline-block;"></span>
                                        <span class="ms-2">Delivered ({{ $deliveredCount }})</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Order -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Recent Order</h5>
                                <a href="{{ route('shipping.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Photo</th>
                                            <th>Product Name</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentOrders as $order)
                                            @foreach($order->orderItems->take(1) as $item)
                                            <tr>
                                                <td>
                                                    <img src="{{ asset($item->product->image) }}" class="rounded" alt="{{ $item->product->name }}" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/50x50?text=No+Image'">
                                                </td>
                                                <td>{{ $item->product->name }}</td>
                                                <td>IDR {{ number_format($item->price, 0, ',', '.') }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No recent orders</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

