@extends('layouts.app')

@section('title', 'Edit Product - Admin - Kisora Shop')

@section('content')
<div class="container my-5">    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title mb-0">Edit Product</h1>
        <a href="{{ route('admin.all-products') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Quick Navigation</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#product-info" class="text-decoration-none">Product Information</a></li>
                        <li class="mb-2"><a href="#product-title" class="text-decoration-none">Product Title</a></li>
                        <li class="mb-2"><a href="#upload-media" class="text-decoration-none">Upload Media</a></li>
                        <li class="mb-2"><a href="#pricing" class="text-decoration-none">Pricing & Inventory</a></li>
                        <li class="mb-2"><a href="#description" class="text-decoration-none">Product Description</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.update-product', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div id="product-info" class="mb-4">
                            <h5 class="mb-3">Product Information</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Illustrator Name <span class="text-danger">*</span></label>
                                <select name="artist_id" class="form-select" required>
                                    <option value="">Select Illustrator</option>
                                    @foreach($artists as $artist)
                                    <option value="{{ $artist->id }}" {{ $product->artist_id == $artist->id ? 'selected' : '' }}>{{ $artist->name }} ({{ $artist->username }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Product Title <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="upload-media" class="mb-4">
                            <h5 class="mb-3">Upload Media</h5>
                            @if($product->image)
                            <div class="mb-3">
                                <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" style="max-width: 200px; border-radius: 5px;" onerror="this.src='https://via.placeholder.com/200x200?text=No+Image'">
                            </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label">Product Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <small class="text-muted">Leave empty to keep current image</small>
                            </div>
                        </div>

                        <div id="pricing" class="mb-4">
                            <h5 class="mb-3">Pricing & Inventory</h5>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Price (IDR) <span class="text-danger">*</span></label>
                                    <input type="number" name="price" class="form-control" value="{{ $product->price }}" min="0" step="0.01" required>
                                    <small class="text-muted">Selling price to customer</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cost/Modal (IDR) <span class="text-danger">*</span></label>
                                    <input type="number" name="cost" class="form-control" value="{{ $product->cost ?? 0 }}" min="0" step="0.01" required>
                                    <small class="text-muted">Product cost/modal</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Stock <span class="text-danger">*</span></label>
                                    <input type="number" name="stock" class="form-control" value="{{ $product->stock }}" min="0" required>
                                </div>
                            </div>
                        </div>

                        <div id="description" class="mb-4">
                            <h5 class="mb-3">Product Description</h5>
                            <div class="mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="5" required>{{ $product->description }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.all-products') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


