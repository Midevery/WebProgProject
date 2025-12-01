@extends('layouts.app')

@section('title', 'My Wishlist - Kisora Shop')

@section('content')
@php
    $wishlistBackUrl = url()->previous();
    if (!$wishlistBackUrl || $wishlistBackUrl === url()->current()) {
        $wishlistBackUrl = route('products.index');
    }
@endphp
<div class="container my-4">
    <a href="{{ $wishlistBackUrl }}" class="btn btn-outline-primary mb-3">← Back</a>
    <h2 class="mb-4">My Wishlist</h2>
    
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    
    <div class="row g-4">
        @forelse($wishlists as $wishlist)
        <div class="col-6 col-md-4 col-lg-3">
            <div class="product-card">
                <a href="{{ route('products.show', $wishlist->product->id) }}" class="text-decoration-none text-dark">
                    <div class="position-relative">
                        <img src="{{ asset($wishlist->product->image) }}" class="product-image" alt="{{ $wishlist->product->name }}" onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
                        @if($wishlist->product->stock > 0)
                            <span class="product-badge badge-ready">Ready Stock</span>
                        @else
                            <span class="product-badge badge-preorder">Pre-Order</span>
                        @endif
                    </div>
                    <div class="p-3">
                        <h6 class="mb-1">{{ $wishlist->product->name }}</h6>
                        <p class="text-muted small mb-1">{{ $wishlist->product->category->name ?? 'N/A' }}</p>
                        <p class="product-price mb-0">IDR {{ number_format($wishlist->product->price, 0, ',', '.') }}</p>
                    </div>
                </a>
                <div class="p-3 pt-0">
                    <form action="{{ route('wishlist.destroy', $wishlist->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                            <i class="bi bi-heart-fill me-1"></i>Remove from Wishlist
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-heart" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">Your wishlist is empty</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary">Start Shopping</a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection


