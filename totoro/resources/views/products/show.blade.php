@extends('layouts.app')

@section('title', $product->name . ' - Kisora Shop')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const wishlistForms = document.querySelectorAll('.wishlist-form');
    
    wishlistForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const button = this.querySelector('.wishlist-btn');
            const icon = button.querySelector('i');
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'added') {
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill');
                    button.classList.add('active');
                } else {
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.add('bi-heart');
                    button.classList.remove('active');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.location.reload();
            });
        });
    });
});
</script>
@endpush

@section('content')
@php
    $previousUrl = url()->previous();
    if (!$previousUrl || $previousUrl === url()->current()) {
        $previousUrl = route('products.index');
    }
@endphp
<div class="container my-4">
    <a href="{{ $previousUrl }}" class="btn btn-outline-primary mb-3">← Back</a>
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <!-- Product Image -->
                <div class="col-md-6">
                    <img src="{{ asset($product->image) }}" class="img-fluid rounded w-100" style="max-width: 100%; height: auto; object-fit: contain;" alt="{{ $product->name }}" onerror="this.src='https://via.placeholder.com/600x800?text=No+Image'">
                </div>
                
                <!-- Product Details -->
                <div class="col-md-6">
                    @if($product->stock > 0)
                        <span class="product-badge badge-ready mb-3">Ready Stock</span>
                    @else
                        <span class="product-badge badge-preorder mb-3">Pre-Order</span>
                    @endif
                    
                    <h2>{{ $product->name }} by <a href="{{ route('artist.show', $product->artist->id) }}">{{ $product->artist->name }}</a></h2>
                    <p class="text-muted">Measurement: 60 cm x 80 cm</p>
                    <p class="text-muted">Illustrator: <a href="{{ route('artist.show', $product->artist->id) }}">{{ $product->artist->name }}</a></p>
                    <h3 class="product-price mb-4">IDR {{ number_format($product->price, 0, ',', '.') }}</h3>
                    
                    @if(!Auth::check() || !Auth::user()->isArtist())
                    <!-- Quantity -->
                    <div class="mb-3">
                        <label class="form-label">Qty</label>
                        <div class="input-group" style="max-width: 150px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="decreaseQty()">-</button>
                            <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="{{ $product->stock }}">
                            <button class="btn btn-outline-secondary" type="button" onclick="increaseQty()">+</button>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Actions -->
                    <div class="d-flex gap-2 mb-4">
                        @auth
                            @if(!Auth::user()->isArtist())
                                <form action="{{ route('wishlist.toggle') }}" method="POST" class="wishlist-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" class="btn btn-outline-danger wishlist-btn" data-product-id="{{ $product->id }}">
                                        <i class="bi bi-heart{{ $inWishlist ? '-fill' : '' }} me-2"></i>Wishlist
                                    </button>
                                </form>
                                <form action="{{ route('cart.store') }}" method="POST" class="flex-grow-1">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" id="cart_quantity" value="1">
                                    <button type="submit" class="btn btn-primary w-100">Add to Cart</button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('signin') }}" class="btn btn-outline-danger"><i class="bi bi-heart me-2"></i>Wishlist</a>
                            <form action="{{ route('cart.store') }}" method="POST" class="flex-grow-1">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" id="cart_quantity" value="1">
                                <button type="submit" class="btn btn-primary w-100">Add to Cart</button>
                            </form>
                        @endauth
                    </div>
                    
                    <!-- Specifications -->
                    <div class="border-top pt-3">
                        <h5>Product Specifications</h5>
                        <p><strong>Character:</strong> {{ $product->name }}</p>
                        <p><strong>Series:</strong> {{ $product->category->name }}</p>
                        <p><strong>Category:</strong> {{ $product->category->name }}</p>
                        <p><strong>Manufacturer/Illustrator:</strong> <a href="{{ route('artist.show', $product->artist->id) }}">{{ $product->artist->name }}</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Comments Section -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Comments</h5>
            <div class="mb-3">
                @forelse($product->comments as $comment)
                <div class="d-flex align-items-start mb-3">
                    <img src="{{ $comment->user->profile_image ? asset($comment->user->profile_image) : 'https://picsum.photos/40/40?random=' . $comment->user->id }}" class="rounded-circle me-2" alt="{{ $comment->user->name }}" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.src='https://picsum.photos/40/40?random={{ $comment->user->id }}'">
                    <div class="flex-grow-1">
                        <strong>{{ $comment->user->name }}</strong>
                        <p class="mb-0">{{ $comment->comment }}</p>
                        <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                        @auth
                            @if(Auth::id() === $comment->user_id)
                            <form action="{{ route('comments.destroy', $comment->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0 ms-2">Delete</button>
                            </form>
                            @endif
                        @endauth
                    </div>
                </div>
                @empty
                <p class="text-muted">No comments yet. Be the first to comment!</p>
                @endforelse
            </div>
            @auth
            <form action="{{ route('comments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="input-group">
                    <input type="text" name="comment" class="form-control" placeholder="Write down your comments" required>
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-send"></i></button>
                </div>
            </form>
            @else
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Please sign in to comment" disabled>
                <a href="{{ route('signin') }}" class="btn btn-outline-primary"><i class="bi bi-box-arrow-in-right"></i> Sign In</a>
            </div>
            @endauth
        </div>
    </div>
    
    <!-- Recently Viewed -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Recently Viewed Item</h5>
            <div class="row g-3">
                @foreach($recentlyViewed as $item)
                <div class="col-md-4">
                    <div class="product-card">
                        <a href="{{ route('products.show', $item->id) }}" class="text-decoration-none text-dark">
                            <div class="position-relative">
                                <img src="{{ asset($item->image) }}" class="product-image" alt="{{ $item->name }}" onerror="this.src='https://via.placeholder.com/200x200?text=No+Image'">
                                @if($item->stock > 0)
                                    <span class="product-badge badge-ready">Ready Stock</span>
                                @else
                                    <span class="product-badge badge-preorder">Pre-Order</span>
                                @endif
                            </div>
                            <div class="p-2">
                                <h6 class="mb-1 small">{{ $item->name }}</h6>
                                <p class="text-muted small mb-1">10 x 40 cm</p>
                                <p class="product-price mb-0 small">IDR {{ number_format($item->price, 0, ',', '.') }}</p>
                            </div>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- More Stuff Like This -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">More Stuff Like this!</h5>
            <div class="row g-3">
                @foreach($similarProducts as $item)
                <div class="col-md-4">
                    <div class="product-card">
                        <a href="{{ route('products.show', $item->id) }}" class="text-decoration-none text-dark">
                            <div class="position-relative">
                                <img src="{{ asset($item->image) }}" class="product-image" alt="{{ $item->name }}" onerror="this.src='https://via.placeholder.com/200x200?text=No+Image'">
                                @if($item->stock > 0)
                                    <span class="product-badge badge-ready">Ready Stock</span>
                                @else
                                    <span class="product-badge badge-preorder">Pre-Order</span>
                                @endif
                            </div>
                            <div class="p-2">
                                <h6 class="mb-1 small">{{ $item->name }}</h6>
                                <p class="text-muted small mb-1">10 x 40 cm</p>
                                <p class="product-price mb-0 small">IDR {{ number_format($item->price, 0, ',', '.') }}</p>
                            </div>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
function increaseQty() {
    const qty = document.getElementById('quantity');
    const max = parseInt(qty.getAttribute('max'));
    if (parseInt(qty.value) < max) {
        qty.value = parseInt(qty.value) + 1;
        document.getElementById('cart_quantity').value = qty.value;
    }
}

function decreaseQty() {
    const qty = document.getElementById('quantity');
    if (parseInt(qty.value) > 1) {
        qty.value = parseInt(qty.value) - 1;
        document.getElementById('cart_quantity').value = qty.value;
    }
}

document.getElementById('quantity').addEventListener('change', function() {
    document.getElementById('cart_quantity').value = this.value;
});
</script>
@endsection

