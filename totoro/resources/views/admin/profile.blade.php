@extends('layouts.app')

@section('title', 'Admin Profile - Kisora Shop')

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
</style>
@endpush

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title mb-0">Admin Profile</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Profile Information -->
    <div class="profile-card">
        <div class="row">
            <div class="col-md-3 text-center">
                <img src="{{ $admin->profile_image ? asset($admin->profile_image) : 'https://picsum.photos/150/150?random=' . $admin->id }}" alt="{{ $admin->name }}" class="profile-image mb-3" onerror="this.src='https://picsum.photos/150/150?random={{ $admin->id }}'">
                <h4>{{ $admin->name }}</h4>
                <p class="text-muted">{{ $admin->email }}</p>
                <span class="badge bg-primary">Admin</span>
            </div>
            <div class="col-md-9">
                <h5 class="mb-3">Profile Information</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Username:</strong>
                        <p>{{ $admin->username }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong>
                        <p>{{ $admin->email }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Phone:</strong>
                        <p>{{ $admin->phone ?? 'Not set' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Role:</strong>
                        <p><span class="badge bg-primary">Admin</span></p>
                    </div>
                </div>
                <a href="{{ route('profile.index') }}" class="btn btn-primary">Edit Profile</a>
            </div>
        </div>
    </div>

    <!-- Analytics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($totalProductsSold) }}</div>
                <div class="stat-label">Total Products Sold</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">IDR {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($totalOrders) }}</div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($totalUsers) }}</div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($totalArtists) }}</div>
                <div class="stat-label">Total Artists</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($totalProducts) }}</div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="profile-card">
        <h5 class="mb-4">Recent Sales</h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSales as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>IDR {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td>{{ $order->created_at->format('d M, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No sales yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


