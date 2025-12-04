import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../api/client.js';
import { useCart } from '../contexts/CartContext.jsx';

function ProductDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [data, setData] = useState(null);
  const [qty, setQty] = useState(1);
  const [loading, setLoading] = useState(true);
  const { refreshCartCount } = useCart();
  const [userRole, setUserRole] = useState(null);
  const isAdmin = userRole === 'admin';

  useEffect(() => {
    async function load() {
      setLoading(true);
      try {
        const [productRes, meRes] = await Promise.allSettled([
          api.get(`/products/${id}`),
          api.get('/me'),
        ]);

        if (productRes.status === 'fulfilled') {
          setData(productRes.value.data);
        } else {
          navigate('/products');
        }

        if (meRes.status === 'fulfilled') {
          setUserRole(meRes.value.data.user?.role || null);
        }
      } catch {
        navigate('/products');
      } finally {
        setLoading(false);
      }
    }
    load();
  }, [id, navigate]);

  if (loading || !data) {
    return (
      <div className="container my-4">
        <p>Loading product...</p>
      </div>
    );
  }

  const { product, recentlyViewed = [], similarProducts = [] } = data;

  const changeQty = (delta) => {
    setQty((prev) => {
      const next = Math.max(1, prev + delta);
      return next;
    });
  };

  const handleAddToCart = async () => {
    try {
      await api.post('/cart', {
        product_id: product.id,
        quantity: qty,
      });
      await refreshCartCount();
      alert('Product added successfully');
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to add product to cart');
    }
  };

  return (
    <div className="container my-4">
      <button
        type="button"
        className="btn btn-outline-primary mb-3"
        onClick={() => navigate(-1)}
      >
        ← Back
      </button>

      <div className="card mb-4">
        <div className="card-body">
          <div className="row">
            <div className="col-md-6">
              <img
                src={
                  product.image
                    ? `/${product.image}`
                    : `https://picsum.photos/600/800?random=${product.id}`
                }
                className="img-fluid rounded w-100"
                alt={product.name}
                onError={(e) => {
                  e.currentTarget.src = `https://picsum.photos/600/800?random=${product.id}`;
                }}
              />
            </div>
            <div className="col-md-6">
              <span
                className={`product-badge mb-3 ${
                  product.stock > 0 ? 'badge-ready' : 'badge-preorder'
                }`}
              >
                {product.stock > 0 ? 'Ready Stock' : 'Pre-Order'}
              </span>
              <h2>{product.name}</h2>
              <p className="text-muted">Measurement: 60 cm x 80 cm</p>
              {product.artist && (
                <p className="text-muted">
                  Illustrator: {product.artist.name}
                </p>
              )}
              <h3 className="product-price mb-4">
                IDR {product.price?.toLocaleString('id-ID')}
              </h3>

              <div className="mb-3" style={{ maxWidth: 200 }}>
                <label className="form-label">Qty</label>
                <div className="input-group">
                  <button
                    className="btn btn-outline-secondary"
                    type="button"
                    onClick={() => changeQty(-1)}
                  >
                    -
                  </button>
                  <input
                    type="number"
                    className="form-control text-center"
                    value={qty}
                    onChange={(e) =>
                      setQty(Math.max(1, Number(e.target.value) || 1))
                    }
                    min={1}
                  />
                  <button
                    className="btn btn-outline-secondary"
                    type="button"
                    onClick={() => changeQty(1)}
                  >
                    +
                  </button>
                </div>
              </div>

              {!isAdmin && (
                <div className="d-flex gap-2 mb-4">
                  <button
                    type="button"
                    className="btn btn-outline-danger"
                    onClick={() =>
                      api.post('/wishlist', { product_id: product.id })
                    }
                  >
                    Wishlist
                  </button>
                  <button
                    type="button"
                    className="btn btn-primary flex-grow-1"
                    onClick={handleAddToCart}
                  >
                    Add to Cart
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      <div className="card mt-4">
        <div className="card-body">
          <h5 className="card-title">Recently Viewed Item</h5>
          <div className="row g-3">
            {recentlyViewed.map((item) => (
              <div key={item.id} className="col-md-4">
                <div className="product-card">
                  <button
                    type="button"
                    className="text-decoration-none text-dark btn btn-link p-0 w-100 text-start"
                    onClick={() => navigate(`/products/${item.id}`)}
                  >
                    <div className="position-relative">
                      <img
                        src={
                          item.image
                            ? `/${item.image}`
                            : `https://picsum.photos/200/200?random=${item.id}`
                        }
                        className="product-image"
                        alt={item.name}
                        onError={(e) => {
                          e.currentTarget.src = `https://picsum.photos/200/200?random=${item.id}`;
                        }}
                      />
                    </div>
                    <div className="p-2">
                      <h6 className="mb-1 small">{item.name}</h6>
                      <p className="product-price mb-0 small">
                        IDR {item.price?.toLocaleString('id-ID')}
                      </p>
                    </div>
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      <div className="card mt-4">
        <div className="card-body">
          <h5 className="card-title">More Stuff Like this!</h5>
          <div className="row g-3">
            {similarProducts.map((item) => (
              <div key={item.id} className="col-md-4">
                <div className="product-card">
                  <button
                    type="button"
                    className="text-decoration-none text-dark btn btn-link p-0 w-100 text-start"
                    onClick={() => navigate(`/products/${item.id}`)}
                  >
                    <div className="position-relative">
                      <img
                      src={
                        item.image
                          ? `/${item.image}`
                          : `https://picsum.photos/200/200?random=${item.id}`
                      }
                        className="product-image"
                        alt={item.name}
                      onError={(e) => {
                        e.currentTarget.src = `https://picsum.photos/200/200?random=${item.id}`;
                      }}
                      />
                    </div>
                    <div className="p-2">
                      <h6 className="mb-1 small">{item.name}</h6>
                      <p className="product-price mb-0 small">
                        IDR {item.price?.toLocaleString('id-ID')}
                      </p>
                    </div>
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

export default ProductDetailPage;



