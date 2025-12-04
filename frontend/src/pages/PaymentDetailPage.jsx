import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../api/client.js';

function PaymentDetailPage() {
  const { orderId } = useParams();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [processing, setProcessing] = useState(false);
  const [paymentMethod, setPaymentMethod] = useState('transfer');
  const navigate = useNavigate();

  useEffect(() => {
    async function load() {
      setLoading(true);
      try {
        const res = await api.get(`/payment/${orderId}`);
        setData(res.data);
      } catch {
        navigate('/cart');
      } finally {
        setLoading(false);
      }
    }
    load();
  }, [orderId, navigate]);

  const handleProcess = async () => {
    setProcessing(true);
    try {
      await api.post(`/payment/${orderId}/process`, {
        payment_method: paymentMethod,
      });
      navigate('/shipping');
    } catch (err) {
      alert(err.response?.data?.message || 'Payment process failed.');
    } finally {
      setProcessing(false);
    }
  };

  if (loading || !data) {
    return (
      <div className="container my-4">
        <p>Loading order...</p>
      </div>
    );
  }

  const {
    order,
    shippingDetails,
    shippingPrice,
    subtotal,
    adminFee,
    taxAmount,
    calculatedTotal,
  } = data;

  const totalItems = order.order_items.reduce(
    (sum, item) => sum + item.quantity,
    0,
  );
  const taxRate = subtotal ? (taxAmount / subtotal) * 100 : 0;

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

          <div className="mb-4">
            <h5>Product Price Details</h5>
            <p>
              <strong>Total Item:</strong> {totalItems}
            </p>
            {order.order_items.map((item) => (
              <p key={item.id}>
                {item.quantity} x{' '}
                {item.price.toLocaleString('id-ID')} = IDR{' '}
                {item.subtotal.toLocaleString('id-ID')}
              </p>
            ))}
          </div>

          <div className="mb-4">
            <h5>Shipping Option</h5>
            <p className="mb-1">
              <strong>Method:</strong> {shippingDetails.label}
            </p>
            <p className="mb-1">
              <strong>Shipping Price:</strong> IDR{' '}
              {shippingPrice.toLocaleString('id-ID')}
            </p>
            <p className="mb-2">
              <strong>Estimate:</strong> {shippingDetails.eta}
            </p>
          </div>

          <div className="mb-4">
            <h5>Order Summary</h5>
            <div className="d-flex justify-content-between">
              <span>Subtotal</span>
              <strong>
                IDR {subtotal.toLocaleString('id-ID')}
              </strong>
            </div>
            <div className="d-flex justify-content-between">
              <span>Admin Fee</span>
              <strong>
                IDR {adminFee.toLocaleString('id-ID')}
              </strong>
            </div>
            <div className="d-flex justify-content-between">
              <span>
                Tax ({taxRate.toFixed(0)}
                %)
              </span>
              <strong>
                IDR {taxAmount.toLocaleString('id-ID')}
              </strong>
            </div>
            <div className="d-flex justify-content-between">
              <span>Shipping</span>
              <strong>
                IDR {shippingPrice.toLocaleString('id-ID')}
              </strong>
            </div>
            <hr />
            <div className="d-flex justify-content-between">
              <span>
                <strong>Total Payment:</strong>
              </span>
              <strong>
                IDR {calculatedTotal.toLocaleString('id-ID')}
              </strong>
            </div>
          </div>

          <div className="mb-4">
            <h5>Pay with:</h5>
            <div className="alert alert-info mb-3">
              Card payment is currently unavailable. Please use Transfer or
              Cash.
            </div>
            <div className="btn-group mb-3" role="group">
              <input
                type="radio"
                className="btn-check"
                name="payment_method"
                id="transfer"
                value="transfer"
                checked={paymentMethod === 'transfer'}
                onChange={() => setPaymentMethod('transfer')}
              />
              <label className="btn btn-outline-primary" htmlFor="transfer">
                Transfer
              </label>

              <input
                type="radio"
                className="btn-check"
                name="payment_method"
                id="cash"
                value="cash"
                checked={paymentMethod === 'cash'}
                onChange={() => setPaymentMethod('cash')}
              />
              <label className="btn btn-outline-primary" htmlFor="cash">
                Cash
              </label>
            </div>

            {paymentMethod === 'transfer' && (
              <div className="alert alert-warning">
                <strong>Transfer Instructions:</strong>
                <br />
                Please transfer to:
                <br />
                Bank: BCA
                <br />
                Account Number: 1234567890
                <br />
                Account Name: Kisora Shop
                <br />
                <small className="text-muted">
                  Please include order number in transfer description
                </small>
              </div>
            )}

            {paymentMethod === 'cash' && (
              <div className="alert alert-warning">
                <strong>Cash Payment:</strong>
                <br />
                Please prepare exact cash amount. Payment will be collected upon
                delivery.
              </div>
            )}
          </div>

          <button
            type="button"
            className="btn btn-primary btn-lg"
            onClick={handleProcess}
            disabled={processing}
          >
            {processing ? 'Processing...' : 'Pay'}
          </button>
        </div>
      </div>
    </div>
  );
}

export default PaymentDetailPage;



