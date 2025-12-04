import { useEffect, useState } from 'react';
import { Link, useSearchParams, useNavigate } from 'react-router-dom';
import { api } from '../../api/client.js';
import { resolveImageUrl } from '../../utils/images.js';

function AdminProductsPage() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [searchParams, setSearchParams] = useSearchParams();
  const navigate = useNavigate();

  const page = Number(searchParams.get('page') || 1);
  const search = searchParams.get('search') || '';
  const status = searchParams.get('status') || '';
  const sort = searchParams.get('sort') || 'new';

  const [searchInput, setSearchInput] = useState(search);
  const [statusInput, setStatusInput] = useState(status);
  const [sortInput, setSortInput] = useState(sort);

  useEffect(() => {
    async function load() {
      setLoading(true);
      try {
        const res = await api.get('/admin/products', {
          params: { page, search, status, sort },
        });
        setData(res.data);
      } finally {
        setLoading(false);
      }
    }
    load();
  }, [page, search, status, sort]);

  const updateParams = (next) => {
    const newParams = new URLSearchParams(searchParams);
    Object.entries(next).forEach(([key, value]) => {
      if (value === '' || value == null) {
        newParams.delete(key);
      } else {
        newParams.set(key, String(value));
      }
    });
    newParams.delete('page');
    setSearchParams(newParams);
  };

  if (loading && !data) {
    return (
      <div className="container my-5">
        <p>Loading products...</p>
      </div>
    );
  }

  const products = data?.products?.data || [];
  const meta = data?.products || {};

  return (
    <div className="container my-5">
      {data?.successMessage && (
        <div className="alert alert-success alert-dismissible fade show" role="alert">
          {data.successMessage}
          <button
            type="button"
            className="btn-close"
            data-bs-dismiss="alert"
            aria-label="Close"
          />
        </div>
      )}
      {data?.errorMessage && (
        <div className="alert alert-danger alert-dismissible fade show" role="alert">
          {data.errorMessage}
          <button
            type="button"
            className="btn-close"
            data-bs-dismiss="alert"
            aria-label="Close"
          />
        </div>
      )}

      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1 className="section-title mb-0">All Products</h1>
        <Link to="/admin/products/add" className="btn btn-primary">
          <i className="bi bi-plus-circle me-2" />
          Add Product
        </Link>
      </div>

      <div className="card mb-4">
        <div className="card-body">
          <form
            className="row g-3"
            onSubmit={(e) => {
              e.preventDefault();
              updateParams({
                search: searchInput.trim(),
                status: statusInput,
                sort: sortInput,
              });
            }}
          >
            <div className="col-md-4">
              <input
                type="text"
                className="form-control"
                placeholder="Search by ID, name, status"
                value={searchInput}
                onChange={(e) => setSearchInput(e.target.value)}
              />
            </div>
            <div className="col-md-3">
              <select
                className="form-select"
                value={statusInput}
                onChange={(e) => setStatusInput(e.target.value)}
              >
                <option value="">All Status</option>
                <option value="in_stock">In Stock</option>
                <option value="out_of_stock">Out of Stock</option>
                <option value="low_stock">Low Stock</option>
              </select>
            </div>
            <div className="col-md-3">
              <select
                className="form-select"
                value={sortInput}
                onChange={(e) => setSortInput(e.target.value)}
              >
                <option value="new">New Order</option>
                <option value="old">Old Order</option>
                <option value="name_asc">Name A-Z</option>
                <option value="name_desc">Name Z-A</option>
                <option value="price_asc">Price Low-High</option>
                <option value="price_desc">Price High-Low</option>
              </select>
            </div>
            <div className="col-md-2">
              <button type="submit" className="btn btn-outline-primary w-100">
                <i className="bi bi-funnel me-2" />
                Filter
              </button>
            </div>
          </form>
        </div>
      </div>

      <div className="card">
        <div className="card-body">
          <div className="table-responsive">
            <table className="product-table w-100">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Price</th>
                  <th>Cost</th>
                  <th>Stock</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                {products.length === 0 && (
                  <tr>
                    <td colSpan={6} className="text-center py-5">
                      <p className="text-muted mb-0">No products found</p>
                    </td>
                  </tr>
                )}
                {products.map((product) => (
                  <tr key={product.id}>
                    <td>
                      <div className="d-flex align-items-center">
                        <img
                          src={resolveImageUrl(product.image)}
                          alt={product.name}
                          className="product-image-small me-3"
                          onError={(e) => {
                            e.target.src =
                              'https://via.placeholder.com/60x60?text=No+Image';
                          }}
                        />
                        <div>
                          <div className="fw-bold">SKU: {product.id}</div>
                          <small className="text-muted">
                            Last Updated:{' '}
                            {product.updated_at
                              ? new Date(product.updated_at).toLocaleDateString(
                                  'en-GB',
                                  {
                                    day: '2-digit',
                                    month: 'short',
                                    year: 'numeric',
                                  },
                                )
                              : '-'}
                          </small>
                          <div className="mt-1">
                            <strong>{product.name}</strong>
                            <br />
                            <small className="text-muted">
                              Series: {product.category?.name || 'N/A'}
                              <br />
                              Category: {product.category?.name || 'N/A'}
                            </small>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <strong>
                        IDR{' '}
                        {Number(product.price || 0).toLocaleString('id-ID')}
                      </strong>
                    </td>
                    <td>
                      <strong>
                        IDR{' '}
                        {Number(product.cost || 0).toLocaleString('id-ID')}
                      </strong>
                    </td>
                    <td>
                      <strong>{product.stock}</strong>
                    </td>
                    <td>
                      {product.stock > 10 ? (
                        <span className="badge-status badge-in-stock">
                          In Stock
                        </span>
                      ) : product.stock > 0 ? (
                        <span className="badge-status badge-low-stock">
                          Low Stock
                        </span>
                      ) : (
                        <span className="badge-status badge-out-of-stock">
                          Out of Stock
                        </span>
                      )}
                    </td>
                    <td>
                      <div className="btn-group">
                        <button
                          type="button"
                          className="btn btn-sm btn-outline-primary"
                          onClick={() =>
                            navigate(`/admin/products/${product.id}/edit`)
                          }
                        >
                          Edit Details
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="mt-4 d-flex justify-content-center gap-2">
            <button
              type="button"
              className="btn btn-outline-primary"
              disabled={!meta.prev_page_url}
              onClick={() => {
                if (!meta.prev_page_url) return;
                updateParams({ page: page - 1 });
              }}
            >
              Previous
            </button>

            <span className="align-self-center px-3">
              Page {meta.current_page || 1} of {meta.last_page || 1}
            </span>

            <button
              type="button"
              className="btn btn-outline-primary"
              disabled={!meta.next_page_url}
              onClick={() => {
                if (!meta.next_page_url) return;
                updateParams({ page: page + 1 });
              }}
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default AdminProductsPage;



