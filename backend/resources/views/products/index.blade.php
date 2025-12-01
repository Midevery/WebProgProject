@extends('layouts.app')

@section('title', 'Display All Product - Kisora Shop')

@php
    $selectedCategories = $selectedCategories ?? collect(request()->input('categories', []))
        ->filter(fn($id) => $id !== null && $id !== '')
        ->map(fn($id) => (int) $id)
        ->toArray();

    if (request('category')) {
        $selectedCategories[] = (int) request('category');
        $selectedCategories = array_unique($selectedCategories);
    }
@endphp

@section('content')
<div class="container my-4">
    <h2 class="mb-4">Display All Product</h2>
    
    <div class="row">
        <!-- Main Products Section -->
        <div class="col-lg-9">
            <h3 class="section-title mb-4">Our Products</h3>
            <div class="row g-4">
                @forelse($products as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <a href="{{ route('products.show', $product->id) }}" class="text-decoration-none text-dark">
                            <div class="position-relative">
                                <img src="{{ asset($product->image) }}" class="product-image" alt="{{ $product->name }}" onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
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
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <p class="text-center text-muted">No products found.</p>
                </div>
                @endforelse
            </div>
            
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
        
        <!-- Filter Sidebar -->
        <div class="col-lg-3">
            <h3 class="section-title mb-4">Filter</h3>
            <form method="GET" action="{{ route('products.index') }}" id="filterForm">
                <!-- Preserve search parameter -->
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                
                <!-- Availability -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Availability In Shop</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="availability" value="ready" id="ready" {{ request('availability') === 'ready' ? 'checked' : '' }} onchange="document.getElementById('filterForm').submit();">
                            <label class="form-check-label" for="ready">Ready Stock</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="availability" value="preorder" id="preorder" {{ request('availability') === 'preorder' ? 'checked' : '' }} onchange="document.getElementById('filterForm').submit();">
                            <label class="form-check-label" for="preorder">Pre Order</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="availability" value="" id="all_availability" {{ !request('availability') ? 'checked' : '' }} onchange="document.getElementById('filterForm').submit();">
                            <label class="form-check-label" for="all_availability">All</label>
                        </div>
                    </div>
                </div>
                
                <!-- Price Range -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Price Range</h6>
                        <div class="mb-2">
                            <label class="form-label small">Min</label>
                            <div class="input-group">
                                <span class="input-group-text">IDR</span>
                                <input type="number" class="form-control" name="min_price" value="{{ request('min_price') }}" placeholder="0">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Max</label>
                            <div class="input-group">
                                <span class="input-group-text">IDR</span>
                                <input type="number" class="form-control" name="max_price" value="{{ request('max_price') }}" placeholder="1000000">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary w-100">Apply Price Filter</button>
                    </div>
                </div>
                
                <!-- Category -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="card-title mb-0">Character & Series</h6>
                            <button type="button" class="btn btn-link btn-sm p-0" id="clearCategoriesBtn">Clear</button>
                        </div>
                        <p class="text-muted small mb-3">Choose any combination or leave all unchecked to view every character.</p>
                        <div class="category-checkboxes">
                            @foreach($categories as $category)
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="categories[]"
                                        value="{{ $category->id }}"
                                        id="cat{{ $category->id }}"
                                        {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="cat{{ $category->id }}">{{ $category->name }}</label>
                                </div>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100 mt-3">Apply Character Filter</button>
                    </div>
                </div>
                
                <!-- Search Illustrator -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Search Illustrator</h6>
                        <input type="text" class="form-control mb-2" name="illustrator" value="{{ request('illustrator') }}" placeholder="Input Illustrator">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </div>
                
                <button type="button" class="btn btn-outline-secondary w-100" id="resetFiltersBtn">Reset Filters</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    if (!filterForm) {
        return;
    }
    const clearCategoriesBtn = document.getElementById('clearCategoriesBtn');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');

    if (clearCategoriesBtn) {
        clearCategoriesBtn.addEventListener('click', function() {
            filterForm.querySelectorAll('input[name="categories[]"]').forEach(cb => cb.checked = false);
        });
    }

    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            filterForm.querySelectorAll('input[type="checkbox"]').forEach(input => input.checked = false);
            filterForm.querySelectorAll('input[type="radio"]').forEach(input => input.checked = false);

            const allAvailability = document.getElementById('all_availability');
            if (allAvailability) {
                allAvailability.checked = true;
            }

            filterForm.querySelectorAll('input[type="number"], input[type="text"]').forEach(input => input.value = '');
            filterForm.querySelectorAll('input[type="hidden"][name="search"]').forEach(input => input.remove());

            filterForm.submit();
        });
    }
});
</script>
@endpush

