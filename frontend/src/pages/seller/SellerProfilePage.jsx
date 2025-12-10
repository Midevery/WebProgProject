import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { api } from '../../api/client.js';
import { resolveImageUrl } from '../../api/media.js';

function SellerProfilePage() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    async function load() {
      setLoading(true);
      try {
        const res = await api.get('/seller/profile');
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
  }, [navigate]);

  if (loading || !data) {
    return (
      <div className="container my-4">
        <p>Loading seller profile...</p>
      </div>
    );
  }

  const { seller, products = [], totalSales = 0, totalOrders = 0 } = data;

  return (
    <div className="container my-5">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1 className="section-title mb-0">Seller Profile</h1>
        <button
          type="button"
          className="btn btn-outline-primary"
          onClick={() => navigate('/seller/dashboard')}
        >
          <i className="bi bi-arrow-left me-2" />
          Back to Dashboard
        </button>
      </div>

      <div className="dashboard-card mb-4">
        <h3 className="mb-4">My Profile</h3>
        <div className="row">
          <div className="col-md-3 text-center mb-3">
            <img
              src={resolveImageUrl(
                seller.profile_image,
                `https://picsum.photos/150/150?random=${seller.id}`,
              )}
              alt={seller.name}
              className="profile-image mb-3"
              onError={(e) => {
                e.currentTarget.src = `https://picsum.photos/150/150?random=${seller.id}`;
              }}
            />
          </div>
          <div className="col-md-9">
            <div className="row mb-3">
              <div className="col-md-6">
                <p className="mb-1">
                  <strong>Username:</strong> {seller.username}
                </p>
                <p className="mb-1">
                  <strong>Name:</strong> {seller.name}
                </p>
                <p className="mb-1">
                  <strong>Phone:</strong> {seller.phone || '-'}
                </p>
              </div>
              <div className="col-md-6">
                <p className="mb-1">
                  <strong>Email:</strong> {seller.email}
                </p>
                <p className="mb-1">
                  <strong>Balance:</strong> IDR{' '}
                  {Number(seller.balance || 0).toLocaleString('id-ID')}
                </p>
              </div>
            </div>
            <button
              type="button"
              className="btn btn-primary"
              onClick={() => navigate('/profile')}
            >
              Edit Profile
            </button>
          </div>
        </div>
      </div>

      <div className="row mb-4">
        <div className="col-md-4 mb-3">
          <div className="stat-card">
            <div className="stat-value">
              IDR {Number(totalSales || 0).toLocaleString('id-ID')}
            </div>
            <div className="stat-label">Total Sales</div>
          </div>
        </div>
        <div className="col-md-4 mb-3">
          <div className="stat-card stat-card-info">
            <div className="stat-value">{totalOrders}</div>
            <div className="stat-label">Total Orders</div>
          </div>
        </div>
        <div className="col-md-4 mb-3">
          <div className="stat-card stat-card-success">
            <div className="stat-value">{products.length}</div>
            <div className="stat-label">Total Products</div>
          </div>
        </div>
      </div>

      <div className="dashboard-card">
        <h3 className="mb-4">My Products &amp; Analytics</h3>
        {products.length === 0 ? (
          <div className="text-center py-5">
            <p className="text-muted mb-0">No products yet.</p>
          </div>
        ) : (
          <div className="table-responsive">
            <table className="product-table w-100">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Stock</th>
                  <th>Total Sold</th>
                  <th>Total Orders</th>
                  <th>Gross Earning</th>
                  <th>Platform Fee</th>
                  <th>Net Earning</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                {products.map((product) => (
                  <tr key={product.id}>
                    <td>
                      <div className="d-flex align-items-center">
                        <img
                          src={resolveImageUrl(
                            product.image,
                            `https://picsum.photos/60/60?random=${product.id}`,
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
                            {product.category?.name || 'N/A'}
                          </small>
                        </div>
                      </div>
                    </td>
                    <td>{product.category?.name || 'N/A'}</td>
                    <td>
                      IDR {Number(product.price || 0).toLocaleString('id-ID')}
                    </td>
                    <td>{product.stock}</td>
                    <td>{product.total_sold || 0}</td>
                    <td>{product.total_orders || 0}</td>
                    <td>
                      IDR{' '}
                      {Number(product.total_earning || 0).toLocaleString('id-ID')}
                    </td>
                    <td>
                      IDR{' '}
                      {Number(product.platform_fee || 0).toLocaleString('id-ID')}
                    </td>
                    <td>
                      IDR{' '}
                      {Number(product.net_earning || 0).toLocaleString('id-ID')}
                    </td>
                    <td>
                      <Link
                        to={`/seller/product/${product.id}/analytics`}
                        className="btn btn-sm btn-primary"
                      >
                        <i className="bi bi-graph-up me-1" />
                        View Analytics
                      </Link>
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

export default SellerProfilePage;

