import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { api } from '../api/client.js';

function ShippingPage() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    async function load() {
      setLoading(true);
      try {
        const res = await api.get('/shipping');
        setOrders(res.data.orders || []);
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

  if (loading) {
    return (
      <div className="container my-4">
        <p>Loading orders...</p>
      </div>
    );
  }

  if (orders.length === 0) {
    return (
      <div className="container my-4">
        <h2 className="mb-4">My Orders</h2>
        <div className="card">
          <div className="card-body text-center py-5">
            <i className="bi bi-box-seam" style={{ fontSize: '4rem', color: '#ccc' }} />
            <p className="text-muted mt-3">No orders yet</p>
            <Link to="/products" className="btn btn-primary">
              Start Shopping
            </Link>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="container my-4">
      <h2 className="mb-4">My Orders</h2>
      {orders.map((order) => {
        const createdDate = order.created_at
          ? new Date(order.created_at).toLocaleDateString('en-GB', {
              day: '2-digit',
              month: 'short',
              year: 'numeric',
            })
          : '';
        const status = order.status || 'pending';
        const badgeVariant =
          status === 'delivered' ? 'success' : status === 'shipped' ? 'info' : 'warning';

        return (
          <div key={order.id} className="card mb-3">
            <div className="card-body">
              <div className="row align-items-center">
                <div className="col-md-8">
                  <h5>Order #{order.order_number}</h5>
                  <p className="text-muted mb-1">Date: {createdDate}</p>
                  <p className="text-muted mb-1">
                    Total: IDR {order.total_amount?.toLocaleString('id-ID')}
                  </p>
                  <p className="mb-0">
                    <span className={`badge bg-${badgeVariant}`}>
                      {status.charAt(0).toUpperCase() + status.slice(1)}
                    </span>
                  </p>
                </div>
                <div className="col-md-4 text-end">
                  <button
                    type="button"
                    className="btn btn-primary"
                    onClick={() => navigate(`/shipping/${order.id}`)}
                  >
                    View Details
                  </button>
                </div>
              </div>
            </div>
          </div>
        );
      })}
    </div>
  );
}

export default ShippingPage;



