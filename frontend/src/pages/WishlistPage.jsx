import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { api } from '../api/client.js';
import { resolveImageUrl } from '../api/media.js';

function WishlistPage() {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    async function load() {
      setLoading(true);
      try {
        const res = await api.get('/wishlist');
        setItems(res.data.items || []);
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

  const removeItem = async (id) => {
    await api.delete(`/wishlist/${id}`);
    setItems((prev) => prev.filter((w) => w.id !== id));
  };

  if (loading) {
    return (
      <div className="container my-4">
        <p>Loading wishlist...</p>
      </div>
    );
  }

  if (items.length === 0) {
    return (
      <div className="container my-4">
        <button
          type="button"
          className="btn btn-link px-0 mb-3"
          onClick={() => navigate(-1)}
        >
          <i className="bi bi-arrow-left me-2" />
          Back
        </button>
        <h2 className="mb-4">My Wishlist</h2>
        <p className="text-muted mb-3">Your wishlist is empty.</p>
        <Link to="/products" className="btn btn-primary">
          Browse Products
        </Link>
      </div>
    );
  }

  return (
    <div className="container my-4">
      <button
        type="button"
        className="btn btn-link px-0 mb-3"
        onClick={() => navigate(-1)}
      >
        <i className="bi bi-arrow-left me-2" />
        Back
      </button>
      <h2 className="mb-4">My Wishlist</h2>
      <div className="row g-4">
        {items.map((item) => (
          <div key={item.id} className="col-12 col-md-4 col-lg-3">
            <div className="product-card h-100">
              <Link
                to={`/products/${item.product.id}`}
                className="text-decoration-none text-dark"
              >
                <div className="position-relative">
                  <img
                    src={resolveImageUrl(
                      item.product.image,
                      `https://picsum.photos/400/250?random=${item.product.id}`,
                    )}
                    className="product-image"
                    alt={item.product.name}
                    onError={(e) => {
                      e.currentTarget.src = `https://picsum.photos/400/250?random=${item.product.id}`;
                    }}
                  />
                  <span
                    className={`product-badge ${
                      item.product.stock > 0 ? 'badge-ready' : 'badge-preorder'
                    }`}
                  >
                    {item.product.stock > 0 ? 'Ready Stock' : 'Pre-Order'}
                  </span>
                </div>
                <div className="p-3">
                  <h6 className="mb-1 small">{item.product.name}</h6>
                  <p className="mb-1 small text-muted">
                    {item.product.category?.name || 'Figure'}
                  </p>
                  <p className="product-price mb-2">
                    IDR {Number(item.product.price || 0).toLocaleString('id-ID')}
                  </p>
                </div>
              </Link>
              <div className="px-3 pb-3">
                <button
                  type="button"
                  className="btn btn-outline-danger w-100"
                  onClick={() => removeItem(item.id)}
                >
                  <i className="bi bi-heart-fill me-2" />
                  Remove from Wishlist
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

export default WishlistPage;



