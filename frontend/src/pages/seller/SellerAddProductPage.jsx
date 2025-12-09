import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { api } from '../../api/client.js';

function SellerAddProductPage() {
  const [form, setForm] = useState({
    name: '',
    description: '',
    price: '',
    cost: '',
    stock: '',
    category_id: '',
  });
  const [imageFile, setImageFile] = useState(null);
  const [imagePreview, setImagePreview] = useState('');
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const navigate = useNavigate();

  useEffect(() => {
    async function loadCategories() {
      try {
        const res = await api.get('/seller/products/add');
        setCategories(res.data.categories || []);
      } catch (err) {
        setError('Failed to load categories');
      }
    }
    loadCategories();
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleImageChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) {
      setImageFile(null);
      setImagePreview('');
      return;
    }
    setImageFile(file);
    const reader = new FileReader();
    reader.onload = (ev) => {
      if (typeof ev.target?.result === 'string') {
        setImagePreview(ev.target.result);
      }
    };
    reader.readAsDataURL(file);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    try {
      const formData = new FormData();
      formData.append('name', form.name);
      formData.append('description', form.description);
      formData.append('price', form.price);
      formData.append('cost', form.cost);
      formData.append('stock', form.stock);
      formData.append('category_id', form.category_id);
      if (imageFile) {
        formData.append('image', imageFile);
      }

      const res = await api.post('/seller/products', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      setSuccess('Product added successfully!');
      
      window.dispatchEvent(new CustomEvent('productUpdated'));
      
      setTimeout(() => {
        navigate('/seller/dashboard');
      }, 1500);
    } catch (err) {
      const msg =
        err.response?.data?.message ||
        Object.values(err.response?.data?.errors || {})[0]?.[0] ||
        'Failed to add product. Please check your data.';
      setError(msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container my-5">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1 className="section-title mb-0">Add New Product</h1>
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

      <div className="card">
        <div className="card-body">
          <form onSubmit={handleSubmit} encType="multipart/form-data">
            <div className="row">
              <div className="col-md-6 mb-3">
                <label className="form-label">
                  Product Name <span className="text-danger">*</span>
                </label>
                <input
                  type="text"
                  name="name"
                  className="form-control"
                  value={form.name}
                  onChange={handleChange}
                  required
                  placeholder="Enter product name"
                />
              </div>

              <div className="col-md-6 mb-3">
                <label className="form-label">
                  Category <span className="text-danger">*</span>
                </label>
                <select
                  name="category_id"
                  className="form-select"
                  value={form.category_id}
                  onChange={handleChange}
                  required
                >
                  <option value="">Select Category</option>
                  {categories.map((cat) => (
                    <option key={cat.id} value={cat.id}>
                      {cat.name}
                    </option>
                  ))}
                </select>
              </div>
            </div>

            <div className="mb-3">
              <label className="form-label">
                Description <span className="text-danger">*</span>
              </label>
              <textarea
                name="description"
                className="form-control"
                rows="5"
                value={form.description}
                onChange={handleChange}
                required
                placeholder="Enter product description"
              />
            </div>

            <div className="row">
              <div className="col-md-4 mb-3">
                <label className="form-label">
                  Price (IDR) <span className="text-danger">*</span>
                </label>
                <input
                  type="number"
                  name="price"
                  className="form-control"
                  value={form.price}
                  onChange={handleChange}
                  required
                  min="0"
                  step="0.01"
                  placeholder="0"
                />
              </div>

              <div className="col-md-4 mb-3">
                <label className="form-label">
                  Cost (IDR) <span className="text-danger">*</span>
                </label>
                <input
                  type="number"
                  name="cost"
                  className="form-control"
                  value={form.cost}
                  onChange={handleChange}
                  required
                  min="0"
                  step="0.01"
                  placeholder="0"
                />
              </div>

              <div className="col-md-4 mb-3">
                <label className="form-label">
                  Stock <span className="text-danger">*</span>
                </label>
                <input
                  type="number"
                  name="stock"
                  className="form-control"
                  value={form.stock}
                  onChange={handleChange}
                  required
                  min="0"
                  placeholder="0"
                />
              </div>
            </div>

            <div className="mb-3">
              <label className="form-label">
                Product Image <span className="text-danger">*</span>
              </label>
              <div className="mb-2">
                {imagePreview && (
                  <img
                    src={imagePreview}
                    alt="Preview"
                    style={{
                      maxWidth: '200px',
                      maxHeight: '200px',
                      objectFit: 'cover',
                      borderRadius: '8px',
                      marginBottom: '10px',
                    }}
                  />
                )}
              </div>
              <input
                type="file"
                name="image"
                className="form-control"
                accept="image/*"
                onChange={handleImageChange}
                required
              />
              <small className="text-muted">
                Accepted formats: JPEG, PNG, JPG, GIF (Max: 2MB)
              </small>
            </div>

            <div className="d-flex gap-2">
              <button
                type="submit"
                className="btn btn-primary"
                disabled={loading}
              >
                {loading ? (
                  <>
                    <span
                      className="spinner-border spinner-border-sm me-2"
                      role="status"
                      aria-hidden="true"
                    />
                    Adding...
                  </>
                ) : (
                  <>
                    <i className="bi bi-check-circle me-2" />
                    Add Product
                  </>
                )}
              </button>
              <button
                type="button"
                className="btn btn-secondary"
                onClick={() => navigate('/seller/dashboard')}
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}

export default SellerAddProductPage;

