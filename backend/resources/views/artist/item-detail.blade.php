@extends('layouts.app')

@section('title', 'Product Detail - Artist Dashboard - Kisora Shop')

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
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    .product-image-large {
        width: 100%;
        max-width: 400px;
        height: auto;
        border-radius: 10px;
    }
    
    .chart-container {
        width: 100%;
        height: 300px;
        margin: 0 auto;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title mb-0">Product Detail</h1>
        <a href="{{ route('artist.dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <div class="row mb-4">
        <!-- Product Image -->
        <div class="col-md-4">
            <div class="dashboard-card text-center">
                <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="product-image-large" onerror="this.src='https://via.placeholder.com/400x400?text=No+Image'">
            </div>
        </div>

        <!-- Product Information -->
        <div class="col-md-8">
            <div class="dashboard-card">
                @if($product->stock > 0)
                    <span class="badge bg-success mb-2">Ready Stock</span>
                @else
                    <span class="badge bg-warning mb-2">Pre-Order</span>
                @endif
                
                <h3 class="mb-3">{{ $product->name }}</h3>
                
                <div class="mb-3">
                    <p class="mb-1"><strong>Category:</strong> {{ $product->category->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Price:</strong> <span class="text-primary fw-bold">IDR {{ number_format($product->price, 0, ',', '.') }}</span></p>
                    <p class="mb-1"><strong>Stock:</strong> {{ $product->stock }}</p>
                </div>

                <div class="mb-3">
                    <h5>Description</h5>
                    <p>{{ $product->description }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Analytics -->
    <div class="dashboard-card">
        <h4 class="mb-4">Product Analytics</h4>
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value">{{ $totalSold }}</div>
                    <div class="stat-label">Total Sold</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value">{{ $totalBuyers }}</div>
                    <div class="stat-label">Total Buyers</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value">IDR {{ number_format($totalEarning, 0, ',', '.') }}</div>
                    <div class="stat-label">Total Earning</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value">{{ $product->clicks }}</div>
                    <div class="stat-label">Total Views</div>
                </div>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="mt-4">
            <h5 class="mb-3">Sales Over Time (Last 7 Days)</h5>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="dashboard-card">
        <h4 class="mb-4">Comments</h4>
        @if($product->comments->count() > 0)
            @foreach($product->comments->take(5) as $comment)
            <div class="d-flex align-items-start mb-3">
                <img src="https://picsum.photos/40/40?random={{ $comment->user_id }}" class="rounded-circle me-3" alt="User">
                <div>
                    <strong>{{ $comment->user->name }}</strong>
                    <p class="mb-0">{{ $comment->content }}</p>
                    <small class="text-muted">{{ $comment->created_at->format('d M Y') }}</small>
                </div>
            </div>
            @endforeach
            @if($product->comments->count() > 5)
            <a href="#" class="text-decoration-none">Lainnya ></a>
            @endif
        @else
            <p class="text-muted">No comments yet</p>
        @endif
    </div>
</div>

<script>
// Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
const salesData = @json($salesOverTime);
const salesLabels = salesData.map(item => new Date(item.date).toLocaleDateString('en-US', { weekday: 'short' }));
const salesValues = salesData.map(item => parseInt(item.sold || 0));
const earningValues = salesData.map(item => parseFloat(item.earning || 0));

new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: salesLabels,
        datasets: [{
            label: 'Items Sold',
            data: salesValues,
            borderColor: '#87CEEB',
            backgroundColor: 'rgba(135, 206, 235, 0.1)',
            tension: 0.4,
            yAxisID: 'y'
        }, {
            label: 'Earning (IDR)',
            data: earningValues,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                beginAtZero: true
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true,
                grid: {
                    drawOnChartArea: false
                }
            }
        }
    }
});
</script>
@endsection

