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
      const role = res.data?.user?.role;
      const allowedRoles = ['seller', 'customer'];
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
    <div>
      <h1 className="auth-title">Sign In</h1>
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
          className="auth-button-primary"
          disabled={loading}
        >
          {loading ? 'Signing in...' : 'Sign In'}
        </button>
        <div className="auth-footer-text">
          No Account? <Link to="/signup">Sign Up</Link>
        </div>
      </form>
    </div>
  );
}

export default SignInPage;

