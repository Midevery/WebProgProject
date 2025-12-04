import { useEffect, useState } from 'react';
import { api } from '../../api/client.js';

function AdminProfilePage() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function load() {
      setLoading(true);
      const res = await api.get('/admin/profile');
      setData(res.data);
      setLoading(false);
    }
    load();
  }, []);

  if (loading || !data) {
    return (
      <div className="container my-4">
        <p>Loading admin profile...</p>
      </div>
    );
  }

  const { admin } = data;

  return (
    <div className="container my-4">
      <h2 className="mb-3">Admin Profile</h2>
      <p>
        <strong>Name:</strong> {admin.name}
      </p>
      <p>
        <strong>Email:</strong> {admin.email}
      </p>
    </div>
  );
}

export default AdminProfilePage;



