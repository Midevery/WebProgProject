@extends('layouts.app')

@section('title', 'All Orders - Admin - Kisora Shop')

@push('styles')
<style>
    .order-table {
        width: 100%;
    }
    
    .order-table th {
        background-color: var(--kisora-light-blue);
        padding: 1rem;
        text-align: left;
        font-weight: 600;
    }
    
    .order-table td {
        padding: 1rem;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .product-image-small {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
    }
    
    .nav-tabs .nav-link {
        color: #333;
    }
    
    .nav-tabs .nav-link.active {
        background-color: var(--kisora-light-blue);
        border-color: var(--kisora-blue);
    }
</style>
@endpush

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title mb-0">All Order</h1>
    </div>

    <!-- Status Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ request('status') === null || request('status') === 'all' ? 'active' : '' }}" href="{{ route('admin.all-orders', ['status' => 'all']) }}">
                All order ({{ $orderCounts['all'] }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') === 'processing' ? 'active' : '' }}" href="{{ route('admin.all-orders', ['status' => 'processing']) }}">
                Processing ({{ $orderCounts['processing'] }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') === 'shipped' ? 'active' : '' }}" href="{{ route('admin.all-orders', ['status' => 'shipped']) }}">
                Shipped ({{ $orderCounts['shipped'] }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') === 'delivered' ? 'active' : '' }}" href="{{ route('admin.all-orders', ['status' => 'delivered']) }}">
                Delivered ({{ $orderCounts['delivered'] }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') === 'cancelled' ? 'active' : '' }}" href="{{ route('admin.all-orders', ['status' => 'cancelled']) }}">
                Canceled ({{ $orderCounts['cancelled'] }})
            </a>
        </li>
    </ul>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.all-orders') }}" class="row g-3">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control" placeholder="Search by ID, name, status" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select">
                        <option value="new" {{ request('sort') === 'new' ? 'selected' : '' }}>New order</option>
                        <option value="old" {{ request('sort') === 'old' ? 'selected' : '' }}>Old order</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-funnel me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox"></th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        @foreach($order->orderItems as $item)
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>
                                <div>
                                    <div class="fw-bold">Customer: {{ $order->user->name }}</div>
                                    <small class="text-muted">Date of Order: {{ $order->created_at->format('d M, Y') }}</small>
                                    <div class="d-flex align-items-center mt-2">
                                        <img src="{{ asset($item->product->image) }}" alt="{{ $item->product->name }}" class="product-image-small me-3" onerror="this.src='https://via.placeholder.com/60x60?text=No+Image'">
                                        <div>
                                            <strong>{{ $item->product->name }}</strong><br>
                                            <small class="text-muted">
                                                Character: {{ $item->product->name }}<br>
                                                Company: {{ $item->product->category->name ?? 'N/A' }}<br>
                                                Quantity: {{ $item->quantity }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>IDR {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                {{ $order->payment->method ?? 'N/A' }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : ($order->status === 'shipped' ? 'info' : 'warning')) }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                                @if($order->status === 'pending')
                                <br><small class="text-muted">Please Process before {{ $order->created_at->addDays(3)->format('d M, y') }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group-vertical">
                                    <div class="mb-2">
                                        <small class="text-muted">Order ID: {{ $order->order_number }}</small>
                                    </div>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="printLabel('{{ $order->order_number }}')">Print Label</button>
                                        <form action="{{ route('admin.update-order-status', $order->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <p class="text-muted">No orders found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center gap-2">
                @if($orders->onFirstPage())
                    <button class="btn btn-outline-secondary" disabled>Previous</button>
                @else
                    <a href="{{ $orders->previousPageUrl() }}" class="btn btn-outline-primary">Previous</a>
                @endif
                
                <span class="align-self-center px-3">Page {{ $orders->currentPage() }} of {{ $orders->lastPage() }}</span>
                
                @if($orders->hasMorePages())
                    <a href="{{ $orders->nextPageUrl() }}" class="btn btn-outline-primary">Next</a>
                @else
                    <button class="btn btn-outline-secondary" disabled>Next</button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function printLabel(orderNumber) {
    window.print();
}
</script>
@endsection


