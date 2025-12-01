@extends('layouts.app')

@section('title', 'Home - Kisora Shop')

@section('content')
<div class="container my-5">
    <!-- Product Category Section -->
    <section class="mb-5">
        <h2 class="section-title">Product Category</h2>
        <div class="row g-4">
            @foreach($categories as $index => $category)
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('products.index', ['category' => $category->id]) }}" class="category-card">
                    <img src="https://picsum.photos/150/150?random={{ $category->id }}" alt="{{ $category->name }}">
                    <p class="mb-0">{{ $category->name }}</p>
                </a>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Our Illustrator Section -->
    <section class="mb-5">
        <h2 class="section-title">Our Illustrator</h2>
        <div class="row g-4">
            @foreach($artists as $artist)
            <div class="col-md-4 text-center">
                <a href="{{ route('artist.show', $artist->id) }}" class="text-decoration-none text-dark">
                    <div class="illustrator-card">
                        <img src="{{ $artist->profile_image ? asset($artist->profile_image) : 'https://picsum.photos/120/120?random=' . $artist->id }}" alt="{{ $artist->name }}" onerror="this.src='https://picsum.photos/120/120?random={{ $artist->id }}'">
                        <h5>{{ $artist->name }}</h5>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Our Product Section -->
    <section class="mb-5">
        <h2 class="section-title">Our Product</h2>
        <div class="row g-4">
            @foreach($featuredProducts as $product)
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('products.show', $product->id) }}" class="text-decoration-none text-dark">
                    <div class="product-card">
                        <div class="position-relative">
                            <img src="{{ asset($product->image) }}" class="product-image" alt="{{ $product->name }}" onerror="this.src='https://via.placeholder.com/200x200?text=No+Image'">
                            @if($product->stock > 0)
                                <span class="product-badge badge-ready">Ready Stock</span>
                            @else
                                <span class="product-badge badge-preorder">Pre-Order</span>
                            @endif
                        </div>
                        <div class="p-3">
                            <h6 class="mb-1">{{ $product->name }}</h6>
                            <p class="text-muted small mb-1">60 x 40 cm</p>
                            <p class="product-price mb-0">IDR {{ number_format($product->price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Popular & Trending Series Section -->
    <section class="mb-5">
        <h2 class="section-title">Popular & Trending Series</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <img src="https://picsum.photos/400/250?random=30" class="trending-banner" alt="Anime Figure">
            </div>
            <div class="col-md-4">
                <img src="https://picsum.photos/400/250?random=31" class="trending-banner" alt="Spooktober">
            </div>
            <div class="col-md-4">
                <img src="https://picsum.photos/400/250?random=32" class="trending-banner" alt="Trick or Treat">
            </div>
        </div>
    </section>

    <!-- Product Ranking Section -->
    <section class="mb-5">
        <h2 class="section-title">Product Ranking</h2>
        <div class="row g-4">
            @foreach($trendingProducts as $product)
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('products.show', $product->id) }}" class="text-decoration-none">
                    <img src="{{ asset($product->image) }}" class="product-image rounded" alt="{{ $product->name }}" onerror="this.src='https://via.placeholder.com/200x200?text=No+Image'">
                </a>
            </div>
            @endforeach
        </div>
    </section>

    <!-- See All Products Button -->
        <div class="text-center">
        <a href="{{ route('products.index') }}" class="btn btn-see-all">Click to see all of our products!</a>
    </div>
</div>
@endsection

