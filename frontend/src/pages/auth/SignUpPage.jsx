import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { api } from '../../api/client.js';

function SignUpPage() {
  const [form, setForm] = useState({
    username: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    address: '',
    date_of_birth: '',
    gender: '',
  });
  const [profileImageFile, setProfileImageFile] = useState(null);
  const [profilePreview, setProfilePreview] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleChange = (e) => {
    const { name, value, type } = e.target;
    setForm((prev) => ({
      ...prev,
      [name]: type === 'radio' ? value : value,
    }));
  };

  const handleImageChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) {
      setProfileImageFile(null);
      setProfilePreview('');
      return;
    }
    setProfileImageFile(file);
    const reader = new FileReader();
    reader.onload = (ev) => {
      if (typeof ev.target?.result === 'string') {
        setProfilePreview(ev.target.result);
      }
    };
    reader.readAsDataURL(file);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      const formData = new FormData();
      formData.append('username', form.username);
      formData.append('name', form.name);
      formData.append('email', form.email);
      formData.append('password', form.password);
      formData.append('password_confirmation', form.password_confirmation);
      if (form.phone) formData.append('phone', form.phone);
      if (form.address) formData.append('address', form.address);
      if (form.date_of_birth) formData.append('date_of_birth', form.date_of_birth);
      if (form.gender) formData.append('gender', form.gender);
      if (profileImageFile) formData.append('profile_image', profileImageFile);

      await api.post('/auth/signup', formData);
      navigate('/dashboard');
    } catch (err) {
      const msg =
        err.response?.data?.message ||
        Object.values(err.response?.data?.errors || {})[0]?.[0] ||
        'Sign up failed. Please check your data.';
      setError(msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <h1 className="auth-title">Sign Up</h1>
      {error && (
        <div className="alert alert-danger" role="alert">
          {error}
        </div>
      )}
      <form onSubmit={handleSubmit} encType="multipart/form-data">
        <div className="mb-3 text-center">
          <div
            style={{
              width: 100,
              height: 100,
              borderRadius: '50%',
              overflow: 'hidden',
              margin: '0 auto 10px',
              backgroundColor: '#f0f0f0',
            }}
          >
            {profilePreview ? (
              <img
                src={profilePreview}
                alt="Profile preview"
                style={{ width: '100%', height: '100%', objectFit: 'cover' }}
              />
            ) : (
              <div
                style={{
                  width: '100%',
                  height: '100%',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  color: '#999',
                  fontSize: 12,
                }}
              >
                Profile Photo
              </div>
            )}
          </div>
          <input
            type="file"
            id="profile_image"
            accept="image/*"
            className="d-none"
            onChange={handleImageChange}
          />
          <label htmlFor="profile_image" className="btn btn-sm btn-outline-primary">
            Upload Profile Photo
          </label>
        </div>
        <div className="mb-3">
          <label className="auth-form-label">Username</label>
          <input
            type="text"
            name="username"
            className="auth-input"
            value={form.username}
            onChange={handleChange}
            placeholder="Enter your username"
            required
          />
        </div>
        <div className="mb-3">
          <label className="auth-form-label">Name</label>
          <input
            type="text"
            name="name"
            className="auth-input"
            value={form.name}
            onChange={handleChange}
            placeholder="Enter your full name"
            required
          />
        </div>
        <div className="mb-3">
          <label className="auth-form-label">Email</label>
          <input
            type="email"
            name="email"
            className="auth-input"
            value={form.email}
            onChange={handleChange}
            placeholder="Enter your email"
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
        <div className="mb-3">
          <label className="auth-form-label">Confirm Password</label>
          <input
            type="password"
            name="password_confirmation"
            className="auth-input"
            value={form.password_confirmation}
            onChange={handleChange}
            placeholder="Confirm your password"
            required
          />
        </div>
        <div className="mb-3">
          <label className="auth-form-label">Phone Number</label>
          <input
            type="tel"
            name="phone"
            className="auth-input"
            value={form.phone}
            onChange={handleChange}
            placeholder="+62 812-3456-7890"
          />
        </div>
        <div className="mb-3">
          <label className="auth-form-label">Address</label>
          <textarea
            name="address"
            className="auth-input"
            rows="3"
            value={form.address}
            onChange={handleChange}
            placeholder="Enter your address"
          />
        </div>
        <div className="mb-3">
          <label className="auth-form-label">Date of Birth</label>
          <input
            type="date"
            name="date_of_birth"
            className="auth-input"
            value={form.date_of_birth}
            onChange={handleChange}
          />
        </div>
        <div className="mb-3">
          <label className="auth-form-label">Gender</label>
          <div className="gender-group">
            <label className="gender-option">
              <input
                type="radio"
                name="gender"
                value="Male"
                checked={form.gender === 'Male'}
                onChange={handleChange}
              />
              <span>Male</span>
            </label>
            <label className="gender-option">
              <input
                type="radio"
                name="gender"
                value="Female"
                checked={form.gender === 'Female'}
                onChange={handleChange}
              />
              <span>Female</span>
            </label>
          </div>
        </div>
        <button
          type="submit"
          className="auth-button-primary"
          disabled={loading}
        >
          {loading ? 'Signing up...' : 'Sign Up'}
        </button>
        <div className="auth-footer-text">
          Have Account? <Link to="/signin">Sign In</Link>
        </div>
      </form>
    </div>
  );
}

export default SignUpPage;

