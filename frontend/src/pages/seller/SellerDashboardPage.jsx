import { useEffect, useState, useMemo } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { api } from '../../api/client.js';

function SellerDashboardPage() {
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
        const res = await api.get('/seller/dashboard');
        setData(res.data);
      } catch (err) {
        if (err.response?.status === 401) {
          navigate('/signin');
        }
      } finally {
        setLoading(false);
      }
    }
    load();
    
    // Refresh when product is added/updated
    const handleProductUpdate = () => {
      load();
    };
    window.addEventListener('productUpdated', handleProductUpdate);
    
    return () => {
      window.removeEventListener('productUpdated', handleProductUpdate);
    };
  }, [navigate]);

  if (loading || !data) {
    return (
      <div className="container my-4">
        <p>Loading seller dashboard...</p>
      </div>
    );
  }

  const {
    totalSales = 0,
    totalOrders = 0,
    totalPlatformFee = 0,
    netSales = 0,
    productsWithSales = [],
  } = data;

  return (
    <div className="container my-5">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1 className="section-title mb-0">Seller Dashboard</h1>
        <div className="d-flex gap-2">
          <button
            className="btn btn-primary"
            onClick={() => navigate('/seller/products/add')}
          >
            <i className="bi bi-plus-circle me-2" />
            Add Product
          </button>
          <button
            className="btn btn-outline-primary"
            onClick={() => navigate('/seller/shipping')}
          >
            <i className="bi bi-truck me-2" />
            Manage Shipping
          </button>
        </div>
      </div>

      <div className="row mb-4">
        <div className="col-md-3 mb-3">
          <div className="stat-card">
            <div className="stat-value">
              IDR {totalSales.toLocaleString('id-ID')}
            </div>
            <div className="stat-label">Gross Sales</div>
          </div>
        </div>
        <div className="col-md-3 mb-3">
          <div className="stat-card stat-card-warm">
            <div className="stat-value">IDR 0</div>
            <div className="stat-label">Total Cost</div>
          </div>
        </div>
        <div className="col-md-3 mb-3">
          <div className="stat-card stat-card-danger">
            <div className="stat-value">
              IDR {totalPlatformFee.toLocaleString('id-ID')}
            </div>
            <div className="stat-label">Platform Fee</div>
          </div>
        </div>
        <div className="col-md-3 mb-3">
          <div className="stat-card stat-card-success">
            <div className="stat-value">
              IDR {netSales.toLocaleString('id-ID')}
            </div>
            <div className="stat-label">Net Sales (You Receive)</div>
          </div>
        </div>
      </div>

      <div className="row mb-4">
        <div className="col-md-3 mb-3">
          <div className="stat-card">
            <div className="stat-value">{totalOrders}</div>
            <div className="stat-label">Total Orders</div>
          </div>
        </div>
      </div>

      <div className="dashboard-card">
        <div className="d-flex justify-content-between align-items-center mb-4">
          <h3 className="mb-0">My Products</h3>
          <span className="badge bg-primary">
            {productsWithSales.length} {productsWithSales.length === 1 ? 'Product' : 'Products'}
          </span>
        </div>
        {productsWithSales.length === 0 ? (
          <div className="text-center py-5">
            <p className="text-muted mb-3">
              No products yet. Add your first product to get started.
            </p>
            <button
              className="btn btn-primary"
              onClick={() => navigate('/seller/products/add')}
            >
              <i className="bi bi-plus-circle me-2" />
              Add Your First Product
            </button>
          </div>
        ) : (
          <div className="table-responsive">
            <table className="product-table w-100">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Unit Price</th>
                  <th>Total Sales</th>
                  <th>Gross Earning</th>
                  <th>Cost</th>
                  <th>Platform Fee</th>
                  <th>Net Earning</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                {productsWithSales.map((product) => (
                  <tr key={product.id}>
                    <td>
                      <div className="d-flex align-items-center">
                        <img
                          src={resolveImageUrl(
                            product.image,
                            `https://picsum.photos/60/60?random=${product.id}`
                          )}
                          alt={product.name}
                          className="product-image-small me-3"
                          onError={(e) => {
                            e.currentTarget.src = `https://picsum.photos/60/60?random=${product.id}`;
                          }}
                        />
                        <div>
                          <div className="fw-bold">{product.name}</div>
                          <small className="text-muted">
                            Category: {product.category?.name || 'N/A'}
                          </small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <strong>
                        IDR {Number(product.price || 0).toLocaleString('id-ID')}
                      </strong>
                    </td>
                    <td>
                      <strong>{product.total_sold || 0}</strong>
                    </td>
                    <td>
                      <strong>
                        IDR{' '}
                        {Number(product.total_earning || 0).toLocaleString(
                          'id-ID',
                        )}
                      </strong>
                    </td>
                    <td>
                      <small className="text-muted">
                        - IDR{' '}
                        {(
                          (product.total_sold || 0) * (product.cost || 0)
                        ).toLocaleString('id-ID')}
                      </small>
                    </td>
                    <td>
                      <small className="text-muted">
                        - IDR{' '}
                        {Number(product.platform_fee || 0).toLocaleString(
                          'id-ID',
                        )}
                      </small>
                    </td>
                    <td>
                      <strong className="text-success">
                        IDR{' '}
                        {Number(product.net_earning || 0).toLocaleString(
                          'id-ID',
                        )}
                      </strong>
                    </td>
                    <td>
                      {product.stock > 0 ? (
                        <span className="badge-status badge-in-stock">
                          In Stock
                        </span>
                      ) : (
                        <span className="badge-status badge-out-of-stock">
                          Out of Stock
                        </span>
                      )}
                    </td>
                    <td>
                      <div className="d-flex gap-1">
                        <Link
                          to={`/seller/product/${product.id}/analytics`}
                          className="btn btn-sm btn-primary"
                        >
                          <i className="bi bi-graph-up me-1" />
                          Analytics
                        </Link>
                        <Link
                          to={`/seller/products/${product.id}/edit`}
                          className="btn btn-sm btn-outline-secondary"
                        >
                          <i className="bi bi-pencil me-1" />
                          Edit
                        </Link>
                      </div>
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

export default SellerDashboardPage;

