import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../../api/client.js';
import { resolveImageUrl } from '../../api/media.js';

function SellerProductAnalyticsPage() {
  const { productId } = useParams();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    async function load() {
      setLoading(true);
      try {
        const res = await api.get(`/seller/product/${productId}/analytics`);
        setData(res.data);
      } catch (err) {
        if (err.response?.status === 401) {
          navigate('/signin');
        } else {
          navigate('/seller/dashboard');
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
          onClick={() => navigate('/seller/dashboard')}
        >
          <i className="bi bi-arrow-left me-2" />
          Back to Dashboard
        </button>
      </div>

      <div className="card mb-4" style={{ padding: '1.5rem' }}>
        <div className="row g-4">
          <div className="col-md-4 text-center">
            <img
              src={resolveImageUrl(
                product.image,
                `https://picsum.photos/400/400?random=${product.id}`,
              )}
              alt={product.name}
              style={{
                width: '100%',
                maxWidth: '300px',
                height: 'auto',
                borderRadius: '8px',
                objectFit: 'cover',
              }}
              onError={(e) => {
                e.currentTarget.src = `https://picsum.photos/400/400?random=${product.id}`;
              }}
            />
          </div>
          <div className="col-md-8">
            <h3 className="mb-3">{product.name}</h3>
            <div className="mb-2">
              <strong>Category:</strong> {product.category?.name || 'N/A'}
            </div>
            <div className="mb-2">
              <strong>Price:</strong>{' '}
              <span className="text-primary">
                IDR {Number(product.price || 0).toLocaleString('id-ID')}
              </span>
            </div>
            <div className="mb-2">
              <strong>Stock:</strong>{' '}
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
            </div>
            <div className="mb-2">
              <strong>Total Views:</strong> <strong>{product.clicks}</strong>
            </div>
            <div className="mb-2">
              <strong>Created:</strong>{' '}
              {new Date(product.created_at).toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
              })}
            </div>
          </div>
        </div>
      </div>

      <div className="row g-3 mb-4">
        <div className="col-md-3">
          <div className="stat-card text-center h-100" style={{ padding: '1.5rem' }}>
            <div className="stat-value" style={{ fontSize: '2rem', marginBottom: '0.5rem' }}>
              {totalSold}
            </div>
            <div className="stat-label">Total Sold</div>
          </div>
        </div>
        <div className="col-md-3">
          <div className="stat-card text-center h-100" style={{ padding: '1.5rem' }}>
            <div className="stat-value" style={{ fontSize: '1.5rem', marginBottom: '0.5rem' }}>
              IDR {Number(totalEarning || 0).toLocaleString('id-ID')}
            </div>
            <div className="stat-label">Gross Earning</div>
          </div>
        </div>
        <div className="col-md-3">
          <div className="stat-card text-center h-100" style={{ padding: '1.5rem' }}>
            <div className="stat-value" style={{ fontSize: '2rem', marginBottom: '0.5rem' }}>
              {totalOrders}
            </div>
            <div className="stat-label">Total Orders</div>
          </div>
        </div>
        <div className="col-md-3">
          <div className="stat-card text-center h-100" style={{ padding: '1.5rem' }}>
            <div className="stat-value" style={{ fontSize: '1.5rem', marginBottom: '0.5rem' }}>
              IDR {Number(netEarning || 0).toLocaleString('id-ID')}
            </div>
            <div className="stat-label">Net Earning</div>
          </div>
        </div>
      </div>

      <div className="card mb-4" style={{ padding: '1.5rem' }}>
        <h5 className="mb-4">Earning Breakdown</h5>
        <div className="row g-3">
          <div className="col-md-3">
            <div className="p-3 bg-light rounded">
              <div className="mb-2">
                <strong>Gross Earning:</strong>
              </div>
              <div className="text-primary" style={{ fontSize: '1.1rem' }}>
                <strong>
                  IDR {Number(totalEarning || 0).toLocaleString('id-ID')}
                </strong>
              </div>
            </div>
          </div>
          <div className="col-md-3">
            <div className="p-3 bg-light rounded">
              <div className="mb-2">
                <strong>
                  Cost ({Number(productCost || 0).toLocaleString('id-ID')} x{' '}
                  {totalSold}):
                </strong>
              </div>
              <div className="text-warning" style={{ fontSize: '1.1rem' }}>
                <strong>
                  - IDR {Number(totalCost || 0).toLocaleString('id-ID')}
                </strong>
              </div>
            </div>
          </div>
          <div className="col-md-3">
            <div className="p-3 bg-light rounded">
              <div className="mb-2">
                <strong>
                  Platform Fee ({Number(platformFee || 0).toLocaleString('id-ID')} x{' '}
                  {totalSold}):
                </strong>
              </div>
              <div className="text-danger" style={{ fontSize: '1.1rem' }}>
                <strong>
                  - IDR{' '}
                  {Number(totalPlatformFee || 0).toLocaleString('id-ID')}
                </strong>
              </div>
            </div>
          </div>
          <div className="col-md-3">
            <div className="p-3 bg-success bg-opacity-10 rounded">
              <div className="mb-2">
                <strong>Net Earning (You Receive):</strong>
              </div>
              <div className="text-success" style={{ fontSize: '1.1rem' }}>
                <strong>
                  IDR {Number(netEarning || 0).toLocaleString('id-ID')}
                </strong>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="row g-3 mb-4">
        <div className="col-md-6">
          <div className="card" style={{ padding: '1.5rem', height: '100%' }}>
            <h5 className="mb-3">Performance Metrics</h5>
            <table className="table">
              <tbody>
                <tr>
                  <td style={{ padding: '0.75rem' }}>
                    <strong>Average Order Value</strong>
                  </td>
                  <td style={{ padding: '0.75rem' }}>
                    IDR {Number(avgOrderValue || 0).toLocaleString('id-ID')}
                  </td>
                </tr>
                <tr>
                  <td style={{ padding: '0.75rem' }}>
                    <strong>Total Views</strong>
                  </td>
                  <td style={{ padding: '0.75rem' }}>{product.clicks}</td>
                </tr>
                <tr>
                  <td style={{ padding: '0.75rem' }}>
                    <strong>Conversion Rate</strong>
                  </td>
                  <td style={{ padding: '0.75rem' }}>
                    {Number(conversionRate || 0).toFixed(2)}%
                  </td>
                </tr>
                <tr>
                  <td style={{ padding: '0.75rem' }}>
                    <strong>Views per Sale</strong>
                  </td>
                  <td style={{ padding: '0.75rem' }}>
                    {totalOrders > 0
                      ? (product.clicks / totalOrders).toFixed(1)
                      : 'N/A'}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div className="col-md-6">
          <div className="card" style={{ padding: '1.5rem', height: '100%' }}>
            <h5 className="mb-3">Sales Summary</h5>
            <table className="table">
              <tbody>
                <tr>
                  <td style={{ padding: '0.75rem' }}>
                    <strong>Total Quantity Sold</strong>
                  </td>
                  <td style={{ padding: '0.75rem' }}>{totalSold} units</td>
                </tr>
                <tr>
                  <td style={{ padding: '0.75rem' }}>
                    <strong>Gross Revenue</strong>
                  </td>
                  <td style={{ padding: '0.75rem' }}>
                    IDR {Number(totalEarning || 0).toLocaleString('id-ID')}
                  </td>
                </tr>
                <tr>
                  <td style={{ padding: '0.75rem' }}>
                    <strong>Cost</strong>
                  </td>
                  <td className="text-warning" style={{ padding: '0.75rem' }}>
                    - IDR {Number(totalCost || 0).toLocaleString('id-ID')}
                  </td>
                </tr>
                <tr>
                  <td style={{ padding: '0.75rem' }}>
                    <strong>Platform Fee</strong>
                  </td>
                  <td className="text-danger" style={{ padding: '0.75rem' }}>
                    - IDR{' '}
                    {Number(totalPlatformFee || 0).toLocaleString('id-ID')}
                  </td>
                </tr>
                <tr>
                  <td style={{ padding: '0.75rem' }}>
                    <strong>Net Revenue (You Receive)</strong>
                  </td>
                  <td className="text-success" style={{ padding: '0.75rem' }}>
                    <strong>
                      IDR {Number(netEarning || 0).toLocaleString('id-ID')}
                    </strong>
                  </td>
                </tr>
                <tr>
                  <td style={{ padding: '0.75rem' }}>
                    <strong>Total Orders</strong>
                  </td>
                  <td style={{ padding: '0.75rem' }}>{totalOrders} orders</td>
                </tr>
                <tr>
                  <td style={{ padding: '0.75rem' }}>
                    <strong>Average per Order</strong>
                  </td>
                  <td style={{ padding: '0.75rem' }}>
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
        <div className="card mb-4" style={{ padding: '1.5rem' }}>
          <h5 className="mb-4">Sales Trend (Last 30 Days)</h5>
          <div className="table-responsive">
            <table className="table table-hover">
              <thead>
                <tr>
                  <th style={{ padding: '0.75rem' }}>Date</th>
                  <th style={{ padding: '0.75rem' }}>Quantity Sold</th>
                  <th style={{ padding: '0.75rem' }}>Earning</th>
                </tr>
              </thead>
              <tbody>
                {salesByDate.map((sale) => (
                  <tr key={sale.date}>
                    <td style={{ padding: '0.75rem' }}>
                      {new Date(sale.date).toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                      })}
                    </td>
                    <td style={{ padding: '0.75rem' }}>
                      <strong>{sale.quantity_sold}</strong>
                    </td>
                    <td className="text-success" style={{ padding: '0.75rem' }}>
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
        <div className="card mb-4" style={{ padding: '1.5rem' }}>
          <h5 className="mb-4">Monthly Sales (Last 12 Months)</h5>
          <div className="table-responsive">
            <table className="table table-hover">
              <thead>
                <tr>
                  <th style={{ padding: '0.75rem' }}>Month</th>
                  <th style={{ padding: '0.75rem' }}>Quantity Sold</th>
                  <th style={{ padding: '0.75rem' }}>Earning</th>
                </tr>
              </thead>
              <tbody>
                {salesByMonth.map((sale) => (
                  <tr key={sale.month}>
                    <td style={{ padding: '0.75rem' }}>{sale.month}</td>
                    <td style={{ padding: '0.75rem' }}>
                      <strong>{sale.quantity_sold}</strong>
                    </td>
                    <td className="text-success" style={{ padding: '0.75rem' }}>
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

      <div className="card mb-4" style={{ padding: '1.5rem' }}>
        <h5 className="mb-4">Recent Orders</h5>
        {recentOrders.length === 0 ? (
          <div className="text-center py-5">
            <p className="text-muted mb-0">
              No orders yet for this product.
            </p>
          </div>
        ) : (
          <div className="table-responsive">
            <table className="table table-hover">
              <thead>
                <tr>
                  <th style={{ padding: '0.75rem' }}>Order Number</th>
                  <th style={{ padding: '0.75rem' }}>Customer</th>
                  <th style={{ padding: '0.75rem' }}>Quantity</th>
                  <th style={{ padding: '0.75rem' }}>Subtotal</th>
                  <th style={{ padding: '0.75rem' }}>Date</th>
                  <th style={{ padding: '0.75rem' }}>Status</th>
                </tr>
              </thead>
              <tbody>
                {recentOrders.map((item) => (
                  <tr key={item.id}>
                    <td style={{ padding: '0.75rem' }}>
                      {item.order?.order_number || 'N/A'}
                    </td>
                    <td style={{ padding: '0.75rem' }}>
                      {item.order?.user?.name || 'N/A'}
                    </td>
                    <td style={{ padding: '0.75rem' }}>
                      <strong>{item.quantity}</strong>
                    </td>
                    <td className="text-success" style={{ padding: '0.75rem' }}>
                      <strong>
                        IDR {Number(item.subtotal || 0).toLocaleString('id-ID')}
                      </strong>
                    </td>
                    <td style={{ padding: '0.75rem' }}>
                      {new Date(item.created_at).toLocaleString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                      })}
                    </td>
                    <td style={{ padding: '0.75rem' }}>
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

export default SellerProductAnalyticsPage;

