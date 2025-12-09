import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../api/client.js';
import { resolveImageUrl } from '../api/media.js';
import { useCart } from '../contexts/CartContext.jsx';

function ProductDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [data, setData] = useState(null);
  const [qty, setQty] = useState(1);
  const [loading, setLoading] = useState(true);
  const { refreshCartCount } = useCart();
  const [userRole, setUserRole] = useState(null);
  const [currentUser, setCurrentUser] = useState(null);
  const [commentText, setCommentText] = useState('');
  const [submittingComment, setSubmittingComment] = useState(false);
  const [commentSuccess, setCommentSuccess] = useState('');
  const [commentError, setCommentError] = useState('');
  const [deleteConfirm, setDeleteConfirm] = useState(null);
  const [isWishlisted, setIsWishlisted] = useState(false);
  const [isInCart, setIsInCart] = useState(false);

  useEffect(() => {
    async function load() {
      setLoading(true);
      try {
        const [productRes, meRes] = await Promise.allSettled([
          api.get(`/products/${id}`),
          api.get('/me'),
        ]);

        if (productRes.status === 'fulfilled') {
          const productData = productRes.value.data;
          setData(productData);
          setIsWishlisted(Boolean(productData.product?.is_in_wishlist));
          setIsInCart(Boolean(productData.product?.is_in_cart));
        } else {
          navigate('/products');
        }

        if (meRes.status === 'fulfilled') {
          const user = meRes.value.data.user;
          setUserRole(user?.role || null);
          setCurrentUser(user);
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
      setIsInCart(true);
      setCommentSuccess('Product added to cart successfully!');
      setTimeout(() => setCommentSuccess(''), 3000);
    } catch (err) {
      setCommentError(err.response?.data?.message || 'Failed to add product to cart');
      setTimeout(() => setCommentError(''), 3000);
    }
  };

  const handleSubmitComment = async (e) => {
    e.preventDefault();
    if (!commentText.trim()) {
      setCommentError('Comment cannot be empty');
      setTimeout(() => setCommentError(''), 3000);
      return;
    }

    setSubmittingComment(true);
    setCommentError('');
    setCommentSuccess('');

    try {
      const res = await api.post('/comments', {
        product_id: product.id,
        comment: commentText.trim(),
      });
      
      // Reload product data to get updated comments
      const productRes = await api.get(`/products/${id}`);
      setData(productRes.data);
      setCommentText('');
      setCommentSuccess('Comment added successfully!');
      setTimeout(() => setCommentSuccess(''), 3000);
    } catch (err) {
      setCommentError(err.response?.data?.message || 'Failed to add comment');
      setTimeout(() => setCommentError(''), 3000);
    } finally {
      setSubmittingComment(false);
    }
  };

  const handleDeleteComment = async (commentId) => {
    setDeleteConfirm(commentId);
  };

  const confirmDelete = async () => {
    if (!deleteConfirm) return;

    try {
      await api.delete(`/comments/${deleteConfirm}`);
      
      // Reload product data to get updated comments
      const productRes = await api.get(`/products/${id}`);
      setData(productRes.data);
      setCommentSuccess('Comment deleted successfully!');
      setTimeout(() => setCommentSuccess(''), 3000);
      setDeleteConfirm(null);
    } catch (err) {
      setCommentError(err.response?.data?.message || 'Failed to delete comment');
      setTimeout(() => setCommentError(''), 3000);
      setDeleteConfirm(null);
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
                src={resolveImageUrl(
                  product.image,
                  `https://picsum.photos/600/800?random=${product.id}`
                )}
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
              {product.category && (
                <p className="text-muted">Category: {product.category.name}</p>
              )}
              {product.seller && (
                <p className="text-muted">
                  Seller: {product.seller.name}
                </p>
              )}
              {product.description && (
                <div className="mb-3">
                  <p className="text-muted">{product.description}</p>
                </div>
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

              {userRole === 'customer' && (
                <div className="d-flex gap-2 mb-4">
                  <button
                    type="button"
                    className={`btn ${isWishlisted ? 'btn-danger' : 'btn-outline-danger'}`}
                    disabled={isWishlisted}
                    onClick={async () => {
                      try {
                        await api.post('/wishlist', { product_id: product.id });
                        setIsWishlisted(true);
                        setCommentSuccess('Added to wishlist!');
                        setTimeout(() => setCommentSuccess(''), 3000);
                      } catch (err) {
                        setCommentError(err.response?.data?.message || 'Failed to add to wishlist');
                        setTimeout(() => setCommentError(''), 3000);
                      }
                    }}
                  >
                    {isWishlisted ? 'In Wishlist' : 'Wishlist'}
                  </button>
                  <button
                    type="button"
                    className={`btn flex-grow-1 ${isInCart ? 'btn-success' : 'btn-primary'}`}
                    disabled={isInCart}
                    onClick={handleAddToCart}
                  >
                    {isInCart ? 'In Cart' : 'Add to Cart'}
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
                        src={resolveImageUrl(
                          item.image,
                          `https://picsum.photos/200/200?random=${item.id}`
                        )}
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
                        src={resolveImageUrl(
                          item.image,
                          `https://picsum.photos/200/200?random=${item.id}`
                        )}
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

      {/* Comments Section */}
      <div className="card mt-4">
        <div className="card-body">
          <h5 className="card-title mb-4">Comments ({product.comments?.length || 0})</h5>

          {commentSuccess && (
            <div className="alert alert-success alert-dismissible fade show" role="alert">
              {commentSuccess}
              <button
                type="button"
                className="btn-close"
                onClick={() => setCommentSuccess('')}
                aria-label="Close"
              />
            </div>
          )}

          {commentError && (
            <div className="alert alert-danger alert-dismissible fade show" role="alert">
              {commentError}
              <button
                type="button"
                className="btn-close"
                onClick={() => setCommentError('')}
                aria-label="Close"
              />
            </div>
          )}

          {/* Comment Form */}
          {currentUser && userRole === 'customer' && (
            <form onSubmit={handleSubmitComment} className="mb-4">
              <div className="mb-3">
                <label htmlFor="comment" className="form-label">
                  Add a Comment
                </label>
                <textarea
                  id="comment"
                  className="form-control"
                  rows="3"
                  value={commentText}
                  onChange={(e) => setCommentText(e.target.value)}
                  placeholder="Write your comment here..."
                  maxLength={1000}
                  required
                />
                <small className="text-muted">
                  {commentText.length}/1000 characters
                </small>
              </div>
              <button
                type="submit"
                className="btn btn-primary"
                disabled={submittingComment || !commentText.trim()}
              >
                {submittingComment ? (
                  <>
                    <span
                      className="spinner-border spinner-border-sm me-2"
                      role="status"
                      aria-hidden="true"
                    />
                    Posting...
                  </>
                ) : (
                  'Post Comment'
                )}
              </button>
            </form>
          )}

          {!currentUser && (
            <div className="alert alert-info mb-4">
              <a href="/signin" className="alert-link">
                Sign in
              </a>{' '}
              to leave a comment
            </div>
          )}

          {/* Comments List */}
          <div className="comments-list">
            {product.comments && product.comments.length > 0 ? (
              product.comments
                .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
                .map((comment) => (
                  <div key={comment.id} className="card mb-3">
                    <div className="card-body">
                      <div className="d-flex justify-content-between align-items-start">
                        <div className="flex-grow-1">
                          <div className="d-flex align-items-center mb-2">
                            <strong className="me-2">
                              {comment.user?.name || 'Anonymous'}
                            </strong>
                            <small className="text-muted">
                              {new Date(comment.created_at).toLocaleDateString('id-ID', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                              })}
                            </small>
                          </div>
                          <p className="mb-0">{comment.comment}</p>
                        </div>
                        {currentUser && currentUser.id === comment.user_id && (
                          <>
                            {deleteConfirm === comment.id ? (
                              <div className="d-flex gap-1 ms-2">
                                <button
                                  type="button"
                                  className="btn btn-sm btn-danger"
                                  onClick={confirmDelete}
                                >
                                  <i className="bi bi-check" />
                                </button>
                                <button
                                  type="button"
                                  className="btn btn-sm btn-secondary"
                                  onClick={() => setDeleteConfirm(null)}
                                >
                                  <i className="bi bi-x" />
                                </button>
                              </div>
                            ) : (
                              <button
                                type="button"
                                className="btn btn-sm btn-outline-danger ms-2"
                                onClick={() => handleDeleteComment(comment.id)}
                              >
                                <i className="bi bi-trash" />
                              </button>
                            )}
                          </>
                        )}
                      </div>
                    </div>
                  </div>
                ))
            ) : (
              <p className="text-muted">No comments yet. Be the first to comment!</p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

export default ProductDetailPage;



