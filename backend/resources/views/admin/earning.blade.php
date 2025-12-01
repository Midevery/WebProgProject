@extends('layouts.app')

@section('title', 'Earning - Admin - Kisora Shop')

@push('styles')
<style>
    .earning-card {
        background: linear-gradient(135deg, #87CEEB 0%, #6BB6D6 100%);
        color: white;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .earning-value {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .earning-label {
        font-size: 1.1rem;
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
</style>
@endpush

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title mb-0">Earning</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Earnings Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="earning-card">
                <div class="earning-value">IDR {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                <div class="earning-label">Total Revenue (Gross)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="earning-card" style="background: linear-gradient(135deg, #FFE0B2 0%, #FFCC80 100%);">
                <div class="earning-value">IDR {{ number_format($totalCost, 0, ',', '.') }}</div>
                <div class="earning-label">Total Cost</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="earning-card" style="background: linear-gradient(135deg, #FFCDD2 0%, #EF9A9A 100%);">
                <div class="earning-value">IDR {{ number_format($totalPlatformFee, 0, ',', '.') }}</div>
                <div class="earning-label">Total Platform Fee</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="earning-card" style="background: linear-gradient(135deg, #C8E6C9 0%, #A5D6A7 100%);">
                <div class="earning-value">IDR {{ number_format($adminNetEarning, 0, ',', '.') }}</div>
                <div class="earning-label">Admin Net Earning</div>
                <small class="opacity-75">Cost + Platform Fee</small>
            </div>
        </div>
    </div>

    <!-- Product Earnings Table -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-4">Product Earnings</h5>
            <div class="table-responsive">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox"></th>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Cost</th>
                            <th>Total Sales</th>
                            <th>Gross Revenue</th>
                            <th>Cost</th>
                            <th>Platform Fee</th>
                            <th>Admin Net Earning</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productEarnings as $product)
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="product-image-small me-3" onerror="this.src='https://via.placeholder.com/60x60?text=No+Image'">
                                    <div>
                                        <div class="fw-bold">{{ $product->name }}</div>
                                        <small class="text-muted">
                                            SKU: {{ $product->id }}<br>
                                            Last Updated: {{ now()->format('d M, Y') }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>IDR {{ number_format($product->price, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <small>IDR {{ number_format($product->cost ?? 0, 0, ',', '.') }}</small>
                            </td>
                            <td>
                                <strong>{{ $product->total_sales }}</strong>
                            </td>
                            <td>
                                <strong>IDR {{ number_format($product->total_earning, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <small class="text-muted">- IDR {{ number_format($product->total_cost, 0, ',', '.') }}</small>
                            </td>
                            <td>
                                <small class="text-muted">- IDR {{ number_format($product->platform_fee, 0, ',', '.') }}</small>
                            </td>
                            <td>
                                <strong class="text-success">IDR {{ number_format($product->admin_net_earning, 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <p class="text-muted">No earnings data yet</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Category Earnings -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-4">Earnings by Category</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Total Earning</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categoryEarnings as $category)
                                <tr>
                                    <td><strong>{{ $category->name }}</strong></td>
                                    <td><strong class="text-success">IDR {{ number_format($category->total_earning, 0, ',', '.') }}</strong></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center">No data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Artist Earnings -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-4">Earnings by Artist</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Artist</th>
                                    <th>Total Earning</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($artistEarnings as $artist)
                                <tr>
                                    <td><strong>{{ $artist->name }} ({{ $artist->username }})</strong></td>
                                    <td><strong class="text-success">IDR {{ number_format($artist->total_earning, 0, ',', '.') }}</strong></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center">No data</td>
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
@endsection


