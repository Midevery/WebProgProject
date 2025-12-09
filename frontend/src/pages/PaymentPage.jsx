import { useEffect, useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { api } from '../api/client.js';
import { useCart } from '../contexts/CartContext.jsx';
import { resolveImageUrl } from '../api/media.js';

function PaymentPage() {
  const location = useLocation();
  const navigate = useNavigate();
  const [data, setData] = useState(null);
  const [shippingMethod, setShippingMethod] = useState('fast');
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const { refreshCartCount } = useCart();

  const searchParams = new URLSearchParams(location.search);

  useEffect(() => {
    async function load() {
      setLoading(true);
      try {
        const res = await api.get('/payment/preview', {
          params: Object.fromEntries(searchParams.entries()),
        });
        setData(res.data);
        setShippingMethod(res.data.currentShipping || 'fast');
      } catch (err) {
        if (err.response?.status === 401) {
          navigate('/signin');
        } else {
          alert('Cannot load payment preview.');
          navigate('/cart');
        }
      } finally {
        setLoading(false);
      }
    }
    load();
  }, [location.search, navigate]);

  const handleCheckout = async () => {
    setSubmitting(true);
    try {
      const payload = {
        shipping_method: shippingMethod,
        payment_method: 'transfer',
      };
      searchParams.forEach((v, k) => {
        if (k === 'selected[]') {
          if (!payload.selected) payload.selected = [];
          payload.selected.push(v);
        }
      });
      const res = await api.post('/payment/checkout', payload);
      const orderId = res.data.order.id;
      await refreshCartCount();
      navigate(`/payment/${orderId}`);
    } catch (err) {
      alert(err.response?.data?.message || 'Checkout failed.');
    } finally {
      setSubmitting(false);
    }
  };

  if (loading || !data) {
    return (
      <div className="container my-4">
        <p>Loading payment...</p>
      </div>
    );
  }

  const {
    carts = [],
    subtotal,
    adminFee,
    ppn,
    shippingOptions = {},
  } = data;

  const taxRate = subtotal ? ppn / subtotal : 0;
  const taxAmount = subtotal * taxRate;
  const currentShipping = shippingOptions[shippingMethod] || {};
  const shippingPrice = currentShipping.price || 0;
  const total =
    subtotal + adminFee + taxAmount + shippingPrice;

  return (
    <div className="container my-4">
      <button
        type="button"
        className="btn btn-outline-primary mb-3"
        onClick={() => navigate(-1)}
      >
        ← Back
      </button>
      <div className="card">
        <div className="card-body">
          <h3 className="mb-4">Product Price Details</h3>
          <div className="row g-4">
            <div className="col-md-7">
              {carts.map((c) => (
                <div key={c.id} className="card mb-3">
                  <div className="card-body">
                    <div className="row">
                      <div className="col-md-3">
                        <img
                          src={resolveImageUrl(
                            c.product.image,
                            `https://picsum.photos/200/200?random=${c.product.id}`,
                          )}
                          className="img-fluid rounded"
                          alt={c.product.name}
                          onError={(e) => {
                            e.currentTarget.src = `https://picsum.photos/200/200?random=${c.product.id}`;
                          }}
                        />
                      </div>
                      <div className="col-md-9">
                        <span
                          className={`product-badge ${
                            c.product.stock > 0 ? 'badge-ready' : 'badge-preorder'
                          }`}
                        >
                          {c.product.stock > 0 ? 'Ready Stock' : 'Pre-Order'}
                        </span>
                        <h5 className="mt-2">
                          {c.product.name} by{' '}
                          {c.product.artist?.name || 'Unknown'}
                        </h5>
                        <p className="text-muted mb-1">
                          Character: {c.product.name}
                        </p>
                        <p className="product-price mt-2">
                          IDR {Number(c.product.price || 0).toLocaleString('id-ID')}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
            <div className="col-md-5">
              <div className="card h-100">
                <div className="card-body">
                  <h5 className="mb-3">Shipping Method</h5>
                  <p className="text-muted small">
                    Choose a courier speed that fits your schedule.
                  </p>
                  {Object.entries(shippingOptions).map(([key, opt]) => (
                    <div className="form-check mb-2" key={key}>
                      <input
                        className="form-check-input"
                        type="radio"
                        name="shipping_method"
                        id={`ship-${key}`}
                        checked={shippingMethod === key}
                        onChange={() => setShippingMethod(key)}
                      />
                      <label
                        className="form-check-label w-100"
                        htmlFor={`ship-${key}`}
                      >
                        <div className="d-flex justify-content-between">
                          <span>
                            {opt.label}{' '}
                            <small className="text-muted d-block">
                              {opt.eta}
                            </small>
                          </span>
                          <strong>
                            IDR {opt.price.toLocaleString('id-ID')}
                          </strong>
                        </div>
                      </label>
                    </div>
                  ))}

                  <hr />
                  <h5 className="mb-3">Order Summary</h5>
                  <div className="d-flex justify-content-between">
                    <span>
                      Subtotal ({carts.reduce(
                        (sum, c) => sum + (c.quantity || 0),
                        0,
                      )}{' '}
                      items)
                    </span>
                    <strong>
                      IDR {Number(subtotal || 0).toLocaleString('id-ID')}
                    </strong>
                  </div>
                  <div className="d-flex justify-content-between">
                    <span>Admin Fee</span>
                    <strong>
                      IDR {Number(adminFee || 0).toLocaleString('id-ID')}
                    </strong>
                  </div>
                  <div className="d-flex justify-content-between">
                    <span>
                      Tax (
                      {(taxRate * 100).toFixed(0)}
                      %)
                    </span>
                    <strong>
                      IDR {Number(taxAmount || 0).toLocaleString('id-ID')}
                    </strong>
                  </div>
                  <div className="d-flex justify-content-between">
                    <span>
                      Shipping (
                      {currentShipping.label || 'Select option'}
                      )
                    </span>
                    <strong>
                      IDR {Number(shippingPrice || 0).toLocaleString('id-ID')}
                    </strong>
                  </div>
                  <div className="d-flex justify-content-between mt-3">
                    <span>Total</span>
                    <strong>
                      IDR {Number(total || 0).toLocaleString('id-ID')}
                    </strong>
                  </div>
                  <small className="text-muted d-block mt-2">
                    Tax is calculated from the product subtotal before
                    shipping.
                  </small>
                </div>
                <div className="card-footer bg-white border-0">
                  <button
                    type="button"
                    className="btn btn-danger btn-lg w-100"
                    onClick={handleCheckout}
                    disabled={submitting}
                  >
                    {submitting ? 'Processing...' : 'Check Out'}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default PaymentPage;



