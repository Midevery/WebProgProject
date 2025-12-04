import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { api } from '../api/client.js';
import { useCart } from '../contexts/CartContext.jsx';

function CartPage() {
  const [items, setItems] = useState([]);
  const [selected, setSelected] = useState(new Set());
  const [qty, setQty] = useState({});
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();
  const { refreshCartCount } = useCart();

  useEffect(() => {
    async function load() {
      setLoading(true);
      const res = await api.get('/cart');
      const arr = res.data.items || [];
      setItems(arr);
      setSelected(new Set(arr.map((c) => c.id)));
      setQty(
        Object.fromEntries(arr.map((c) => [c.id, c.quantity])),
      );
      setLoading(false);
    }
    load();
  }, []);

  const toggleSelect = (id) => {
    setSelected((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  };

  const updateQtyLocal = (id, newQty) => {
    setQty((prev) => ({ ...prev, [id]: newQty }));
  };

  const updateQtyServer = async (id, newQty) => {
    await api.put(`/cart/${id}`, { quantity: newQty });
  };

  const removeItem = async (id) => {
    try {
      await api.delete(`/cart/${id}`);
      setItems((prev) => prev.filter((c) => c.id !== id));
      setSelected((prev) => {
        const next = new Set(prev);
        next.delete(id);
        return next;
      });
      await refreshCartCount();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to remove item');
    }
  };

  const selectedItems = items.filter((c) => selected.has(c.id));
  const selectedCount = selectedItems.reduce(
    (sum, c) => sum + (qty[c.id] || c.quantity),
    0,
  );
  const selectedSubtotal = selectedItems.reduce(
    (sum, c) => sum + (c.product.price || 0) * (qty[c.id] || c.quantity),
    0,
  );

  const proceedToPayment = () => {
    const selectedIds = selectedItems.map((c) => c.id);
    const params = new URLSearchParams();
    selectedIds.forEach((id) => params.append('selected[]', id));
    navigate(`/payment?${params.toString()}`);
  };

  if (loading) {
    return (
      <div className="container my-4">
        <p>Loading cart...</p>
      </div>
    );
  }

  if (items.length === 0) {
    return (
      <div className="container my-4">
        <div className="card">
          <div className="card-body text-center py-5">
            <i className="bi bi-cart-x" style={{ fontSize: '4rem', color: '#ccc' }} />
            <p className="text-muted mt-3">Your cart is empty</p>
            <Link to="/products" className="btn btn-primary">
              Start Shopping
            </Link>
          </div>
        </div>
      </div>
    );
  }

  const allSelected = selected.size === items.length && items.length > 0;

  const toggleAll = () => {
    if (allSelected) {
      setSelected(new Set());
    } else {
      setSelected(new Set(items.map((c) => c.id)));
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
      <h2 className="mb-4">Your Cart</h2>
      <p className="text-muted mb-3">
        Select the items you want to purchase now. You can adjust the quantity
        directly from your cart.
      </p>

      <div className="row">
        <div className="col-lg-8">
          {items.map((cart) => (
            <div key={cart.id} className="card mb-3">
              <div className="card-body">
                <div className="row align-items-center g-3">
                  <div className="col-md-1 col-2 text-center">
                    <input
                      type="checkbox"
                      className="form-check-input"
                      checked={selected.has(cart.id)}
                      onChange={() => toggleSelect(cart.id)}
                    />
                  </div>
                  <div className="col-md-2 col-4">
                    <img
                      src={
                        cart.product.image
                          ? `/${cart.product.image}`
                          : `https://picsum.photos/150/150?random=${cart.product.id}`
                      }
                      className="img-fluid rounded"
                      alt={cart.product.name}
                      onError={(e) => {
                        e.currentTarget.src = `https://picsum.photos/150/150?random=${cart.product.id}`;
                      }}
                    />
                  </div>
                  <div className="col-md-5">
                    <span
                      className={`product-badge ${
                        cart.product.stock > 0 ? 'badge-ready' : 'badge-preorder'
                      }`}
                    >
                      {cart.product.stock > 0 ? 'Ready Stock' : 'Pre-Order'}
                    </span>
                    <h5 className="mt-2">{cart.product.name}</h5>
                    <p className="text-muted mb-1">Poster (60 x 40 cm)</p>
                    <p className="product-price mb-0">
                      IDR {cart.product.price?.toLocaleString('id-ID')}
                    </p>
                  </div>
                  <div className="col-md-4 text-md-end">
                    <div
                      className="input-group input-group-sm mb-2"
                      style={{ maxWidth: 200, marginLeft: 'auto' }}
                    >
                      <button
                        className="btn btn-outline-secondary"
                        type="button"
                        onClick={async () => {
                          const next = Math.max(1, (qty[cart.id] || cart.quantity) - 1);
                          updateQtyLocal(cart.id, next);
                          await updateQtyServer(cart.id, next);
                          await refreshCartCount();
                        }}
                      >
                        -
                      </button>
                      <input
                        type="number"
                        className="form-control text-center"
                        value={qty[cart.id] || cart.quantity}
                        min={1}
                        onChange={(e) => {
                          const next = Math.max(1, Number(e.target.value) || 1);
                          updateQtyLocal(cart.id, next);
                        }}
                        onBlur={async (e) => {
                          const next = Math.max(1, Number(e.target.value) || 1);
                          await updateQtyServer(cart.id, next);
                          await refreshCartCount();
                        }}
                      />
                      <button
                        className="btn btn-outline-secondary"
                        type="button"
                        onClick={async () => {
                          const next = (qty[cart.id] || cart.quantity) + 1;
                          updateQtyLocal(cart.id, next);
                          await updateQtyServer(cart.id, next);
                          await refreshCartCount();
                        }}
                      >
                        +
                      </button>
                    </div>
                    <div className="d-flex flex-wrap gap-2 justify-content-end mt-2">
                      <Link
                        to={`/products/${cart.product.id}`}
                        className="btn btn-sm btn-primary"
                      >
                        Go to Detail
                      </Link>
                      <button
                        type="button"
                        className="btn btn-sm btn-outline-danger"
                        onClick={() => removeItem(cart.id)}
                      >
                        Remove
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>

        <div className="col-lg-4">
          <div className="card sticky-top" style={{ top: 90 }}>
            <div className="card-body">
              <div className="d-flex justify-content-between align-items-center mb-2">
                <h5 className="card-title mb-0">Order Summary</h5>
                <button
                  type="button"
                  className="btn btn-link btn-sm p-0"
                  onClick={toggleAll}
                >
                  {allSelected ? 'Deselect All' : 'Select All'}
                </button>
              </div>
              <p className="text-muted small">
                Only selected items will proceed to payment.
              </p>
              <hr />
              <div className="d-flex justify-content-between mb-2">
                <span>Selected Items:</span>
                <strong>{selectedCount}</strong>
              </div>
              <div className="d-flex justify-content-between mb-3">
                <span>Subtotal:</span>
                <strong>
                  IDR {selectedSubtotal.toLocaleString('id-ID')}
                </strong>
              </div>
              <button
                type="button"
                className="btn btn-primary w-100"
                disabled={selected.size === 0}
                onClick={proceedToPayment}
              >
                Purchase Selected
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default CartPage;



