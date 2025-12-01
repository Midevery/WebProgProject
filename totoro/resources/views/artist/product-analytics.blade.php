@extends('layouts.app')

@section('title', 'Product Analytics - ' . $product->name . ' - Kisora Shop')

@push('styles')
<style>
    .analytics-card {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stat-card {
        background: linear-gradient(135deg, #87CEEB 0%, #6BB6D6 100%);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        text-align: center;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    .product-image-large {
        width: 100%;
        max-width: 400px;
        height: auto;
        border-radius: 10px;
        object-fit: contain;
    }
    
    .table-analytics {
        width: 100%;
    }
    
    .table-analytics th {
        background-color: var(--kisora-light-blue);
        padding: 1rem;
        text-align: left;
        font-weight: 600;
    }
    
    .table-analytics td {
        padding: 1rem;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .chart-container {
        height: 300px;
        margin: 1rem 0;
    }
</style>
@endpush

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title mb-0">Product Analytics: {{ $product->name }}</h1>
        <a href="{{ route('artist.dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Product Information -->
    <div class="analytics-card">
        <div class="row">
            <div class="col-md-4 text-center">
                <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="product-image-large mb-3" onerror="this.src='https://via.placeholder.com/400x400?text=No+Image'">
            </div>
            <div class="col-md-8">
                <h3>{{ $product->name }}</h3>
                <p class="text-muted">Category: {{ $product->category->name ?? 'N/A' }}</p>
                <p class="text-muted">Price: <strong>IDR {{ number_format($product->price, 0, ',', '.') }}</strong></p>
                <p class="text-muted">Stock: 
                    @if($product->stock > 10)
                        <span class="badge bg-success">{{ $product->stock }} In Stock</span>
                    @elseif($product->stock > 0)
                        <span class="badge bg-warning">{{ $product->stock }} Low Stock</span>
                    @else
                        <span class="badge bg-danger">Out of Stock</span>
                    @endif
                </p>
                <p class="text-muted">Total Views: <strong>{{ $product->clicks }}</strong></p>
                <p class="text-muted">Created: {{ $product->created_at->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Key Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $totalSold }}</div>
                <div class="stat-label">Total Sold</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">IDR {{ number_format($totalEarning, 0, ',', '.') }}</div>
                <div class="stat-label">Gross Earning</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $totalOrders }}</div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">IDR {{ number_format($netEarning, 0, ',', '.') }}</div>
                <div class="stat-label">Net Earning</div>
            </div>
        </div>
    </div>
    
    <!-- Earning Breakdown -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="analytics-card">
                <h5 class="mb-3">Earning Breakdown</h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><strong>Gross Earning:</strong></span>
                                <span class="text-primary"><strong>IDR {{ number_format($totalEarning, 0, ',', '.') }}</strong></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><strong>Cost ({{ number_format($productCost, 0, ',', '.') }} x {{ $totalSold }}):</strong></span>
                                <span class="text-warning"><strong>- IDR {{ number_format($totalCost, 0, ',', '.') }}</strong></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><strong>Platform Fee ({{ number_format($platformFee, 0, ',', '.') }} x {{ $totalSold }}):</strong></span>
                                <span class="text-danger"><strong>- IDR {{ number_format($totalPlatformFee, 0, ',', '.') }}</strong></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-success bg-opacity-10 rounded mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><strong>Net Earning (You Receive):</strong></span>
                                <span class="text-success"><strong>IDR {{ number_format($netEarning, 0, ',', '.') }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Metrics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="analytics-card">
                <h5 class="mb-3">Performance Metrics</h5>
                <table class="table-analytics">
                    <tr>
                        <td><strong>Average Order Value</strong></td>
                        <td>IDR {{ number_format($avgOrderValue, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Views</strong></td>
                        <td>{{ $product->clicks }}</td>
                    </tr>
                    <tr>
                        <td><strong>Conversion Rate</strong></td>
                        <td>{{ number_format($conversionRate, 2) }}%</td>
                    </tr>
                    <tr>
                        <td><strong>Views per Sale</strong></td>
                        <td>{{ $totalOrders > 0 ? number_format($product->clicks / $totalOrders, 1) : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="analytics-card">
                <h5 class="mb-3">Sales Summary</h5>
                <table class="table-analytics">
                    <tr>
                        <td><strong>Total Quantity Sold</strong></td>
                        <td>{{ $totalSold }} units</td>
                    </tr>
                    <tr>
                        <td><strong>Gross Revenue</strong></td>
                        <td>IDR {{ number_format($totalEarning, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Cost</strong></td>
                        <td class="text-warning">- IDR {{ number_format($totalCost, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Platform Fee</strong></td>
                        <td class="text-danger">- IDR {{ number_format($totalPlatformFee, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Net Revenue (You Receive)</strong></td>
                        <td class="text-success"><strong>IDR {{ number_format($netEarning, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Total Orders</strong></td>
                        <td>{{ $totalOrders }} orders</td>
                    </tr>
                    <tr>
                        <td><strong>Average per Order</strong></td>
                        <td>{{ $totalOrders > 0 ? number_format($totalSold / $totalOrders, 1) : '0' }} units</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Sales by Date (Last 30 Days) -->
    @if($salesByDate->count() > 0)
    <div class="analytics-card">
        <h5 class="mb-4">Sales Trend (Last 30 Days)</h5>
        <div class="table-responsive">
            <table class="table-analytics">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Quantity Sold</th>
                        <th>Earning</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesByDate as $sale)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</td>
                        <td><strong>{{ $sale->quantity_sold }}</strong></td>
                        <td class="text-success"><strong>IDR {{ number_format($sale->earning, 0, ',', '.') }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Sales by Month (Last 12 Months) -->
    @if($salesByMonth->count() > 0)
    <div class="analytics-card">
        <h5 class="mb-4">Monthly Sales (Last 12 Months)</h5>
        <div class="table-responsive">
            <table class="table-analytics">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Quantity Sold</th>
                        <th>Earning</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesByMonth as $sale)
                    <tr>
                        <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $sale->month)->format('M Y') }}</td>
                        <td><strong>{{ $sale->quantity_sold }}</strong></td>
                        <td class="text-success"><strong>IDR {{ number_format($sale->earning, 0, ',', '.') }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Recent Orders -->
    @if($recentOrders->count() > 0)
    <div class="analytics-card">
        <h5 class="mb-4">Recent Orders</h5>
        <div class="table-responsive">
            <table class="table-analytics">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Customer</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $item)
                    <tr>
                        <td>{{ $item->order->order_number ?? 'N/A' }}</td>
                        <td>{{ $item->order->user->name ?? 'N/A' }}</td>
                        <td><strong>{{ $item->quantity }}</strong></td>
                        <td class="text-success"><strong>IDR {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                        <td>{{ $item->created_at->format('d M Y H:i') }}</td>
                        <td>
                            <span class="badge bg-{{ $item->order->status === 'delivered' ? 'success' : ($item->order->status === 'shipped' ? 'info' : 'warning') }}">
                                {{ ucfirst($item->order->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="analytics-card">
        <div class="text-center py-5">
            <p class="text-muted">No orders yet for this product.</p>
        </div>
    </div>
    @endif
</div>
@endsection

