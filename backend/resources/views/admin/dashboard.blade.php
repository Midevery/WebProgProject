@extends('layouts.app')

@section('title', 'Admin Dashboard - Kisora Shop')

@push('styles')
<style>
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
    
    .chart-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .chart-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    .chart-container {
        width: 100%;
        max-width: 500px;
        height: 500px;
        margin: 0 auto;
        position: relative;
    }
    
    .chart-wrapper {
        width: 100%;
        height: 100%;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title mb-0">Admin Dashboard</h1>
        <div>
            <a href="{{ route('admin.all-products') }}" class="btn btn-outline-primary me-2">All Products</a>
            <a href="{{ route('admin.all-orders') }}" class="btn btn-outline-primary me-2">All Orders</a>
            <a href="{{ route('admin.earning') }}" class="btn btn-outline-primary">Earning</a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($totalOrders) }}</div>
                <div class="stat-label">Total Order</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($totalUsers) }}</div>
                <div class="stat-label">Total User</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($totalVisitors) }}</div>
                <div class="stat-label">Total Visitor</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">IDR {{ number_format($totalSales, 0, ',', '.') }}</div>
                <div class="stat-label">Total Sales</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Revenue Growth Chart -->
        <div class="col-md-6">
            <div class="chart-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="chart-title mb-0">Revenue Growth</h5>
                    <select class="form-select form-select-sm" style="width: auto;">
                        <option>Weekly</option>
                        <option>Monthly</option>
                    </select>
                </div>
                <div class="chart-container">
                    <div class="chart-wrapper">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Status Summary -->
        <div class="col-md-6">
            <div class="chart-card">
                <h5 class="chart-title">Order Status</h5>
                <div class="chart-container">
                    <div class="chart-wrapper">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Daily Income Chart -->
        <div class="col-md-6">
            <div class="chart-card">
                <h5 class="chart-title">Daily Income</h5>
                <div class="chart-container">
                    <div class="chart-wrapper">
                        <canvas id="dailyIncomeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Earning Categories -->
        <div class="col-md-6">
            <div class="chart-card">
                <h5 class="chart-title">Top Earning Category</h5>
                <div class="chart-container">
                    <div class="chart-wrapper">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="chart-card">
                <h5 class="chart-title">Recent Orders</h5>
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
                            @forelse($recentOrders as $order)
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
                                <td colspan="5" class="text-center">No orders yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Revenue Growth Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueData = @json($revenueGrowth);
const revenueLabels = revenueData.map(item => new Date(item.date).toLocaleDateString('en-US', { weekday: 'short' }));
const revenueValues = revenueData.map(item => parseFloat(item.revenue));

new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: revenueLabels,
        datasets: [{
            label: 'Revenue',
            data: revenueValues,
            borderColor: '#87CEEB',
            backgroundColor: 'rgba(135, 206, 235, 0.1)',
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

// Daily Income Chart
const dailyCtx = document.getElementById('dailyIncomeChart').getContext('2d');
const dailyData = @json($dailyIncome);
const dailyLabels = dailyData.map(item => new Date(item.date).toLocaleDateString('en-US', { weekday: 'short' }));
const dailyValues = dailyData.map(item => parseFloat(item.income));

new Chart(dailyCtx, {
    type: 'bar',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Income',
            data: dailyValues,
            backgroundColor: '#87CEEB'
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

// Order Status Chart
const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
const statusData = @json($orderStatusSummary);
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(statusData),
        datasets: [{
            data: Object.values(statusData),
            backgroundColor: ['#87CEEB', '#6BB6D6', '#4A9BCF', '#28a745', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Category Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryData = @json($topCategories);
const categoryLabels = categoryData.map(item => item.name);
const categoryValues = categoryData.map(item => parseFloat(item.total));

new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: categoryLabels,
        datasets: [{
            data: categoryValues,
            backgroundColor: ['#87CEEB', '#6BB6D6', '#4A9BCF', '#28a745', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>
@endsection

