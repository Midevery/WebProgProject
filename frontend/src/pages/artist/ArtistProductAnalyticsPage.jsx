import { useEffect, useMemo, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../../api/client.js';

function ArtistProductAnalyticsPage() {
  const { productId } = useParams();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  const backendBaseUrl =
    import.meta.env.VITE_BACKEND_URL?.replace(/\/$/, '') || 'http://localhost:8000';

  const resolveImageUrl = useMemo(() => {
    return (path, fallback) => {
      if (!path) return fallback;
      if (path.startsWith('http://') || path.startsWith('https://')) {
        return path;
      }
      return `${backendBaseUrl}/${path.replace(/^\/+/, '')}`;
    };
  }, [backendBaseUrl]);

  useEffect(() => {
    async function load() {
      setLoading(true);
      try {
        const res = await api.get(`/artist/product/${productId}/analytics`);
        setData(res.data);
      } catch (err) {
        if (err.response?.status === 401) {
          navigate('/signin');
        } else {
          navigate('/artist/dashboard');
        }
      } finally {
        setLoading(false);
      }
    }
    load();
  }, [productId, navigate]);

  if (loading || !data) {
    return (
      <div className="container my-4">
        <p>Loading product analytics...</p>
      </div>
    );
  }

  const {
    product,
    totalSold,
    totalEarning,
    netEarning,
    totalOrders,
    avgOrderValue,
    conversionRate,
    salesByDate = [],
    salesByMonth = [],
    recentOrders = [],
    platformFee,
    totalPlatformFee,
    totalCost,
    productCost,
  } = data;

  return (
    <div className="container my-5">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1 className="section-title mb-0">
          Product Analytics: {product.name}
        </h1>
        <button
          type="button"
          className="btn btn-outline-primary"
          onClick={() => navigate('/artist/dashboard')}
        >
          <i className="bi bi-arrow-left me-2" />
          Back to Dashboard
        </button>
      </div>

      <div className="analytics-card mb-4">
        <div className="row">
          <div className="col-md-4 text-center">
            <img
              src={resolveImageUrl(
                product.image,
                `https://picsum.photos/400/400?random=${product.id}`,
              )}
              alt={product.name}
              className="product-image-large mb-3"
              onError={(e) => {
                e.currentTarget.src = `https://picsum.photos/400/400?random=${product.id}`;
              }}
            />
          </div>
          <div className="col-md-8">
            <h3>{product.name}</h3>
            <p className="text-muted">
              Category: {product.category?.name || 'N/A'}
            </p>
            <p className="text-muted">
              Price:{' '}
              <strong>
                IDR {Number(product.price || 0).toLocaleString('id-ID')}
              </strong>
            </p>
            <p className="text-muted">
              Stock:{' '}
              {product.stock > 10 ? (
                <span className="badge bg-success">
                  {product.stock} In Stock
                </span>
              ) : product.stock > 0 ? (
                <span className="badge bg-warning">
                  {product.stock} Low Stock
                </span>
              ) : (
                <span className="badge bg-danger">Out of Stock</span>
              )}
            </p>
            <p className="text-muted">
              Total Views: <strong>{product.clicks}</strong>
            </p>
            <p className="text-muted">
              Created:{' '}
              {new Date(product.created_at).toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
              })}
            </p>
          </div>
        </div>
      </div>

      <div className="row mb-4">
        <div className="col-md-3 mb-3">
          <div className="stat-card text-center">
            <div className="stat-value">{totalSold}</div>
            <div className="stat-label">Total Sold</div>
          </div>
        </div>
        <div className="col-md-3 mb-3">
          <div className="stat-card text-center">
            <div className="stat-value">
              IDR {Number(totalEarning || 0).toLocaleString('id-ID')}
            </div>
            <div className="stat-label">Gross Earning</div>
          </div>
        </div>
        <div className="col-md-3 mb-3">
          <div className="stat-card text-center">
            <div className="stat-value">{totalOrders}</div>
            <div className="stat-label">Total Orders</div>
          </div>
        </div>
        <div className="col-md-3 mb-3">
          <div className="stat-card text-center">
            <div className="stat-value">
              IDR {Number(netEarning || 0).toLocaleString('id-ID')}
            </div>
            <div className="stat-label">Net Earning</div>
          </div>
        </div>
      </div>

      <div className="row mb-4">
        <div className="col-md-12">
          <div className="analytics-card">
            <h5 className="mb-3">Earning Breakdown</h5>
            <div className="row">
              <div className="col-md-3 mb-2">
                <div className="p-3 bg-light rounded">
                  <div className="d-flex justify-content-between align-items-center">
                    <span>
                      <strong>Gross Earning:</strong>
                    </span>
                    <span className="text-primary">
                      <strong>
                        IDR {Number(totalEarning || 0).toLocaleString('id-ID')}
                      </strong>
                    </span>
                  </div>
                </div>
              </div>
              <div className="col-md-3 mb-2">
                <div className="p-3 bg-light rounded">
                  <div className="d-flex justify-content-between align-items-center">
                    <span>
                      <strong>
                        Cost ({Number(productCost || 0).toLocaleString(
                          'id-ID',
                        )}{' '}
                        x {totalSold}):
                      </strong>
                    </span>
                    <span className="text-warning">
                      <strong>
                        - IDR {Number(totalCost || 0).toLocaleString('id-ID')}
                      </strong>
                    </span>
                  </div>
                </div>
              </div>
              <div className="col-md-3 mb-2">
                <div className="p-3 bg-light rounded">
                  <div className="d-flex justify-content-between align-items-center">
                    <span>
                      <strong>
                        Platform Fee (
                        {Number(platformFee || 0).toLocaleString('id-ID')} x{' '}
                        {totalSold}):
                      </strong>
                    </span>
                    <span className="text-danger">
                      <strong>
                        - IDR{' '}
                        {Number(totalPlatformFee || 0).toLocaleString('id-ID')}
                      </strong>
                    </span>
                  </div>
                </div>
              </div>
              <div className="col-md-3 mb-2">
                <div className="p-3 bg-success bg-opacity-10 rounded">
                  <div className="d-flex justify-content-between align-items-center">
                    <span>
                      <strong>Net Earning (You Receive):</strong>
                    </span>
                    <span className="text-success">
                      <strong>
                        IDR {Number(netEarning || 0).toLocaleString('id-ID')}
                      </strong>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="row mb-4">
        <div className="col-md-6 mb-3">
          <div className="analytics-card">
            <h5 className="mb-3">Performance Metrics</h5>
            <table className="table-analytics">
              <tbody>
                <tr>
                  <td>
                    <strong>Average Order Value</strong>
                  </td>
                  <td>
                    IDR {Number(avgOrderValue || 0).toLocaleString('id-ID')}
                  </td>
                </tr>
                <tr>
                  <td>
                    <strong>Total Views</strong>
                  </td>
                  <td>{product.clicks}</td>
                </tr>
                <tr>
                  <td>
                    <strong>Conversion Rate</strong>
                  </td>
                  <td>{Number(conversionRate || 0).toFixed(2)}%</td>
                </tr>
                <tr>
                  <td>
                    <strong>Views per Sale</strong>
                  </td>
                  <td>
                    {totalOrders > 0
                      ? (product.clicks / totalOrders).toFixed(1)
                      : 'N/A'}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div className="col-md-6 mb-3">
          <div className="analytics-card">
            <h5 className="mb-3">Sales Summary</h5>
            <table className="table-analytics">
              <tbody>
                <tr>
                  <td>
                    <strong>Total Quantity Sold</strong>
                  </td>
                  <td>{totalSold} units</td>
                </tr>
                <tr>
                  <td>
                    <strong>Gross Revenue</strong>
                  </td>
                  <td>
                    IDR {Number(totalEarning || 0).toLocaleString('id-ID')}
                  </td>
                </tr>
                <tr>
                  <td>
                    <strong>Cost</strong>
                  </td>
                  <td className="text-warning">
                    - IDR {Number(totalCost || 0).toLocaleString('id-ID')}
                  </td>
                </tr>
                <tr>
                  <td>
                    <strong>Platform Fee</strong>
                  </td>
                  <td className="text-danger">
                    - IDR{' '}
                    {Number(totalPlatformFee || 0).toLocaleString('id-ID')}
                  </td>
                </tr>
                <tr>
                  <td>
                    <strong>Net Revenue (You Receive)</strong>
                  </td>
                  <td className="text-success">
                    <strong>
                      IDR {Number(netEarning || 0).toLocaleString('id-ID')}
                    </strong>
                  </td>
                </tr>
                <tr>
                  <td>
                    <strong>Total Orders</strong>
                  </td>
                  <td>{totalOrders} orders</td>
                </tr>
                <tr>
                  <td>
                    <strong>Average per Order</strong>
                  </td>
                  <td>
                    {totalOrders > 0
                      ? (totalSold / totalOrders).toFixed(1)
                      : '0'}{' '}
                    units
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {salesByDate.length > 0 && (
        <div className="analytics-card mb-4">
          <h5 className="mb-4">Sales Trend (Last 30 Days)</h5>
          <div className="table-responsive">
            <table className="table-analytics">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Quantity Sold</th>
                  <th>Earning</th>
                </tr>
              </thead>
              <tbody>
                {salesByDate.map((sale) => (
                  <tr key={sale.date}>
                    <td>
                      {new Date(sale.date).toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                      })}
                    </td>
                    <td>
                      <strong>{sale.quantity_sold}</strong>
                    </td>
                    <td className="text-success">
                      <strong>
                        IDR{' '}
                        {Number(sale.earning || 0).toLocaleString('id-ID')}
                      </strong>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {salesByMonth.length > 0 && (
        <div className="analytics-card mb-4">
          <h5 className="mb-4">Monthly Sales (Last 12 Months)</h5>
          <div className="table-responsive">
            <table className="table-analytics">
              <thead>
                <tr>
                  <th>Month</th>
                  <th>Quantity Sold</th>
                  <th>Earning</th>
                </tr>
              </thead>
              <tbody>
                {salesByMonth.map((sale) => (
                  <tr key={sale.month}>
                    <td>{sale.month}</td>
                    <td>
                      <strong>{sale.quantity_sold}</strong>
                    </td>
                    <td className="text-success">
                      <strong>
                        IDR{' '}
                        {Number(sale.earning || 0).toLocaleString('id-ID')}
                      </strong>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      <div className="analytics-card mb-4">
        <h5 className="mb-4">Recent Orders</h5>
        {recentOrders.length === 0 ? (
          <div className="text-center py-5">
            <p className="text-muted mb-0">
              No orders yet for this product.
            </p>
          </div>
        ) : (
          <div className="table-responsive">
            <table className="table-analytics">
              <thead>
                <tr>
                  <th>Order Number</th>
                  <th>Customer</th>
                  <th>Quantity</th>
                  <th>Subtotal</th>
                  <th>Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                {recentOrders.map((item) => (
                  <tr key={item.id}>
                    <td>{item.order?.order_number || 'N/A'}</td>
                    <td>{item.order?.user?.name || 'N/A'}</td>
                    <td>
                      <strong>{item.quantity}</strong>
                    </td>
                    <td className="text-success">
                      <strong>
                        IDR {Number(item.subtotal || 0).toLocaleString('id-ID')}
                      </strong>
                    </td>
                    <td>
                      {new Date(item.created_at).toLocaleString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                      })}
                    </td>
                    <td>
                      <span
                        className={`badge bg-${
                          item.order?.status === 'delivered'
                            ? 'success'
                            : item.order?.status === 'shipped'
                              ? 'info'
                              : 'warning'
                        }`}
                      >
                        {item.order?.status
                          ? item.order.status.charAt(0).toUpperCase() +
                            item.order.status.slice(1)
                          : 'N/A'}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}

export default ArtistProductAnalyticsPage;
