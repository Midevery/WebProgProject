<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kisora Shop')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --kisora-blue: #87CEEB;
            --kisora-light-blue: #E3F2FD;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .navbar-kisora {
            background-color: var(--kisora-light-blue);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            color: var(--kisora-blue) !important;
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .navbar-toggler {
            border: 1px solid rgba(0,0,0,0.1);
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%2833, 37, 41, 0.75%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        
        .search-bar {
            max-width: 400px;
            flex: 1;
        }
        
        .search-bar .form-control {
            border-radius: 25px;
            border: 1px solid #ddd;
            padding-left: 45px;
        }
        
        .search-bar .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 1;
        }
        
        .nav-icon {
            font-size: 1.3rem;
            color: #333;
            margin: 0 10px;
            text-decoration: none;
            position: relative;
        }
        
        .nav-icon:hover {
            color: var(--kisora-blue);
        }
        
        /* Responsive navbar */
        @media (max-width: 991px) {
            .navbar-collapse {
                margin-top: 1rem;
            }
            .search-bar {
                max-width: 100%;
                margin: 0.5rem 0;
            }
            .d-flex.align-items-center {
                flex-wrap: wrap;
            }
        }
        
        @media (max-width: 576px) {
            .nav-icon {
                margin: 0 5px;
                font-size: 1.1rem;
            }
            .btn-signin {
                padding: 6px 15px;
                font-size: 0.9rem;
            }
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-signin {
            background-color: var(--kisora-blue);
            color: white;
            border: none;
            border-radius: 20px;
            padding: 8px 20px;
        }
        
        .btn-signin:hover {
            background-color: #6BB6D6;
            color: white;
        }
        
        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 2rem;
            color: #333;
        }
        
        .category-card {
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s;
            text-decoration: none;
            color: inherit;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            color: inherit;
        }
        
        .category-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        
        .illustrator-card {
            text-align: center;
        }
        
        .illustrator-card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        
        .product-card {
            border: 1px solid #eee;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            position: relative;
        }
        
        .product-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-preorder {
            background-color: #FFA500;
            color: white;
        }
        
        .badge-ready {
            background-color: #28a745;
            color: white;
        }
        
        .badge-late {
            background-color: #dc3545;
            color: white;
        }
        
        .product-price {
            color: var(--kisora-blue);
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .trending-banner {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .footer-kisora {
            background-color: var(--kisora-light-blue);
            padding: 3rem 0 2rem;
            margin-top: 4rem;
        }
        
        .footer-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .footer-link {
            color: #666;
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .footer-link:hover {
            color: var(--kisora-blue);
        }
        
        .social-icon {
            font-size: 1.5rem;
            color: #333;
            margin-right: 15px;
            text-decoration: none;
        }
        
        .social-icon:hover {
            color: var(--kisora-blue);
        }
        
        .btn-see-all {
            background-color: var(--kisora-blue);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            margin: 2rem 0;
        }
        
        .btn-see-all:hover {
            background-color: #6BB6D6;
            color: white;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-kisora">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                Kisora
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <form method="GET" action="{{ route('products.index') }}" class="search-bar position-relative mx-3 my-2 my-lg-0">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="search" class="form-control" placeholder="Search our product" value="{{ request('search') }}">
                </form>
                
                <div class="d-flex align-items-center ms-auto">
                    @auth
                        @if(Auth::user()->isCustomer())
                            <a href="{{ route('wishlist.index') }}" class="nav-icon">
                                <i class="bi bi-heart"></i>
                            </a>
                            <a href="{{ route('cart.index') }}" class="nav-icon position-relative">
                                <i class="bi bi-cart3"></i>
                                <span class="cart-badge">{{ Auth::user()->carts()->count() }}</span>
                            </a>
                        @endif
                    @else
                        <a href="{{ route('wishlist.index') }}" class="nav-icon">
                            <i class="bi bi-heart"></i>
                        </a>
                        <a href="{{ route('cart.index') }}" class="nav-icon position-relative">
                            <i class="bi bi-cart3"></i>
                            <span class="cart-badge">0</span>
                        </a>
                    @endauth
                    @auth
                        <div class="dropdown ms-2">
                            <button class="btn btn-signin dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ Auth::user()->profile_image ? asset(Auth::user()->profile_image) : 'https://picsum.photos/40/40?random=' . Auth::id() }}" class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;" alt="Profile" onerror="this.src='https://picsum.photos/40/40?random={{ Auth::id() }}'">
                                <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if(Auth::user()->isAdmin())
                                    {{-- Admin Menu --}}
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="bi bi-person-circle me-2"></i>Admin Profile & Analytics</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.all-products') }}"><i class="bi bi-box-seam me-2"></i>All Products</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.add-product') }}"><i class="bi bi-plus-circle me-2"></i>Add Product</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.all-orders') }}"><i class="bi bi-receipt me-2"></i>All Orders</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.earning') }}"><i class="bi bi-cash-coin me-2"></i>Earning</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i class="bi bi-person me-2"></i>Edit Profile</a></li>
                                @elseif(Auth::user()->isArtist())
                                    {{-- Artist/Seller Menu --}}
                                    <li><a class="dropdown-item" href="{{ route('artist.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Artist Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('artist.profile') }}"><i class="bi bi-person-circle me-2"></i>Seller Profile & Analytics</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('products.index') }}"><i class="bi bi-box-seam me-2"></i>All Products</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i class="bi bi-person me-2"></i>Edit Profile</a></li>
                                @else
                                    {{-- Customer Menu --}}
                                    <li><a class="dropdown-item" href="{{ route('customer.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('products.index') }}"><i class="bi bi-box-seam me-2"></i>All Products</a></li>
                                    <li><a class="dropdown-item" href="{{ route('cart.index') }}"><i class="bi bi-cart3 me-2"></i>My Cart</a></li>
                                    <li><a class="dropdown-item" href="{{ route('wishlist.index') }}"><i class="bi bi-heart me-2"></i>Wishlist</a></li>
                                    <li><a class="dropdown-item" href="{{ route('shipping.index') }}"><i class="bi bi-box-seam me-2"></i>My Orders</a></li>
                                    <li><a class="dropdown-item" href="{{ route('shipping.index') }}"><i class="bi bi-truck me-2"></i>Track Order</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('signout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('signin') }}" class="btn btn-signin">Sign In</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @if(session('success') || session('error') || session('info'))
            <div class="container mt-3">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>
        @endif
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer-kisora">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Interact with Kisora</h5>
                    <div>
                        <a href="https://www.instagram.com/_kisoraa?igsh=MXUyeGNmZm5kbTF0dA%3D%3D&utm_source=qr" class="social-icon" target="_blank" rel="noopener">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="https://www.tiktok.com/@_kisoraa?_r=1&_t=ZS-91Y4mAMUggE" class="social-icon" target="_blank" rel="noopener">
                            <i class="bi bi-tiktok"></i>
                        </a>
                        <a href="https://x.com/_Kisoraa?s=20" class="social-icon" target="_blank" rel="noopener">
                            <i class="bi bi-twitter-x"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Give us your Idea!</h5>
                    <a href="#" class="footer-link">Feedback</a>
                    <a href="#" class="footer-link">Bug Issue</a>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Terms of Service</h5>
                    <a href="#" class="footer-link">Terms and Conditions</a>
                    <a href="#" class="footer-link">Privacy Policy</a>
                    <a href="#" class="footer-link">Return Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

