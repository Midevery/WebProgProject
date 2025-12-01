@extends('layouts.app')

@section('title', 'Seller Profile & Analytics - Kisora Shop')

@push('styles')
<style>
    .dashboard-card {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stat-card {
        background: linear-gradient(135deg, #87CEEB 0%, #6BB6D6 100%);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .stat-value {
        font-size: 1.8rem;
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
    }
    
    .chart-container {
        width: 100%;
        height: 400px;
        margin: 0 auto;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title mb-0">Seller Profile</h1>
        <a href="{{ route('artist.dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- My Profile Section -->
    <div class="dashboard-card">
        <h3 class="mb-4">My Profile</h3>
        <div class="row">
            <div class="col-md-3 text-center">
                <img src="https://picsum.photos/150/150?random={{ $artist->id }}" alt="{{ $artist->name }}" class="profile-image mb-3">
            </div>
            <div class="col-md-9">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="{{ $artist->username }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $artist->name }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ $artist->email }}" readonly>
                            <a href="#" class="small">Change Email</a>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ $artist->phone ?? '' }}">
                            <a href="#" class="small">Change Phone Number</a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Shop Name</label>
                            <input type="text" name="address" class="form-control" value="{{ $artist->address ?? '' }}" placeholder="My Shop Name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <div>
                                <input type="radio" name="gender" value="Male" {{ $artist->gender === 'Male' ? 'checked' : '' }}> Male
                                <input type="radio" name="gender" value="Female" {{ $artist->gender === 'Female' ? 'checked' : '' }} class="ms-3"> Female
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ $artist->date_of_birth ? $artist->date_of_birth->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{ route('profile.password') }}" class="btn btn-outline-secondary">Change Password</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Shop Analytics Section -->
    <div class="dashboard-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-0">Your Shop Analytic</h3>
                <small class="text-muted">Periode Data Real-time: Hari Ini - Pk {{ now()->format('H:i') }} (GMT+07)</small>
            </div>
            <div>
                <select class="form-select form-select-sm d-inline-block" style="width: auto;">
                    <option>Kriteria Utama</option>
                </select>
                <select class="form-select form-select-sm d-inline-block ms-2" style="width: auto;">
                    <option>Status Pesanan</option>
                </select>
                <a href="#" class="btn btn-sm btn-outline-primary ms-2">Download Data</a>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
                    <div class="stat-label">Penjualan (Sales)</div>
                    <small class="opacity-75">vs Kemarin pada 00:00-{{ now()->format('H:i') }}</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value">{{ $totalOrders }}</div>
                    <div class="stat-label">Pesanan (Orders)</div>
                    <small class="opacity-75">vs Kemarin pada 00:00-{{ now()->format('H:i') }}</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value">{{ number_format($conversionRate, 2) }}%</div>
                    <div class="stat-label">Tingkat Konversi (Conversion Rate)</div>
                    <small class="opacity-75">vs Kemarin pada 00:00-{{ now()->format('H:i') }}</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value">{{ $totalVisitors }}</div>
                    <div class="stat-label">Total Pengunjung (Total Visitors)</div>
                    <small class="opacity-75">vs Kemarin pada 00:00-{{ now()->format('H:i') }}</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value">{{ $productsViewed }}</div>
                    <div class="stat-label">Produk Dilihat (Products Viewed)</div>
                    <small class="opacity-75">vs Kemarin pada 00:00-{{ now()->format('H:i') }}</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value">Rp {{ number_format($salesPerOrder, 0, ',', '.') }}</div>
                    <div class="stat-label">Penjualan per Pesanan (Sales per Order)</div>
                    <small class="opacity-75">vs Kemarin pada 00:00-{{ now()->format('H:i') }}</small>
                </div>
            </div>
        </div>

        <!-- Analytics Chart -->
        <div class="mt-4">
            <h5 class="mb-3">Grafik setiap Kriteria</h5>
            <div class="chart-container">
                <canvas id="analyticsChart"></canvas>
            </div>
            <p class="text-muted mt-2">Kriteria Dipilih 2/4</p>
        </div>

        <div class="mt-4 text-end">
            <a href="{{ route('artist.show', $artist->id) }}" class="btn btn-primary">View Shop</a>
        </div>
    </div>
</div>

<script>
// Analytics Chart
const analyticsCtx = document.getElementById('analyticsChart').getContext('2d');
const analyticsData = @json($analyticsData);
const analyticsLabels = analyticsData.map(item => new Date(item.date).toLocaleDateString('en-US', { weekday: 'short' }));
const salesData = analyticsData.map(item => parseFloat(item.sales || 0));
const visitorsData = analyticsData.map(item => parseInt(item.visitors || 0));

new Chart(analyticsCtx, {
    type: 'line',
    data: {
        labels: analyticsLabels,
        datasets: [{
            label: 'Penjualan',
            data: salesData,
            borderColor: '#87CEEB',
            backgroundColor: 'rgba(135, 206, 235, 0.1)',
            tension: 0.4
        }, {
            label: 'Total Pengunjung',
            data: visitorsData,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endsection

