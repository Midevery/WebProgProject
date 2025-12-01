@extends('layouts.app')

@section('title', 'Artist Dashboard - Kisora Shop')

@push('styles')
<style>
    .dashboard-card {
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
    
    .product-table {
        width: 100%;
    }
    
    .product-table th {
        background-color: var(--kisora-light-blue);
        padding: 1rem;
        text-align: left;
        font-weight: 600;
    }
    
    .product-table td {
        padding: 1rem;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .product-image-small {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
    }
    
    .badge-status {
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-in-stock {
        background-color: #28a745;
        color: white;
    }
    
    .badge-out-of-stock {
        background-color: #dc3545;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title mb-0">Artist Dashboard</h1>
        <a href="{{ route('home') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">IDR {{ number_format($totalSales, 0, ',', '.') }}</div>
                <div class="stat-label">Gross Sales</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #FFE0B2 0%, #FFCC80 100%);">
                <div class="stat-value">IDR {{ number_format($totalCost ?? 0, 0, ',', '.') }}</div>
                <div class="stat-label">Total Cost</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #FFCDD2 0%, #EF9A9A 100%);">
                <div class="stat-value">IDR {{ number_format($totalPlatformFee, 0, ',', '.') }}</div>
                <div class="stat-label">Platform Fee</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #C8E6C9 0%, #A5D6A7 100%);">
                <div class="stat-value">IDR {{ number_format($netSales, 0, ',', '.') }}</div>
                <div class="stat-label">Net Sales (You Receive)</div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $totalOrders }}</div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
    </div>

    <!-- Products List -->
    <div class="dashboard-card">
        <h3 class="mb-4">My Products</h3>
        
        @if($productsWithSales->count() > 0)
        <div class="table-responsive">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th>Total Sales</th>
                        <th>Gross Earning</th>
                        <th>Cost</th>
                        <th>Platform Fee</th>
                        <th>Net Earning</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productsWithSales as $product)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ asset($product->image) }}" 
                                     alt="{{ $product->name }}" 
                                     class="product-image-small me-3"
                                     onerror="this.src='https://via.placeholder.com/60x60?text=No+Image'">
                                <div>
                                    <div class="fw-bold">{{ $product->name }}</div>
                                    <small class="text-muted">
                                        Series: {{ $product->category->name ?? 'N/A' }}<br>
                                        Category: {{ $product->category->name ?? 'N/A' }}
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>IDR {{ number_format($product->price, 0, ',', '.') }}</strong>
                        </td>
                        <td>
                            <strong>{{ $product->total_sold ?? 0 }}</strong>
                        </td>
                        <td>
                            <div>
                                <strong>IDR {{ number_format($product->total_earning ?? 0, 0, ',', '.') }}</strong>
                            </div>
                        </td>
                        <td>
                            <div>
                                <small class="text-muted">- IDR {{ number_format(($product->total_sold ?? 0) * ($product->cost ?? 0), 0, ',', '.') }}</small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <small class="text-muted">- IDR {{ number_format($product->platform_fee ?? 0, 0, ',', '.') }}</small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong class="text-success">IDR {{ number_format($product->net_earning ?? 0, 0, ',', '.') }}</strong>
                            </div>
                        </td>
                        <td>
                            @if($product->stock > 0)
                                <span class="badge-status badge-in-stock">In Stock</span>
                            @else
                                <span class="badge-status badge-out-of-stock">Out of Stock</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('artist.product.analytics', $product->id) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-graph-up me-1"></i>View Analytics
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <p class="text-muted">No products yet. Products will be added by admin.</p>
        </div>
        @endif
    </div>
</div>
@endsection


