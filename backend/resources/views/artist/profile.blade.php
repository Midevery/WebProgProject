@extends('layouts.app')

@section('title', 'Artist Profile - Kisora Shop')

@push('styles')
<style>
    .profile-card {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stat-card {
        background: linear-gradient(135deg, #87CEEB 0%, #6BB6D6 100%);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
    
    .profile-image {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--kisora-blue);
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
        <h1 class="section-title mb-0">Seller Profile</h1>
        <a href="{{ route('artist.dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Profile Information -->
    <div class="profile-card">
        <div class="row">
            <div class="col-md-3 text-center">
                <img src="{{ $artist->profile_image ? asset($artist->profile_image) : 'https://picsum.photos/150/150?random=' . $artist->id }}" alt="{{ $artist->name }}" class="profile-image mb-3" onerror="this.src='https://picsum.photos/150/150?random={{ $artist->id }}'">
                <h4>{{ $artist->name }}</h4>
                <p class="text-muted">{{ $artist->email }}</p>
                <span class="badge bg-success">Seller/Artist</span>
            </div>
            <div class="col-md-9">
                <h5 class="mb-3">My Profile</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Username:</strong>
                        <p>{{ $artist->username }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong>
                        <p>{{ $artist->email }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Phone:</strong>
                        <p>{{ $artist->phone ?? 'Not set' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Balance:</strong>
                        <p>IDR {{ number_format($artist->balance, 0, ',', '.') }}</p>
                    </div>
                </div>
                <a href="{{ route('profile.index') }}" class="btn btn-primary">Edit Profile</a>
            </div>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-value">IDR {{ number_format($totalSales, 0, ',', '.') }}</div>
                <div class="stat-label">Total Sales</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($totalOrders) }}</div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($products->count()) }}</div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>
    </div>

    <!-- Products with Analytics -->
    <div class="profile-card">
        <h5 class="mb-4">My Products & Analytics</h5>
        @if($products->count() > 0)
        <div class="table-responsive">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Total Sold</th>
                        <th>Total Orders</th>
                        <th>Gross Earning</th>
                        <th>Cost</th>
                        <th>Platform Fee</th>
                        <th>Net Earning</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="product-image-small me-3" onerror="this.src='https://via.placeholder.com/60x60?text=No+Image'">
                                <div>
                                    <strong>{{ $product->name }}</strong>
                                </div>
                            </div>
                        </td>
                        <td>{{ $product->category->name ?? 'N/A' }}</td>
                        <td><strong>IDR {{ number_format($product->price, 0, ',', '.') }}</strong></td>
                        <td>
                            @if($product->stock > 10)
                                <span class="badge bg-success">{{ $product->stock }}</span>
                            @elseif($product->stock > 0)
                                <span class="badge bg-warning">{{ $product->stock }}</span>
                            @else
                                <span class="badge bg-danger">Out of Stock</span>
                            @endif
                        </td>
                        <td><strong>{{ $product->total_sold ?? 0 }}</strong></td>
                        <td><strong>{{ $product->total_orders ?? 0 }}</strong></td>
                        <td>
                            <strong>IDR {{ number_format($product->total_earning ?? 0, 0, ',', '.') }}</strong>
                        </td>
                        <td>
                            <small class="text-muted">- IDR {{ number_format(($product->total_sold ?? 0) * ($product->cost ?? 0), 0, ',', '.') }}</small>
                        </td>
                        <td>
                            <small class="text-muted">- IDR {{ number_format($product->platform_fee ?? 0, 0, ',', '.') }}</small>
                        </td>
                        <td>
                            <strong class="text-success">IDR {{ number_format($product->net_earning ?? 0, 0, ',', '.') }}</strong>
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
