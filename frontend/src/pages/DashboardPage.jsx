import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../api/client.js';
import { resolveImageUrl } from '../api/media.js';
import { useCart } from '../contexts/CartContext.jsx';

function DashboardPage() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [wishlistSet, setWishlistSet] = useState(new Set());
  const [feedback, setFeedback] = useState(null);
  const { refreshCartCount } = useCart();

  useEffect(() => {
    async function load() {
      setLoading(true);
      try {
        const res = await api.get('/dashboard');
        setData(res.data);
        const ids = res.data.wishlistProductIds || [];
        setWishlistSet(new Set(ids));
      } catch (err) {
        console.error('Failed to load dashboard', err);
      } finally {
        setLoading(false);
      }
    }
    load();
  }, []);

  if (loading || !data) {
    return (
      <div className="container my-4">
        <p>Loading dashboard...</p>
      </div>
    );
  }

  const {
    ordersInProgress,
    totalSpending,
    rewardPoints,
    activeVouchers,
    recommendedProducts = [],
    ongoingCount,
    cancelledCount,
    deliveredCount,
    recentOrders = [],
    wishlistProductIds = [],
  } = data;

  const totalOrders = ongoingCount + cancelledCount + deliveredCount;

  let circleStyle = {};
  if (totalOrders > 0) {
    const ongoingPercent = (ongoingCount / totalOrders) * 360;
    const cancelledPercent = (cancelledCount / totalOrders) * 360;
    const deliveredPercent = (deliveredCount / totalOrders) * 360;

    const ongoingEnd = ongoingPercent;
    const cancelledStart = ongoingEnd;
    const cancelledEnd = cancelledStart + cancelledPercent;
    const deliveredStart = cancelledEnd;

    circleStyle = {
      width: 150,
      height: 150,
      borderRadius: '50%',
      background: `conic-gradient(
        #1E88E5 0deg ${ongoingEnd}deg,
        #90CAF9 ${cancelledStart}deg ${cancelledEnd}deg,
        #64B5F6 ${deliveredStart}deg 360deg
      )`,
    };
  } else {
    circleStyle = {
      width: 150,
      height: 150,
      borderRadius: '50%',
      background: '#e9ecef',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
    };
  }

  return (
    <div className="container-fluid my-4">
      <div className="row">
        <div className="col-md-3 mb-4">
          <div className="card mb-3 dashboard-card">
            <div className="card-body">
              <div className="d-flex align-items-center">
                <div className="me-3">
                  <i
                    className="bi bi-box-arrow-up"
                    style={{ fontSize: '2rem', color: 'var(--refurbworks-primary)' }}
                  />
                </div>
                <div>
                  <p className="text-muted mb-0 small">Order in Progress</p>
                  <h4 className="mb-0">{ordersInProgress}</h4>
                </div>
              </div>
            </div>
          </div>
          <div className="card mb-3 dashboard-card">
            <div className="card-body">
              <div className="d-flex align-items-center">
                <div className="me-3">
                  <i
                    className="bi bi-currency-dollar"
                    style={{ fontSize: '2rem', color: 'var(--refurbworks-primary)' }}
                  />
                </div>
                <div>
                  <p className="text-muted mb-0 small">
                    Total Spending This Month
                  </p>
                  <h4 className="mb-0">
                    IDR {Number(totalSpending || 0).toLocaleString('id-ID')}
                  </h4>
                </div>
              </div>
            </div>
          </div>
          <div className="card mb-3 dashboard-card">
            <div className="card-body">
              <div className="d-flex align-items-center">
                <div className="me-3">
                  <i
                    className="bi bi-star-fill"
                    style={{ fontSize: '2rem', color: 'var(--refurbworks-primary)' }}
                  />
                </div>
                <div>
                  <p className="text-muted mb-0 small">Reward Point</p>
                  <h4 className="mb-0">{rewardPoints}</h4>
                </div>
              </div>
            </div>
          </div>
          <div className="card dashboard-card">
            <div className="card-body">
              <div className="d-flex align-items-center">
                <div className="me-3">
                  <i
                    className="bi bi-percent"
                    style={{ fontSize: '2rem', color: 'var(--refurbworks-primary)' }}
                  />
                </div>
                <div>
                  <p className="text-muted mb-0 small">Active Voucher</p>
                  <h4 className="mb-0">{activeVouchers}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div className="col-md-9">
          {!!feedback && (
            <div
              className={`alert alert-${feedback.type} alert-dismissible fade show mb-3`}
              role="alert"
            >
              {feedback.message}
              <button
                type="button"
                className="btn-close"
                onClick={() => setFeedback(null)}
                aria-label="Close"
              />
            </div>
          )}
          <div className="card mb-4">
            <div className="card-body">
              <h4 className="mb-4">Recommended For You</h4>
              <div className="row g-3">
                {recommendedProducts.map((product) => (
                  <div key={product.id} className="col-6 col-md-3">
                    <div className="product-card">
                      <Link
                        to={`/products/${product.id}`}
                        className="text-decoration-none text-dark"
                      >
                        <div className="position-relative">
                          <img
                            src={resolveImageUrl(
                              product.image,
                              `https://picsum.photos/200/200?random=${product.id}`,
                            )}
                            className="product-image"
                            alt={product.name}
                            onError={(e) => {
                              e.currentTarget.src = `https://picsum.photos/200/200?random=${product.id}`;
                            }}
                          />
                          <span
                            className={`product-badge ${
                              product.stock > 0 ? 'badge-ready' : 'badge-preorder'
                            }`}
                          >
                            {product.stock > 0 ? 'Ready Stock' : 'Pre-Order'}
                          </span>
                        </div>
                        <div className="p-3">
                          <h6 className="mb-1 small">{product.name}</h6>
                          <p className="product-price mb-2">
                    IDR {Number(product.price || 0).toLocaleString('id-ID')}
                          </p>
                        </div>
                      </Link>
                      <div className="px-3 pb-3 d-flex gap-2">
                        {(() => {
                          const active = wishlistSet.has(product.id);
                          return (
                            <button
                              type="button"
                              className={`btn btn-sm flex-grow-1 ${
                                active ? 'btn-danger' : 'btn-outline-danger'
                              }`}
                              onClick={async () => {
                                const res = await api.post('/wishlist/toggle', {
                                  product_id: product.id,
                                });
                                const status = res.data.status;
                                setWishlistSet((prev) => {
                                  const next = new Set(prev);
                                  if (status === 'added') next.add(product.id);
                                  else if (status === 'removed')
                                    next.delete(product.id);
                                  return next;
                                });
                              }}
                            >
                              <i
                                className={`bi ${
                                  active ? 'bi-heart-fill' : 'bi-heart'
                                }`}
                              />
                            </button>
                          );
                        })()}
                        <button
                          type="button"
                          className="btn btn-sm btn-primary flex-grow-1"
                          onClick={async () => {
                            try {
                              await api.post('/cart', {
                                product_id: product.id,
                                quantity: 1,
                              });
                              await refreshCartCount();
                              setFeedback({
                                type: 'success',
                                message: 'Product added successfully',
                              });
                            } catch (err) {
                              setFeedback({
                                type: 'danger',
                                message:
                                  err.response?.data?.message ||
                                  'Failed to add product to cart',
                              });
                            }
                          }}
                        >
                          <i className="bi bi-cart3" />
                        </button>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>

          <div className="row">
            <div className="col-md-6 mb-4">
              <div className="card">
                <div className="card-body">
                  <h5 className="mb-4">Order Statistics</h5>
                  <div className="d-flex align-items-center justify-content-center">
                    <div className="text-center me-4">
                      <div className="mb-3">
                        <div style={circleStyle}>
                          {totalOrders === 0 && (
                            <small className="text-muted">No orders yet</small>
                          )}
                        </div>
                      </div>
                    </div>
                    <div>
                      <div className="mb-2">
                        <span
                          className="badge"
                          style={{
                            backgroundColor: '#1E88E5',
                            width: 20,
                            height: 20,
                            display: 'inline-block',
                          }}
                        />
                        <span className="ms-2">
                          Ongoing ({ongoingCount})
                        </span>
                      </div>
                      <div className="mb-2">
                        <span
                          className="badge"
                          style={{
                            backgroundColor: '#90CAF9',
                            width: 20,
                            height: 20,
                            display: 'inline-block',
                          }}
                        />
                        <span className="ms-2">
                          Cancelled ({cancelledCount})
                        </span>
                      </div>
                      <div>
                        <span
                          className="badge"
                          style={{
                            backgroundColor: '#64B5F6',
                            width: 20,
                            height: 20,
                            display: 'inline-block',
                          }}
                        />
                        <span className="ms-2">
                          Delivered ({deliveredCount})
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div className="col-md-6 mb-4">
              <div className="card">
                <div className="card-body">
                  <div className="d-flex justify-content-between align-items-center mb-3">
                    <h5 className="mb-0">Recent Order</h5>
                    <Link
                      to="/shipping"
                      className="btn btn-sm btn-outline-primary"
                    >
                      View All
                    </Link>
                  </div>
                  <div className="table-responsive">
                    <table className="table table-sm">
                      <thead>
                        <tr>
                          <th>Photo</th>
                          <th>Product Name</th>
                          <th>Price</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        {recentOrders.length === 0 && (
                          <tr>
                            <td
                              colSpan={4}
                              className="text-center text-muted"
                            >
                              No recent orders
                            </td>
                          </tr>
                        )}
                        {recentOrders.map((order) =>
                          (order.order_items || []).slice(0, 1).map((item) => (
                            <tr key={item.id}>
                              <td>
                                <img
                                  src={resolveImageUrl(
                                    item.product?.image,
                                    `https://picsum.photos/50/50?random=${item.product?.id}`,
                                  )}
                                  alt={item.product?.name}
                                  className="rounded"
                                  style={{
                                    width: 50,
                                    height: 50,
                                    objectFit: 'cover',
                                  }}
                                />
                              </td>
                              <td>{item.product?.name}</td>
                              <td>
                                IDR{' '}
                                {Number(item.price || 0).toLocaleString('id-ID')}
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
                            </tr>
                          )),
                        )}
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default DashboardPage;



