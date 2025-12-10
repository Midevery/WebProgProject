import { useEffect, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { api } from '../api/client.js';
import { resolveImageUrl } from '../api/media.js';

function ProductsListPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [sellerInput, setSellerInput] = useState('');
  const [minPriceInput, setMinPriceInput] = useState('');
  const [maxPriceInput, setMaxPriceInput] = useState('');
  const [pagination, setPagination] = useState(null);

  const availability = searchParams.get('availability') || '';
  const min_price = searchParams.get('min_price') || '';
  const max_price = searchParams.get('max_price') || '';
  const seller = searchParams.get('seller') || '';

  useEffect(() => {
    async function load() {
      setLoading(true);
      try {
        const paramsObj = {};
        searchParams.forEach((value, key) => {
          if (key === 'categories[]') {
            if (!paramsObj[key]) paramsObj[key] = [];
            paramsObj[key].push(value);
          } else {
            paramsObj[key] = value;
          }
        });
        const res = await api.get('/products', {
          params: paramsObj,
        });
        setProducts(res.data.products?.data || []);
        setCategories(res.data.categories || []);
        setPagination(res.data.products || null);
      } finally {
        setLoading(false);
      }
    }
    setSellerInput(seller);
    setMinPriceInput(min_price);
    setMaxPriceInput(max_price);
    load();
  }, [searchParams, seller]);

  const updateFilter = (key, value) => {
    const next = new URLSearchParams(searchParams);
    if (value) next.set(key, value);
    else next.delete(key);
    setSearchParams(next);
  };

  const handlePageChange = (page) => {
    const next = new URLSearchParams(searchParams);
    if (page <= 1) {
      next.delete('page');
    } else {
      next.set('page', page);
    }
    setSearchParams(next);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  if (loading) {
    return (
      <div className="container my-4">
        <p>Loading products...</p>
      </div>
    );
  }

  return (
    <div className="container my-4">
      <h2 className="mb-4">Display All Product</h2>
      <div className="row">
        <div className="col-lg-9">
          <h3 className="section-title mb-4">Our Products</h3>
          <div className="row g-4">
            {products.length === 0 && (
              <div className="col-12">
                <p className="text-center text-muted">No products found.</p>
              </div>
            )}
            {products.map((product) => (
              <div key={product.id} className="col-6 col-md-4 col-lg-3">
                <div className="product-card">
                  <Link
                    to={`/products/${product.id}`}
                    className="text-decoration-none text-dark"
                  >
                    <div className="position-relative">
                      <img
                        src={resolveImageUrl(
                          product.image,
                          `https://picsum.photos/200/200?random=${product.id}`,
                        )}
                        className="product-image"
                        alt={product.name}
                        onError={(e) => {
                          e.currentTarget.src = `https://picsum.photos/200/200?random=${product.id}`;
                        }}
                      />
                      <span
                        className={`product-badge ${
                          product.stock > 0 ? 'badge-ready' : 'badge-preorder'
                        }`}
                      >
                        {product.stock > 0 ? 'Ready Stock' : 'Pre-Order'}
                      </span>
                    </div>
                    <div className="p-3">
                      <h6 className="mb-1">{product.name}</h6>
                      <p className="text-muted small mb-1">
                        {product.category?.name || 'Uncategorized'}
                      </p>
                      <p className="product-price mb-0">
                        IDR {Number(product.price || 0).toLocaleString('id-ID')}
                      </p>
                    </div>
                  </Link>
                </div>
              </div>
            ))}
          </div>
          {pagination && pagination.last_page > 1 && (
            <nav className="d-flex justify-content-center mt-4">
              <ul className="pagination">
                <li className={`page-item ${pagination.current_page === 1 ? 'disabled' : ''}`}>
                  <button
                    className="page-link"
                    onClick={() => handlePageChange(pagination.current_page - 1)}
                    disabled={pagination.current_page === 1}
                  >
                    Previous
                  </button>
                </li>
                {Array.from({ length: pagination.last_page }, (_, idx) => idx + 1).map((page) => (
                  <li
                    key={page}
                    className={`page-item ${pagination.current_page === page ? 'active' : ''}`}
                  >
                    <button className="page-link" onClick={() => handlePageChange(page)}>
                      {page}
                    </button>
                  </li>
                ))}
                <li
                  className={`page-item ${
                    pagination.current_page === pagination.last_page ? 'disabled' : ''
                  }`}
                >
                  <button
                    className="page-link"
                    onClick={() => handlePageChange(pagination.current_page + 1)}
                    disabled={pagination.current_page === pagination.last_page}
                  >
                    Next
                  </button>
                </li>
              </ul>
            </nav>
          )}
        </div>

        <div className="col-lg-3">
          <h3 className="section-title mb-4">Filter</h3>
          <div className="card mb-3">
            <div className="card-body">
              <h6 className="card-title">Availability In Shop</h6>
              <div className="form-check">
                <input
                  className="form-check-input"
                  type="radio"
                  name="availability"
                  value="ready"
                  id="ready"
                  checked={availability === 'ready'}
                  onChange={() => updateFilter('availability', 'ready')}
                />
                <label className="form-check-label" htmlFor="ready">
                  Ready Stock
                </label>
              </div>
              <div className="form-check">
                <input
                  className="form-check-input"
                  type="radio"
                  name="availability"
                  value="preorder"
                  id="preorder"
                  checked={availability === 'preorder'}
                  onChange={() => updateFilter('availability', 'preorder')}
                />
                <label className="form-check-label" htmlFor="preorder">
                  Pre Order
                </label>
              </div>
              <div className="form-check">
                <input
                  className="form-check-input"
                  type="radio"
                  name="availability"
                  id="all_availability"
                  checked={!availability}
                  onChange={() => updateFilter('availability', '')}
                />
                <label className="form-check-label" htmlFor="all_availability">
                  All
                </label>
              </div>
            </div>
          </div>

          <div className="card mb-3">
            <div className="card-body">
              <h6 className="card-title">Price Range</h6>
              <div className="mb-2">
                <label className="form-label small">Min</label>
                <div className="input-group">
                  <span className="input-group-text">IDR</span>
                  <input
                    type="number"
                    className="form-control"
                    value={minPriceInput}
                    onChange={(e) => setMinPriceInput(e.target.value)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter') {
                        e.preventDefault();
                        updateFilter('min_price', minPriceInput.trim());
                      }
                    }}
                  />
                </div>
              </div>
              <div className="mb-2">
                <label className="form-label small">Max</label>
                <div className="input-group">
                  <span className="input-group-text">IDR</span>
                  <input
                    type="number"
                    className="form-control"
                    value={maxPriceInput}
                    onChange={(e) => setMaxPriceInput(e.target.value)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter') {
                        e.preventDefault();
                        updateFilter('max_price', maxPriceInput.trim());
                      }
                    }}
                  />
                </div>
              </div>
            </div>
          </div>

          <div className="card mb-3">
            <div className="card-body">
              <div className="d-flex justify-content-between align-items-center mb-2">
                <h6 className="card-title mb-0">Product Categories</h6>
              </div>
              <p className="text-muted small mb-3">
                Select categories to filter products. You can check or uncheck any category.
              </p>
              <div className="category-checkboxes">
                {categories.map((cat) => {
                  const selected = searchParams.getAll('categories[]');
                  const checked = selected.includes(String(cat.id));
                  return (
                    <div key={cat.id} className="form-check">
                      <input
                        className="form-check-input"
                        type="checkbox"
                        id={`cat${cat.id}`}
                        checked={checked}
                        onChange={() => {
                          const next = new URLSearchParams(
                            searchParams.toString(),
                          );
                          const all = next.getAll('categories[]');
                          next.delete('categories[]');
                          if (!checked) {
                            // Add category if not checked
                            all.push(String(cat.id));
                          } else {
                            // Remove category if checked (uncheck)
                            const filtered = all.filter((id) => id !== String(cat.id));
                            all.splice(0, all.length, ...filtered);
                          }
                          all
                            .filter(Boolean)
                            .forEach((id) => next.append('categories[]', id));
                          setSearchParams(next);
                        }}
                      />
                      <label
                        className="form-check-label"
                        htmlFor={`cat${cat.id}`}
                      >
                        {cat.name}
                      </label>
                    </div>
                  );
                })}
              </div>
            </div>
          </div>

          <div className="card mb-3">
            <div className="card-body">
              <h6 className="card-title">Search Seller</h6>
              <input
                type="text"
                className="form-control mb-2"
                value={sellerInput}
                onChange={(e) => setSellerInput(e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === 'Enter') {
                    e.preventDefault();
                    updateFilter('seller', sellerInput.trim());
                  }
                }}
                placeholder="Input Seller name then press Enter"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default ProductsListPage;



