import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../api/client.js';
import { useCart } from '../contexts/CartContext.jsx';
import { resolveImageUrl } from '../api/media.js';

function ShippingDetailPage() {
  const { orderId } = useParams();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();
  const { refreshCartCount } = useCart();

  useEffect(() => {
    async function load() {
      setLoading(true);
      try {
        const res = await api.get(`/shipping/${orderId}`);
        setOrder(res.data.order);
      } catch {
        navigate('/shipping');
      } finally {
        setLoading(false);
      }
    }
    load();
  }, [orderId, navigate]);

  if (loading || !order) {
    return (
      <div className="container my-4">
        <p>Loading tracking...</p>
      </div>
    );
  }

  const shipping = order.shipping;

  const statuses = {
    pending: ['Pending', 'Order received'],
    processing: ['Processing', 'Order is being prepared'],
    shipped: ['Shipped', 'Order has been shipped'],
    in_transit: ['In Transit', 'Order is on the way'],
    delivered: ['Delivered', 'Order has been delivered'],
  };

  const statusOrder = ['pending', 'processing', 'shipped', 'in_transit', 'delivered'];
  const timelineStatus =
    shipping?.status && shipping.status !== 'pending'
      ? shipping.status
      : order.status || 'pending';
  const currentIndex = statusOrder.indexOf(timelineStatus);

  const createdDateTime = order.created_at
    ? new Date(order.created_at).toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    : '';

  const badgeVariant =
    order.status === 'delivered'
      ? 'success'
      : order.status === 'shipped'
      ? 'info'
      : 'warning';

  return (
    <div className="container my-4">
      <button
        type="button"
        className="btn btn-outline-primary mb-3"
        onClick={() => navigate(-1)}
      >
        ← Back
      </button>

      <h2 className="mb-4">Track Order #{order.order_number}</h2>
      {order.status === 'pending' && (
        <div className="mb-3">
          <button
            type="button"
            className="btn btn-success"
            onClick={() => {
              refreshCartCount();
              navigate(`/payment/${order.id}`);
            }}
          >
            Pay Now
          </button>
        </div>
      )}

      {/* Order Status Timeline */}
      <div className="card mb-4">
        <div className="card-body">
          <h5 className="card-title mb-4">Order Status</h5>
          <div className="row">
            <div className="col-md-12">
              <div className="timeline">
                {statusOrder.map((statusKey, index) => {
                  const info = statuses[statusKey];
                  if (!info) return null;
                  const isActive = index <= currentIndex && currentIndex !== -1;
                  const lineActive = index < currentIndex;

                  return (
                    <div key={statusKey} className="timeline-item mb-4">
                      <div className="d-flex align-items-start">
                        <div
                          className={`timeline-marker me-3 ${
                            isActive ? 'active' : ''
                          }`}
                        >
                          <i
                            className={`bi bi-${
                              isActive ? 'check-circle-fill' : 'circle'
                            }`}
                          />
                        </div>
                        <div className="flex-grow-1">
                          <h6
                            className={`mb-1 ${
                              isActive ? 'text-primary' : 'text-muted'
                            }`}
                          >
                            {info[0]}
                          </h6>
                          <p className="text-muted small mb-0">{info[1]}</p>
                        </div>
                      </div>
                      {index < statusOrder.length - 1 && (
                        <div
                          className={`timeline-line ms-4 ${
                            lineActive ? 'active' : ''
                          }`}
                        />
                      )}
                    </div>
                  );
                })}
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Order Details */}
      <div className="card mb-4">
        <div className="card-body">
          <h5 className="card-title">Order Details</h5>
          <div className="row">
            <div className="col-md-6">
              <p>
                <strong>Order Number:</strong> {order.order_number}
              </p>
              <p>
                <strong>Order Date:</strong> {createdDateTime}
              </p>
              <p>
                <strong>Total Amount:</strong> IDR{' '}
                {Number(order.total_amount || 0).toLocaleString('id-ID')}
              </p>
              <p>
                <strong>Order Status:</strong>{' '}
                <span className={`badge bg-${badgeVariant}`}>
                  {order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                </span>
              </p>
            </div>
            <div className="col-md-6">
              <p>
                <strong>Shipping Address:</strong>
              </p>
              <p className="text-muted">{order.shipping_address}</p>
              <p>
                <strong>Shipping Method:</strong>{' '}
                {order.shipping_method || 'Standard'}
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Shipping Information */}
      {shipping && (
        <div className="card mb-4">
          <div className="card-body">
            <h5 className="card-title">Shipping Information</h5>
            <div className="row">
              <div className="col-md-6">
                <p>
                  <strong>Courier:</strong> {shipping.courier || 'Not set'}
                </p>
                <p>
                  <strong>Tracking Number:</strong>{' '}
                  {shipping.tracking_number ? (
                    <span className="badge bg-info">
                      {shipping.tracking_number}
                    </span>
                  ) : (
                    <span className="text-muted">Not available yet</span>
                  )}
                </p>
                <p>
                  <strong>Shipping Status:</strong>{' '}
                  <span
                    className={`badge bg-${
                      shipping.status === 'delivered'
                        ? 'success'
                        : shipping.status === 'shipped'
                        ? 'info'
                        : 'warning'
                    }`}
                  >
                    {shipping.status.charAt(0).toUpperCase() +
                      shipping.status.slice(1)}
                  </span>
                </p>
              </div>
              <div className="col-md-6">
                {shipping.shipped_at && (
                  <p>
                    <strong>Shipped At:</strong>{' '}
                    {new Date(shipping.shipped_at).toLocaleString('en-GB', {
                      day: '2-digit',
                      month: 'short',
                      year: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit',
                    })}
                  </p>
                )}
                {shipping.delivered_at && (
                  <p>
                    <strong>Delivered At:</strong>{' '}
                    {new Date(shipping.delivered_at).toLocaleString('en-GB', {
                      day: '2-digit',
                      month: 'short',
                      year: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit',
                    })}
                  </p>
                )}
                {shipping.notes && (
                  <p>
                    <strong>Notes:</strong> {shipping.notes}
                  </p>
                )}
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Order Items */}
      <div className="card">
        <div className="card-body">
          <h5 className="card-title">Order Items</h5>
          <div className="table-responsive">
            <table className="table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Quantity</th>
                  <th>Price</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                {order.order_items.map((item) => (
                  <tr key={item.id}>
                    <td>
                      <div className="d-flex align-items-center">
                        <img
                          src={resolveImageUrl(
                            item.product.image,
                            `https://picsum.photos/60/60?random=${item.product.id}`,
                          )}
                          className="rounded me-2"
                          alt={item.product.name}
                          style={{ width: 60, height: 60, objectFit: 'cover' }}
                          onError={(e) => {
                            e.currentTarget.src = `https://picsum.photos/60/60?random=${item.product.id}`;
                          }}
                        />
                        <div>
                          <strong>{item.product.name}</strong>
                          <br />
                          <small className="text-muted">
                            {item.product.category?.name || 'N/A'}
                          </small>
                        </div>
                      </div>
                    </td>
                    <td>{item.quantity}</td>
                    <td>
                      IDR {item.price.toLocaleString('id-ID')}
                    </td>
                    <td>
                      <strong>
                        IDR {item.subtotal.toLocaleString('id-ID')}
                      </strong>
                    </td>
                  </tr>
                ))}
              </tbody>
              <tfoot>
                <tr>
                  <th colSpan={3} className="text-end">
                    Total:
                  </th>
                  <th>
                    IDR {Number(order.total_amount || 0).toLocaleString('id-ID')}
                  </th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
}

export default ShippingDetailPage;



