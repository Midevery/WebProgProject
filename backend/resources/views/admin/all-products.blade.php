@extends('layouts.app')

@section('title', 'All Products - Admin - Kisora Shop')

@push('styles')
<style>
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
    
    .badge-low-stock {
        background-color: #ffc107;
        color: #000;
    }
</style>
@endpush

@section('content')
<div class="container my-5">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title mb-0">All Products</h1>
        <a href="{{ route('admin.add-product') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add Product
        </a>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.all-products') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by ID, name, status" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="in_stock" {{ request('status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="sort" class="form-select">
                        <option value="new" {{ request('sort') === 'new' ? 'selected' : '' }}>New Order</option>
                        <option value="old" {{ request('sort') === 'old' ? 'selected' : '' }}>Old Order</option>
                        <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                        <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price Low-High</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price High-Low</option>
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

    <!-- Products Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Cost</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="product-image-small me-3" onerror="this.src='https://via.placeholder.com/60x60?text=No+Image'">
                                    <div>
                                        <div class="fw-bold">SKU: {{ $product->id }}</div>
                                        <small class="text-muted">Last Updated: {{ $product->updated_at->format('d M, Y') }}</small>
                                        <div class="mt-1">
                                            <strong>{{ $product->name }}</strong><br>
                                            <small class="text-muted">
                                                Series: {{ $product->category->name ?? 'N/A' }}<br>
                                                Category: {{ $product->category->name ?? 'N/A' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>IDR {{ number_format($product->price, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <strong>IDR {{ number_format($product->cost ?? 0, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <strong>{{ $product->stock }}</strong>
                            </td>
                            <td>
                                @if($product->stock > 10)
                                    <span class="badge-status badge-in-stock">In Stock</span>
                                @elseif($product->stock > 0)
                                    <span class="badge-status badge-low-stock">Low Stock</span>
                                @else
                                    <span class="badge-status badge-out-of-stock">Out of Stock</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.edit-product', $product->id) }}" class="btn btn-sm btn-outline-primary">Edit Details</a>
                                    <form action="{{ route('admin.delete-product', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <p class="text-muted">No products found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center gap-2">
                @if($products->onFirstPage())
                    <button class="btn btn-outline-secondary" disabled>Previous</button>
                @else
                    <a href="{{ $products->previousPageUrl() }}" class="btn btn-outline-primary">Previous</a>
                @endif
                
                <span class="align-self-center px-3">Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
                
                @if($products->hasMorePages())
                    <a href="{{ $products->nextPageUrl() }}" class="btn btn-outline-primary">Next</a>
                @else
                    <button class="btn btn-outline-secondary" disabled>Next</button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


