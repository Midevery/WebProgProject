import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Tooltip,
  Legend,
} from 'chart.js';
import { Line, Bar, Doughnut } from 'react-chartjs-2';
import { api } from '../../api/client.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Tooltip,
  Legend,
);

function AdminDashboardPage() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    async function load() {
      setLoading(true);
      try {
        const res = await api.get('/admin/dashboard');
        setData(res.data);
      } finally {
        setLoading(false);
      }
    }
    load();
  }, []);

  if (loading || !data) {
    return (
      <div className="container my-5">
        <p>Loading admin dashboard...</p>
      </div>
    );
  }

  const {
    totalOrders,
    totalUsers,
    totalVisitors,
    totalSales,
    revenueGrowth = [],
    dailyIncome = [],
    orderStatusSummary = {},
    topCategories = [],
    recentOrders = [],
  } = data;

  const orderStatusEntries = Object.entries(orderStatusSummary);
  const categoryColors = ['#87ceeb', '#6bb6d6', '#4a9bcf', '#28a745', '#dc3545'];

  const revenueChartData = {
    labels: revenueGrowth.map((item) =>
      new Date(item.date).toLocaleDateString('en-US', { weekday: 'short' }),
    ),
    datasets: [
      {
        label: 'Revenue',
        data: revenueGrowth.map((item) => Number(item.revenue || 0)),
        borderColor: '#87ceeb',
        backgroundColor: 'rgba(135, 206, 235, 0.15)',
        tension: 0.4,
        fill: true,
      },
    ],
  };

  const dailyIncomeData = {
    labels: dailyIncome.map((item) =>
      new Date(item.date).toLocaleDateString('en-US', { weekday: 'short' }),
    ),
    datasets: [
      {
        label: 'Income',
        data: dailyIncome.map((item) => Number(item.income || 0)),
        backgroundColor: '#87ceeb',
      },
    ],
  };

  const orderStatusData = {
    labels: orderStatusEntries.map(
      ([status]) => status.charAt(0).toUpperCase() + status.slice(1),
    ),
    datasets: [
      {
        data: orderStatusEntries.map(([, count]) => Number(count || 0)),
        backgroundColor: ['#87ceeb', '#ffc107', '#28a745', '#6bb6d6', '#dc3545'],
      },
    ],
  };

  const topCategoriesData = {
    labels: topCategories.map((cat) => cat.name),
    datasets: [
      {
        data: topCategories.map((cat) => Number(cat.total || 0)),
        backgroundColor: topCategories.map(
          (_, idx) => categoryColors[idx % categoryColors.length],
        ),
      },
    ],
  };

  const currencyFormatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  });

  return (
    <div className="container my-5">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1 className="section-title mb-0">Admin Dashboard</h1>
        <div>
          <Link to="/admin/products" className="btn btn-outline-primary me-2">
            All Products
          </Link>
          <Link to="/admin/orders" className="btn btn-outline-primary me-2">
            All Orders
          </Link>
          <Link to="/admin/earning" className="btn btn-outline-primary">
            Earning
          </Link>
        </div>
      </div>

      <div className="row mb-4">
        <div className="col-md-3 mb-3">
          <div className="stat-card">
            <div className="stat-value">
              {Number(totalOrders || 0).toLocaleString('id-ID')}
            </div>
            <div className="stat-label">Total Order</div>
          </div>
        </div>
        <div className="col-md-3 mb-3">
          <div className="stat-card">
            <div className="stat-value">
              {Number(totalUsers || 0).toLocaleString('id-ID')}
            </div>
            <div className="stat-label">Total User</div>
          </div>
        </div>
        <div className="col-md-3 mb-3">
          <div className="stat-card">
            <div className="stat-value">
              {Number(totalVisitors || 0).toLocaleString('id-ID')}
            </div>
            <div className="stat-label">Total Visitor</div>
          </div>
        </div>
        <div className="col-md-3 mb-3">
          <div className="stat-card">
            <div className="stat-value">
              IDR {Number(totalSales || 0).toLocaleString('id-ID')}
            </div>
            <div className="stat-label">Total Sales</div>
          </div>
        </div>
      </div>

      <div className="row">
        <div className="col-md-6">
          <div className="chart-card">
            <div className="d-flex justify-content-between align-items-center mb-3">
              <h5 className="chart-title mb-0">Revenue Growth</h5>
              <select className="form-select form-select-sm" style={{ width: 'auto' }}>
                <option>Weekly</option>
                <option>Monthly</option>
              </select>
            </div>
            <div style={{ height: 260 }}>
              <Line
                data={revenueChartData}
                options={{
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: {
                      callbacks: {
                        label: (context) =>
                          `${context.dataset.label}: ${currencyFormatter.format(
                            context.raw || 0,
                          )}`,
                      },
                    },
                  },
                  scales: {
                    y: {
                      beginAtZero: true,
                      ticks: {
                        callback: (value) => currencyFormatter.format(value),
                      },
                    },
                  },
                }}
              />
            </div>
          </div>
        </div>

        <div className="col-md-6">
          <div className="chart-card">
            <h5 className="chart-title">Order Status</h5>
            <div style={{ height: 260 }}>
              <Doughnut
                data={orderStatusData}
                options={{
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                    legend: {
                      display: true,
                      position: 'right',
                      labels: { boxWidth: 12 },
                    },
                  },
                }}
              />
            </div>
          </div>
        </div>
      </div>

      <div className="row">
        <div className="col-md-6">
          <div className="chart-card">
            <h5 className="chart-title">Daily Income</h5>
            <div style={{ height: 260 }}>
              <Bar
                data={dailyIncomeData}
                options={{
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: {
                      callbacks: {
                        label: (context) =>
                          `${context.dataset.label}: ${currencyFormatter.format(
                            context.raw || 0,
                          )}`,
                      },
                    },
                  },
                  scales: {
                    y: {
                      beginAtZero: true,
                      ticks: {
                        callback: (value) => currencyFormatter.format(value),
                      },
                    },
                  },
                }}
              />
            </div>
          </div>
        </div>

        <div className="col-md-6">
          <div className="chart-card">
            <h5 className="chart-title">Top Earning Category</h5>
            <div style={{ height: 260 }}>
              <Doughnut
                data={topCategoriesData}
                options={{
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                    legend: {
                      display: true,
                      position: 'right',
                      labels: { boxWidth: 12 },
                    },
                    tooltip: {
                      callbacks: {
                        label: (context) =>
                          `${context.label}: ${currencyFormatter.format(
                            context.raw || 0,
                          )}`,
                      },
                    },
                  },
                }}
              />
            </div>
          </div>
        </div>
      </div>

      <div className="dashboard-card">
        <h5 className="mb-3">Recent Orders</h5>
        <div className="table-responsive">
          <table className="table table-hover">
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
              {recentOrders.length === 0 && (
                <tr>
                  <td colSpan={5} className="text-center">
                    No orders yet
                  </td>
                </tr>
              )}
              {recentOrders.map((order) => (
                <tr key={order.id}>
                  <td>{order.order_number}</td>
                  <td>{order.user?.name}</td>
                  <td>
                    IDR{' '}
                    {Number(order.total_amount || 0).toLocaleString('id-ID')}
                  </td>
                  <td>
                    <span
                      className={`badge bg-${
                        order.status === 'delivered'
                          ? 'success'
                          : order.status === 'cancelled'
                            ? 'danger'
                            : 'warning'
                      }`}
                    >
                      {order.status
                        ? order.status.charAt(0).toUpperCase() +
                          order.status.slice(1)
                        : ''}
                    </span>
                  </td>
                  <td>
                    {new Date(order.created_at).toLocaleDateString('en-GB', {
                      day: '2-digit',
                      month: 'short',
                      year: 'numeric',
                    })}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

export default AdminDashboardPage;