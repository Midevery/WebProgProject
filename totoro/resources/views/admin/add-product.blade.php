@extends('layouts.app')

@section('title', 'Add Product - Admin - Kisora Shop')

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title mb-0">Add Product</h1>
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

                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <form action="{{ route('admin.store-product') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div id="product-info" class="mb-4">
                            <h5 class="mb-3">Product Information</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Illustrator Name <span class="text-danger">*</span></label>
                                <select name="artist_id" class="form-select" required>
                                    <option value="">Select Illustrator</option>
                                    @foreach($artists as $artist)
                                    <option value="{{ $artist->id }}">{{ $artist->name }} ({{ $artist->username }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Product Title <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Enter product title" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="upload-media" class="mb-4">
                            <h5 class="mb-3">Upload Media</h5>
                            <div class="mb-3">
                                <label class="form-label">Product Image <span class="text-danger">*</span></label>
                                <input type="file" name="image" class="form-control" accept="image/*" required>
                                <small class="text-muted">Upload product image (JPEG, PNG, JPG, GIF - Max 2MB)</small>
                                <div id="imagePreview" class="mt-3" style="display: none;">
                                    <img id="previewImg" src="" alt="Preview" style="max-width: 300px; border-radius: 5px; border: 1px solid #ddd;">
                                </div>
                            </div>
                        </div>

                        <div id="pricing" class="mb-4">
                            <h5 class="mb-3">Pricing & Inventory</h5>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Price (IDR) <span class="text-danger">*</span></label>
                                    <input type="number" name="price" class="form-control" placeholder="0" min="0" step="0.01" required>
                                    <small class="text-muted">Selling price to customer</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cost/Modal (IDR) <span class="text-danger">*</span></label>
                                    <input type="number" name="cost" class="form-control" placeholder="0" min="0" step="0.01" required>
                                    <small class="text-muted">Product cost/modal</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Stock <span class="text-danger">*</span></label>
                                    <input type="number" name="stock" class="form-control" placeholder="0" min="0" required>
                                </div>
                            </div>
                        </div>

                        <div id="description" class="mb-4">
                            <h5 class="mb-3">Product Description</h5>
                            <div class="mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="5" placeholder="Enter product description" required></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.all-products') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Add Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelector('input[name="image"]').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            document.getElementById('imagePreview').style.display = 'none';
        }
    });
</script>
@endpush
@endsection


