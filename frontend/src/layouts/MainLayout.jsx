import { useEffect, useMemo, useRef, useState } from 'react';
import { Link, Outlet, useNavigate } from 'react-router-dom';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import { api } from '../api/client.js';
import { useCart } from '../contexts/CartContext.jsx';

function MainLayout() {
  const backendBaseUrl =
    import.meta.env.VITE_BACKEND_URL?.replace(/\/$/, '') || 'http://localhost:8000';

  const resolveImageUrl = useMemo(
    () => (path, fallback) => {
      if (!path) return fallback;
      if (path.startsWith('http://') || path.startsWith('https://')) {
        return path;
      }
      return `${backendBaseUrl}/${path.replace(/^\/+/, '')}`;
    },
    [backendBaseUrl],
  );

  const [user, setUser] = useState(null);
  const { cartCount, refreshCartCount } = useCart();
  const [menuOpen, setMenuOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const navigate = useNavigate();
  const dropdownRef = useRef(null);

  useEffect(() => {
    async function loadUser() {
      try {
        const res = await api.get('/me');
        const u = res.data.user;
        setUser(u);
      } catch {
        setUser(null);
      }
    }
    loadUser();
    
    // Listen for user update events
    const handleUserUpdate = (event) => {
      if (event.detail) {
        setUser(event.detail);
      } else {
        loadUser();
      }
    };
    
    window.addEventListener('userUpdated', handleUserUpdate);
    
    return () => {
      window.removeEventListener('userUpdated', handleUserUpdate);
    };
  }, []);

  useEffect(() => {
    function handleClickOutside(event) {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setMenuOpen(false);
      }
    }
    document.addEventListener('click', handleClickOutside);
    return () => {
      document.removeEventListener('click', handleClickOutside);
    };
  }, []);

  const handleSignOut = async () => {
    try {
      await api.post('/auth/signout');
    } catch {
      /* ignore */
    }
    setUser(null);
    await refreshCartCount();
    navigate('/signin');
  };

  const isCustomer = user?.role === 'customer';
  const isSeller = user?.role === 'seller';

  return (
    <div className="d-flex flex-column min-vh-100">
      <nav className="navbar navbar-expand-lg navbar-refurbworks">
        <div className="container">
          <Link className="navbar-brand" to="/">
            RefurbWorks
          </Link>

          <button
            className="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
          >
            <span className="navbar-toggler-icon" />
          </button>

          <div className="collapse navbar-collapse" id="navbarNav">
            <form
              className="search-bar position-relative mx-3 my-2 my-lg-0"
              onSubmit={(e) => {
                e.preventDefault();
                const term = searchTerm.trim();
                if (term) {
                  navigate(`/products?search=${encodeURIComponent(term)}`);
                } else {
                  navigate('/products');
                }
              }}
            >
              <i className="bi bi-search search-icon" />
              <input
                type="text"
                className="form-control"
                placeholder="Search our product"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
            </form>

            <div className="d-flex align-items-center ms-auto">
              {isCustomer && (
                <>
                  <Link to="/wishlist" className="nav-icon">
                    <i className="bi bi-heart" />
                  </Link>
                  <Link to="/cart" className="nav-icon position-relative">
                    <i className="bi bi-cart3" />
                    <span className="cart-badge">{cartCount}</span>
                  </Link>
                </>
              )}
              {user ? (
                <div className="dropdown ms-2" ref={dropdownRef}>
                  <button
                    type="button"
                    className="btn btn-signin dropdown-toggle d-flex align-items-center"
                    onClick={() => setMenuOpen((prev) => !prev)}
                  >
                    <img
                      src={resolveImageUrl(
                        user.profile_image,
                        `https://picsum.photos/40/40?random=${user.id}`,
                      )}
                      alt={user.name || user.username}
                      className="rounded-circle me-2"
                      style={{
                        width: 30,
                        height: 30,
                        objectFit: 'cover',
                      }}
                      onError={(e) => {
                        e.currentTarget.src = `https://picsum.photos/40/40?random=${user.id}`;
                      }}
                    />
                    <span className="d-none d-md-inline">
                      {user.name || user.username}
                    </span>
                  </button>
                  <ul
                    className={`dropdown-menu dropdown-menu-end${
                      menuOpen ? ' show' : ''
                    }`}
                  >
                    {isSeller && (
                      <>
                        <li>
                          <Link
                            className="dropdown-item"
                            to="/seller/dashboard"
                            onClick={() => setMenuOpen(false)}
                          >
                            <i className="bi bi-speedometer2 me-2" />
                            Seller Dashboard
                          </Link>
                        </li>
                        <li>
                          <Link
                            className="dropdown-item"
                            to="/seller/products/add"
                            onClick={() => setMenuOpen(false)}
                          >
                            <i className="bi bi-plus-circle me-2" />
                            Add Product
                          </Link>
                        </li>
                        <li>
                          <Link
                            className="dropdown-item"
                            to="/seller/shipping"
                            onClick={() => setMenuOpen(false)}
                          >
                            <i className="bi bi-truck me-2" />
                            Manage Shipping
                          </Link>
                        </li>
                        <li>
                          <Link
                            className="dropdown-item"
                            to="/seller/profile"
                            onClick={() => setMenuOpen(false)}
                          >
                            <i className="bi bi-person-badge me-2" />
                            Seller Profile &amp; Analytics
                          </Link>
                        </li>
                        <li>
                          <hr className="dropdown-divider" />
                        </li>
                      </>
                    )}
                    {isCustomer && (
                      <>
                        <li>
                          <Link
                            className="dropdown-item"
                            to="/dashboard"
                            onClick={() => setMenuOpen(false)}
                          >
                            <i className="bi bi-speedometer2 me-2" />
                            Dashboard
                          </Link>
                        </li>
                        <li>
                          <Link
                            className="dropdown-item"
                            to="/cart"
                            onClick={() => setMenuOpen(false)}
                          >
                            <i className="bi bi-cart3 me-2" />
                            My Cart
                          </Link>
                        </li>
                        <li>
                          <Link
                            className="dropdown-item"
                            to="/wishlist"
                            onClick={() => setMenuOpen(false)}
                          >
                            <i className="bi bi-heart me-2" />
                            Wishlist
                          </Link>
                        </li>
                        <li>
                          <Link
                            className="dropdown-item"
                            to="/shipping"
                            onClick={() => setMenuOpen(false)}
                          >
                            <i className="bi bi-box-seam me-2" />
                            My Orders
                          </Link>
                        </li>
                        <li>
                          <Link
                            className="dropdown-item"
                            to="/shipping"
                            onClick={() => setMenuOpen(false)}
                          >
                            <i className="bi bi-truck me-2" />
                            Track Order
                          </Link>
                        </li>
                        <li>
                          <hr className="dropdown-divider" />
                        </li>
                      </>
                    )}
                    <li>
                      <Link
                        className="dropdown-item"
                        to="/profile"
                        onClick={() => setMenuOpen(false)}
                      >
                        <i className="bi bi-person me-2" />
                        Edit Profile
                      </Link>
                    </li>
                    <li>
                      <hr className="dropdown-divider" />
                    </li>
                    <li>
                      <button
                        type="button"
                        className="dropdown-item"
                        onClick={() => {
                          setMenuOpen(false);
                          handleSignOut();
                        }}
                      >
                        <i className="bi bi-box-arrow-right me-2" />
                        Sign Out
                      </button>
                    </li>
                  </ul>
                </div>
              ) : (
                <Link to="/signin" className="btn btn-signin ms-2">
                  Sign In
                </Link>
              )}
            </div>
          </div>
        </div>
      </nav>

      <main className="flex-grow-1">
        <Outlet />
      </main>

      <footer className="footer-refurbworks">
        <div className="container">
          <div className="row">
            <div className="col-md-4 mb-4">
              <h5 className="footer-title">Connect with RefurbWorks</h5>
              <div>
                <a
                  href="https://www.instagram.com"
                  className="social-icon"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <i className="bi bi-instagram" />
                </a>
                <a
                  href="https://www.tiktok.com"
                  className="social-icon"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <i className="bi bi-tiktok" />
                </a>
                <a
                  href="https://x.com"
                  className="social-icon"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <i className="bi bi-twitter-x" />
                </a>
              </div>
            </div>
            <div className="col-md-4 mb-4">
              <h5 className="footer-title">Give us your Idea!</h5>
              <a href="#" className="footer-link">
                Feedback
              </a>
              <a href="#" className="footer-link">
                Bug Issue
              </a>
            </div>
            <div className="col-md-4 mb-4">
              <h5 className="footer-title">Terms of Service</h5>
              <a href="#" className="footer-link">
                Terms and Conditions
              </a>
              <a href="#" className="footer-link">
                Privacy Policy
              </a>
              <a href="#" className="footer-link">
                Return Policy
              </a>
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
}

export default MainLayout;



