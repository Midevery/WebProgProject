import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { api } from '../../api/client.js';
import { resolveImageUrl } from '../../api/media.js';

function SellerShippingPage() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const navigate = useNavigate();

  useEffect(() => {
    loadOrders();
  }, []);

  const loadOrders = async () => {
    setLoading(true);
    try {
      const res = await api.get('/seller/shipping');
      setOrders(res.data.orders || []);
    } catch (err) {
      setError('Failed to load shipping orders');
    } finally {
      setLoading(false);
    }
  };

  const handleUpdateStatus = async (orderId, status, trackingNumber, courier) => {
    try {
      const payload = {
        status,
      };
      if (trackingNumber) {
        payload.tracking_number = trackingNumber;
      }
      if (courier) {
        payload.courier = courier;
      }
      await api.put(`/seller/shipping/${orderId}/status`, payload);
      setSuccess('Shipping status updated successfully!');
      setTimeout(() => setSuccess(''), 3000);
      loadOrders();
    } catch (err) {
      const msg =
        err.response?.data?.message || 'Failed to update shipping status';
      setError(msg);
      setTimeout(() => setError(''), 3000);
    }
  };

  if (loading) {
    return (
      <div className="container my-4">
        <p>Loading shipping orders...</p>
      </div>
    );
  }

  return (
    <div className="container my-5">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1 className="section-title mb-0">Manage Shipping</h1>
        <button
          type="button"
          className="btn btn-outline-secondary"
          onClick={() => navigate('/seller/dashboard')}
        >
          <i className="bi bi-arrow-left me-2" />
          Back to Dashboard
        </button>
      </div>

      {error && (
        <div className="alert alert-danger alert-dismissible fade show" role="alert">
          {error}
          <button
            type="button"
            className="btn-close"
            onClick={() => setError('')}
            aria-label="Close"
          />
        </div>
      )}

      {success && (
        <div className="alert alert-success alert-dismissible fade show" role="alert">
          {success}
          <button
            type="button"
            className="btn-close"
            onClick={() => setSuccess('')}
            aria-label="Close"
          />
        </div>
      )}

      {orders.length === 0 ? (
        <div className="card">
          <div className="card-body text-center py-5">
            <p className="text-muted mb-0">No orders to ship yet.</p>
          </div>
        </div>
      ) : (
        <div className="card">
          <div className="card-body">
            <div className="table-responsive">
              <table className="table table-hover">
                <thead>
                  <tr>
                    <th>Order Number</th>
                    <th>Customer</th>
                    <th>Products</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Tracking</th>
                    <th>Courier</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                {orders.map((order) => {
                  const statusForAction = order.shipping?.status || order.status || 'pending';
                  
                  return (
                  <tr key={order.id}>
                      <td>
                        <strong>{order.order_number}</strong>
                        <br />
                        <small className="text-muted">
                          {new Date(order.created_at).toLocaleDateString('id-ID')}
                        </small>
                      </td>
                      <td>
                        {order.user?.name || 'N/A'}
                        <br />
                        <small className="text-muted">{order.user?.email || ''}</small>
                      </td>
                      <td>
                        {order.order_items?.map((item, idx) => (
                          <div key={idx} className="d-flex align-items-center mb-2">
                            <img
                              src={resolveImageUrl(item.product?.image)}
                              alt={item.product?.name}
                              style={{
                                width: '40px',
                                height: '40px',
                                objectFit: 'cover',
                                borderRadius: '4px',
                                marginRight: '8px',
                              }}
                              onError={(e) => {
                                e.currentTarget.src = 'https://picsum.photos/40/40';
                              }}
                            />
                            <div>
                              <small className="d-block">{item.product?.name}</small>
                              <small className="text-muted">Qty: {item.quantity}</small>
                            </div>
                          </div>
                        ))}
                      </td>
                      <td>
                        <strong>
                          IDR {Number(order.total_amount || 0).toLocaleString('id-ID')}
                        </strong>
                      </td>
                      <td>
                        <span
                          className={`badge bg-${
                            statusForAction === 'delivered'
                              ? 'success'
                              : statusForAction === 'shipped'
                                ? 'info'
                              : statusForAction === 'processing'
                                ? 'primary'
                                : 'warning'
                          }`}
                        >
                          {statusForAction}
                        </span>
                      </td>
                      <td>
                        {order.shipping?.tracking_number ? (
                          <small>{order.shipping.tracking_number}</small>
                        ) : (
                          <small className="text-muted">-</small>
                        )}
                      </td>
                      <td>
                        {order.shipping?.courier ? (
                          <small>{order.shipping.courier}</small>
                        ) : (
                          <small className="text-muted">-</small>
                        )}
                      </td>
                      <td>
                        {/* Show button for pending orders - simple check */}
                        {statusForAction === 'processing' && (
                          <ShippingForm
                            orderId={order.id}
                            currentStatus="processing"
                            existingCourier={order.shipping?.courier || ''}
                            onUpdate={handleUpdateStatus}
                          />
                        )}
                        {/* Show button for shipped orders to mark as delivered */}
                {statusForAction === 'shipped' && order.status !== 'delivered' && (
                          <button
                            className="btn btn-sm btn-success"
                            onClick={() =>
                              handleUpdateStatus(
                                order.id,
                                'delivered',
                                order.shipping.tracking_number,
                                order.shipping.courier,
                              )
                            }
                          >
                            Mark Delivered
                          </button>
                        )}
                        {/* Show delivered badge */}
                        {order.shipping?.status === 'delivered' && (
                          <span className="badge bg-success">Delivered</span>
                        )}
                      </td>
                  </tr>
                )})}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

function ShippingForm({ orderId, currentStatus, existingCourier = '', onUpdate }) {
  const [showForm, setShowForm] = useState(false);
  const [trackingNumber, setTrackingNumber] = useState('');

  const handleSubmit = (e) => {
    e.preventDefault();
    // pending -> processing (no tracking needed)
    // processing -> shipped (needs tracking, courier is from existing)
    const nextStatus = currentStatus === 'pending' ? 'processing' : 'shipped';
    
    // If going to shipped, require tracking number
    if (nextStatus === 'shipped' && !trackingNumber) {
      return;
    }
    
    // Always use existing courier (cannot be changed)
    const courierToUse = existingCourier;
    
    onUpdate(orderId, nextStatus, trackingNumber || null, courierToUse || null);
    setShowForm(false);
    setTrackingNumber('');
  };

  if (!showForm) {
    const buttonText = currentStatus === 'pending' ? 'Start Processing' : 'Ship Now';
    return (
      <button
        className="btn btn-sm btn-primary"
        onClick={() => setShowForm(true)}
      >
        <i className="bi bi-truck me-1" />
        {buttonText}
      </button>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="d-flex gap-2 flex-column">
      {currentStatus === 'processing' && (
        <>
          <input
            type="text"
            className="form-control form-control-sm"
            placeholder="Tracking Number"
            value={trackingNumber}
            onChange={(e) => setTrackingNumber(e.target.value)}
            required
          />
          <input
            type="text"
            className="form-control form-control-sm"
            placeholder="Courier"
            value={existingCourier}
            disabled
            readOnly
            style={{ backgroundColor: '#e9ecef', cursor: 'not-allowed' }}
          />
          {existingCourier && (
            <small className="text-muted">
              <i className="bi bi-info-circle me-1" />
              Courier cannot be changed
            </small>
          )}
        </>
      )}
      <div className="d-flex gap-1">
        <button type="submit" className="btn btn-sm btn-success">
          <i className="bi bi-check me-1" />
          {currentStatus === 'pending' ? 'Start Processing' : 'Confirm Ship'}
        </button>
        <button
          type="button"
          className="btn btn-sm btn-secondary"
          onClick={() => {
            setShowForm(false);
            setTrackingNumber('');
          }}
        >
          Cancel
        </button>
      </div>
    </form>
  );
}

export default SellerShippingPage;

