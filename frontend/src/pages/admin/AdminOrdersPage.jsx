import { useEffect, useState } from 'react';
import { api } from '../../api/client.js';

function AdminOrdersPage() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function load() {
      setLoading(true);
      const res = await api.get('/admin/orders');
      setData(res.data);
      setLoading(false);
    }
    load();
  }, []);

  if (loading || !data) {
    return (
      <div className="container my-4">
        <p>Loading orders...</p>
      </div>
    );
  }

  const orders = data.orders?.data || [];

  return (
    <div className="container my-4">
      <h2 className="mb-4">Admin - All Orders</h2>
      <div className="table-responsive">
        <table className="table">
          <thead>
            <tr>
              <th>Order</th>
              <th>User</th>
              <th>Status</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            {orders.map((o) => (
              <tr key={o.id}>
                <td>{o.order_number}</td>
                <td>{o.user?.name}</td>
                <td>{o.status}</td>
                <td>IDR {o.total_amount?.toLocaleString('id-ID')}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

export default AdminOrdersPage;



