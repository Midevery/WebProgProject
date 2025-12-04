import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../../api/client.js';
import { resolveImageUrl } from '../../utils/images.js';

function AdminEditProductPage() {
  const { id } = useParams();
  const navigate = useNavigate();

  const [artists, setArtists] = useState([]);
  const [categories, setCategories] = useState([]);
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [errors, setErrors] = useState([]);
  const [success, setSuccess] = useState('');
  const [imagePreview, setImagePreview] = useState('');

  const [form, setForm] = useState({
    artist_id: '',
    category_id: '',
    name: '',
    price: '',
    cost: '',
    stock: '',
    description: '',
    image: null,
  });

  useEffect(() => {
    async function load() {
      try {
        setLoading(true);
        const res = await api.get(`/admin/products/${id}/edit`);
        const { product: prod, artists: art, categories: cats } = res.data;
        setProduct(prod);
        setArtists(art || []);
        setCategories(cats || []);
        setForm({
          artist_id: prod.artist_id || '',
          category_id: prod.category_id || '',
          name: prod.name || '',
          price: prod.price ?? '',
          cost: prod.cost ?? '',
          stock: prod.stock ?? '',
          description: prod.description || '',
          image: null,
        });
        setImagePreview(resolveImageUrl(prod.image));
      } finally {
        setLoading(false);
      }
    }
    load();
  }, [id]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleImageChange = (e) => {
    const file = e.target.files?.[0] || null;
    setForm((prev) => ({ ...prev, image: file }));
    if (file) {
      const reader = new FileReader();
      reader.onload = (ev) => {
        if (typeof ev.target?.result === 'string') {
          setImagePreview(ev.target.result);
        }
      };
      reader.readAsDataURL(file);
    } else if (product?.image) {
      setImagePreview(resolveImageUrl(product.image));
    } else {
      setImagePreview('');
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors([]);
    setSuccess('');
    setSubmitting(true);
    try {
      const fd = new FormData();
      fd.append('artist_id', form.artist_id);
      fd.append('category_id', form.category_id);
      fd.append('name', form.name);
      fd.append('price', form.price);
      fd.append('cost', form.cost);
      fd.append('stock', form.stock);
      fd.append('description', form.description);
      if (form.image) {
        fd.append('image', form.image);
      }
      fd.append('_method', 'PUT');
      await api.post(`/admin/products/${id}`, fd);
      setSuccess('Product updated successfully.');
      setTimeout(() => navigate('/admin/products'), 800);
    } catch (err) {
      if (err.response?.status === 422 && err.response.data?.errors) {
        const arr = Object.values(err.response.data.errors).flat();
        setErrors(arr);
      } else {
        setErrors([err.response?.data?.message || 'Failed to update product.']);
      }
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return (
      <div className="container my-5">
        <p>Loading...</p>
      </div>
    );
  }

  if (!product) {
    return (
      <div className="container my-5">
        <p>Product not found.</p>
      </div>
    );
  }

  return (
    <div className="container my-5">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1 className="section-title mb-0">Edit Product</h1>
        <button
          type="button"
          className="btn btn-outline-primary"
          onClick={() => navigate('/admin/products')}
        >
          <i className="bi bi-arrow-left me-2" />
          Back
        </button>
      </div>

      <div className="row">
        <div className="col-md-3">
          <div className="card">
            <div className="card-body">
              <h6 className="fw-bold mb-3">Quick Navigation</h6>
              <ul className="list-unstyled">
                <li className="mb-2">
                  <a href="#product-info" className="text-decoration-none">
                    Product Information
                  </a>
                </li>
                <li className="mb-2">
                  <a href="#upload-media" className="text-decoration-none">
                    Upload Media
                  </a>
                </li>
                <li className="mb-2">
                  <a href="#pricing" className="text-decoration-none">
                    Pricing &amp; Inventory
                  </a>
                </li>
                <li className="mb-2">
                  <a href="#description" className="text-decoration-none">
                    Product Description
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div className="col-md-9">
          <div className="card">
            <div className="card-body">
              {success && (
                <div className="alert alert-success alert-dismissible fade show" role="alert">
                  {success}
                  <button
                    type="button"
                    className="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"
                  />
                </div>
              )}
              {errors.length > 0 && (
                <div className="alert alert-danger alert-dismissible fade show" role="alert">
                  <ul className="mb-0">
                    {errors.map((errMsg) => (
                      <li key={errMsg}>{errMsg}</li>
                    ))}
                  </ul>
                  <button
                    type="button"
                    className="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"
                  />
                </div>
              )}

              <form onSubmit={handleSubmit} encType="multipart/form-data">
                <div id="product-info" className="mb-4">
                  <h5 className="mb-3">Product Information</h5>

                  <div className="mb-3">
                    <label className="form-label">
                      Illustrator Name <span className="text-danger">*</span>
                    </label>
                    <select
                      name="artist_id"
                      className="form-select"
                      required
                      value={form.artist_id}
                      onChange={handleChange}
                    >
                      <option value="">Select Illustrator</option>
                      {artists.map((artist) => (
                        <option key={artist.id} value={artist.id}>
                          {artist.name} ({artist.username})
                        </option>
                      ))}
                    </select>
                  </div>

                  <div className="mb-3">
                    <label className="form-label">
                      Product Title <span className="text-danger">*</span>
                    </label>
                    <input
                      type="text"
                      name="name"
                      className="form-control"
                      required
                      value={form.name}
                      onChange={handleChange}
                    />
                  </div>

                  <div className="mb-3">
                    <label className="form-label">
                      Category <span className="text-danger">*</span>
                    </label>
                    <select
                      name="category_id"
                      className="form-select"
                      required
                      value={form.category_id}
                      onChange={handleChange}
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

                <div id="upload-media" className="mb-4">
                  <h5 className="mb-3">Upload Media</h5>
                  {imagePreview && (
                    <div className="mb-3">
                      <img
                        src={imagePreview}
                        alt={form.name}
                        style={{ maxWidth: '200px', borderRadius: '5px' }}
                        onError={(e) => {
                          e.target.src =
                            'https://via.placeholder.com/200x200?text=No+Image';
                        }}
                      />
                    </div>
                  )}
                  <div className="mb-3">
                    <label className="form-label">Product Image</label>
                    <input
                      type="file"
                      name="image"
                      className="form-control"
                      accept="image/*"
                      onChange={handleImageChange}
                    />
                    <small className="text-muted">
                      Leave empty to keep current image
                    </small>
                  </div>
                </div>

                <div id="pricing" className="mb-4">
                  <h5 className="mb-3">Pricing &amp; Inventory</h5>
                  <div className="row">
                    <div className="col-md-4 mb-3">
                      <label className="form-label">
                        Price (IDR) <span className="text-danger">*</span>
                      </label>
                      <input
                        type="number"
                        name="price"
                        className="form-control"
                        min="0"
                        step="0.01"
                        required
                        value={form.price}
                        onChange={handleChange}
                      />
                      <small className="text-muted">Selling price to customer</small>
                    </div>
                    <div className="col-md-4 mb-3">
                      <label className="form-label">
                        Cost/Modal (IDR) <span className="text-danger">*</span>
                      </label>
                      <input
                        type="number"
                        name="cost"
                        className="form-control"
                        min="0"
                        step="0.01"
                        required
                        value={form.cost}
                        onChange={handleChange}
                      />
                      <small className="text-muted">Product cost/modal</small>
                    </div>
                    <div className="col-md-4 mb-3">
                      <label className="form-label">
                        Stock <span className="text-danger">*</span>
                      </label>
                      <input
                        type="number"
                        name="stock"
                        className="form-control"
                        min="0"
                        required
                        value={form.stock}
                        onChange={handleChange}
                      />
                    </div>
                  </div>
                </div>

                <div id="description" className="mb-4">
                  <h5 className="mb-3">Product Description</h5>
                  <div className="mb-3">
                    <label className="form-label">
                      Description <span className="text-danger">*</span>
                    </label>
                    <textarea
                      name="description"
                      className="form-control"
                      rows={5}
                      required
                      value={form.description}
                      onChange={handleChange}
                    />
                  </div>
                </div>

                <div className="d-flex justify-content-end">
                  <button
                    type="button"
                    className="btn btn-outline-secondary me-2"
                    onClick={() => navigate('/admin/products')}
                  >
                    Cancel
                  </button>
                  <button type="submit" className="btn btn-primary" disabled={submitting}>
                    {submitting ? 'Saving...' : 'Update Product'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default AdminEditProductPage;


