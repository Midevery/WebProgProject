import { useEffect, useState } from 'react';
import { api } from '../../api/client.js';

function AdminEarningPage() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function load() {
      setLoading(true);
      const res = await api.get('/admin/earning');
      setData(res.data);
      setLoading(false);
    }
    load();
  }, []);

  if (loading || !data) {
    return (
      <div className="container my-4">
        <p>Loading earnings...</p>
      </div>
    );
  }

  return (
    <div className="container my-4">
      <h2 className="mb-4">Admin - Earnings</h2>
      <p>
        <strong>Total Revenue:</strong> IDR{' '}
        {data.totalRevenue.toLocaleString('id-ID')}
      </p>
      <p>
        <strong>Admin Net Earning:</strong> IDR{' '}
        {data.adminNetEarning.toLocaleString('id-ID')}
      </p>
    </div>
  );
}

export default AdminEarningPage;



