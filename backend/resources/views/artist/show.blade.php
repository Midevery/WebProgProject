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
    
    .profile-image {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--kisora-blue);
    }
    
    .product-card {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
        background: white;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .product-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
</style>
@endpush

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title mb-0">Artist Profile</h1>
        <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Products
        </a>
    </div>

    <!-- Profile Information -->
    <div class="profile-card">
        <div class="row">
            <div class="col-md-3 text-center">
                <img src="{{ $artist->profile_image ? asset($artist->profile_image) : 'https://picsum.photos/150/150?random=' . $artist->id }}" alt="{{ $artist->name }}" class="profile-image mb-3" onerror="this.src='https://picsum.photos/150/150?random={{ $artist->id }}'">
                <h4>{{ $artist->name }}</h4>
                <p class="text-muted">{{ $artist->username }}</p>
                <span class="badge bg-success">Artist/Illustrator</span>
            </div>
            <div class="col-md-9">
                <h5 class="mb-3">About This Artist</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Username:</strong>
                        <p>{{ $artist->username }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Total Products:</strong>
                        <p>{{ $artist->products->count() }} products</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Artist Products -->
    <div class="profile-card">
        <h5 class="mb-4">Artist Products</h5>
        @if($artist->products->count() > 0)
        <div class="row g-4">
            @foreach($artist->products as $product)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="product-card">
                    <a href="{{ route('products.show', $product->id) }}" class="text-decoration-none text-dark">
                        <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="product-image" onerror="this.src='https://via.placeholder.com/200x200?text=No+Image'">
                        <div class="p-3">
                            <h6 class="mb-1">{{ $product->name }}</h6>
                            <p class="text-muted small mb-1">{{ $product->category->name ?? 'N/A' }}</p>
                            <p class="product-price mb-0">IDR {{ number_format($product->price, 0, ',', '.') }}</p>
                        </div>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5">
            <p class="text-muted">No products available from this artist yet.</p>
        </div>
        @endif
    </div>
</div>
@endsection

