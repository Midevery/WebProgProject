import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { api } from '../../api/client.js';

function SignInPage() {
  const [form, setForm] = useState({
    login: '',
    password: '',
    remember: false,
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setForm((prev) => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      const res = await api.post('/auth/signin', form);
      const token = res.data.access_token;
      const user = res.data.user;
      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(user));
      const role = user?.role;
      const allowedRoles = ['seller', 'customer'];
      if (!allowedRoles.includes(role)) {
        setError('Admin access is disabled.');
        localStorage.removeItem('token');
        return;
      }
      if (role === 'seller') {
        navigate('/seller/dashboard');
      } else {
        navigate('/dashboard');
      }
    } catch (err) {
      const msg =
        err.response?.data?.message ||
        'Sign in failed. Please check your credentials.';
      setError(msg);
    } finally {
      setLoading(false);
    }
  };

  return (
      <div className="container">
        <div className="row justify-content-center align-items-center min-vh-100">
          <div className="col-lg-6 d-flex justify-content-center">
            <div className="signin-plain w-100">
              <h1 className="auth-title text-center">Sign In</h1>
              {error && (
                <div className="alert alert-danger" role="alert">
                  {error}
                </div>
              )}
              <form onSubmit={handleSubmit}>
                <div className="mb-3">
                  <label className="auth-form-label">Username / Email</label>
                  <input
                    type="text"
                    name="login"
                    className="auth-input"
                    value={form.login}
                    onChange={handleChange}
                    placeholder="Enter your username or email"
                    required
                  />
                </div>
                <div className="mb-3">
                  <label className="auth-form-label">Password</label>
                  <input
                    type="password"
                    name="password"
                    className="auth-input"
                    value={form.password}
                    onChange={handleChange}
                    placeholder="Enter your password"
                    required
                  />
                </div>
                <div className="auth-checkbox-row">
                  <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    checked={form.remember}
                    onChange={handleChange}
                  />
                  <label htmlFor="remember">Keep Sign In</label>
                </div>
                <button
                  type="submit"
                  className="auth-button-primary w-100"
                  disabled={loading}
                >
                  {loading ? 'Signing in...' : 'Sign In'}
                </button>
                <div className="auth-footer-text text-center">
                  No Account? <Link to="/signup">Sign Up</Link>
                </div>
              </form>
            </div>
          </div>
          <div className="col-lg-6 d-none d-lg-flex align-items-center justify-content-end">
            <div className="auth-welcome-text-right text-end">
              <h2 className="fw-bold mb-3">Welcome to RefurbWorks</h2>
              <p className="text-muted">Sign in to continue shopping or selling.</p>
            </div>
          </div>
        </div>
      </div>
  );
}

export default SignInPage;

