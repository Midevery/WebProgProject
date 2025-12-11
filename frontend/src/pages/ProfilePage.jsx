import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { api } from '../api/client.js';
import { resolveImageUrl } from '../api/media.js';

function ProfilePage() {
  const buildProfilePreview = (imagePath, userId, bustCache = false) => {
    const fallback = `https://picsum.photos/200/200?random=${userId || 'profile'}`;
    let resolved = resolveImageUrl(imagePath, fallback);
    if (imagePath && bustCache) {
      const separator = resolved.includes('?') ? '&' : '?';
      resolved = `${resolved}${separator}v=${Date.now()}`;
    }
    return resolved;
  };

  const [user, setUser] = useState(null);
  const [form, setForm] = useState({
    name: '',
    email: '',
    phone: '',
    address: '',
    gender: '',
    date_of_birth: '',
  });
  
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [profileImageFile, setProfileImageFile] = useState(null);
  const [profilePreview, setProfilePreview] = useState('');
  const [passwordModalOpen, setPasswordModalOpen] = useState(false);
  const [passwordSubmitting, setPasswordSubmitting] = useState(false);
  const [passwordError, setPasswordError] = useState('');
  const [passwordForm, setPasswordForm] = useState({
    current_password: '',
    password: '',
    password_confirmation: '',
  });
  const navigate = useNavigate();

  useEffect(() => {
    async function load() {
      setLoading(true);
      const res = await api.get('/me');
      const u = res.data.user;
      setUser(u);
      let dateOfBirth = '';
      if (u.date_of_birth) {
        if (typeof u.date_of_birth === 'string') {
          dateOfBirth = u.date_of_birth.split('T')[0];
        } else {
          dateOfBirth = u.date_of_birth;
        }
      }
      setForm({
        name: u.name || '',
        email: u.email || '',
        phone: u.phone || '',
        address: u.address || '',
        gender: u.gender || '',
        date_of_birth: dateOfBirth,
      });
      setProfilePreview(buildProfilePreview(u.profile_image, u.id));
      setLoading(false);
    }
    load();
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleImageChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) {
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
    setSubmitting(true);
    setError('');
    setSuccess('');
    try {
      const nameValue = (form.name || '').trim();
      const emailValue = (form.email || '').trim();
      
      if (!nameValue || !emailValue) {
        setError('Name and Email are required fields.');
        setSubmitting(false);
        return;
      }
      
      const formData = new FormData();
      formData.append('name', nameValue);
      formData.append('email', emailValue);
      if (form.phone && form.phone.trim()) {
        formData.append('phone', form.phone.trim());
      }
      if (form.address && form.address.trim()) {
        formData.append('address', form.address.trim());
      }
      if (form.gender) {
        formData.append('gender', form.gender);
      }
      if (form.date_of_birth) {
        formData.append('date_of_birth', form.date_of_birth);
      }
      if (profileImageFile) {
        formData.append('profile_image', profileImageFile);
      }
      formData.delete('username');
      formData.append('_method', 'PUT');
      
      console.log('FormData contents:', {
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        address: formData.get('address'),
        gender: formData.get('gender'),
        date_of_birth: formData.get('date_of_birth'),
        has_image: !!formData.get('profile_image'),
      });
      const res = await api.post('/me', formData);
      const updatedUser = res.data.user;
      setUser(updatedUser);
      setProfileImageFile(null);
      setProfilePreview(buildProfilePreview(updatedUser.profile_image, updatedUser.id, true));
      setSuccess('Profile updated successfully!');
      
      // Trigger user refresh in MainLayout
      window.dispatchEvent(new CustomEvent('userUpdated', { detail: updatedUser }));
      
      setTimeout(() => setSuccess(''), 3000);
    } catch (err) {
      const errorMsg =
        err.response?.data?.message ||
        Object.values(err.response?.data?.errors || {})[0]?.[0] ||
        'Failed to update profile. Please try again.';
      setError(errorMsg);
    } finally {
      setSubmitting(false);
    }
  };

  const handlePasswordChangeField = (e) => {
    const { name, value } = e.target;
    setPasswordForm((prev) => ({ ...prev, [name]: value }));
  };

  const handlePasswordSubmit = async (e) => {
    e.preventDefault();
    setPasswordSubmitting(true);
    setPasswordError('');

    if (passwordForm.password !== passwordForm.password_confirmation) {
      setPasswordError('New password and confirmation do not match.');
      setPasswordSubmitting(false);
      return;
    }

    try {
      await api.put('/me/password', passwordForm);
      setPasswordForm({
        current_password: '',
        password: '',
        password_confirmation: '',
      });
      setPasswordModalOpen(false);
      setSuccess('Password updated successfully!');
      setTimeout(() => setSuccess(''), 3000);
    } catch (err) {
      const errorMsg =
        err.response?.data?.message ||
        Object.values(err.response?.data?.errors || {})[0]?.[0] ||
        'Failed to update password. Please check your current password.';
      setPasswordError(errorMsg);
    } finally {
      setPasswordSubmitting(false);
    }
  };

  if (loading || !user) {
    return (
      <div className="container my-4">
        <p>Loading profile...</p>
      </div>
    );
  }

  return (
    <div
      className="container my-4"
      style={{
        backgroundColor: 'var(--refurbworks-light)',
        padding: '2rem',
        borderRadius: '10px',
      }}
    >
      <button
        type="button"
        className="btn btn-outline-primary mb-3"
        onClick={() => navigate(-1)}
      >
        ← Back
      </button>

      <div className="row">
        <div className="col-md-3 text-center mb-4">
          <div className="position-relative d-inline-block">
            <img
              src={profilePreview}
              alt="Profile"
              className="rounded-circle img-fluid"
              style={{ width: 200, height: 200, objectFit: 'cover' }}
              onError={(e) => {
                e.currentTarget.src = `https://picsum.photos/200/200?random=${user.id}`;
              }}
            />
            <label
              htmlFor="profile_image"
              className="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle"
              style={{ width: 40, height: 40, cursor: 'pointer' }}
              title="Upload Photo"
            >
              <i className="bi bi-camera" />
            </label>
          </div>
          <div className="mt-3">
            <small className="text-muted">Click camera icon to upload</small>
          </div>
        </div>
        <div className="col-md-9">
          <div className="card">
            <div className="card-body">
              <h3 className="card-title mb-4">My Profile</h3>
              {error && (
                <div className="alert alert-danger alert-dismissible fade show" role="alert">
                  {error}
                  <button
                    type="button"
                    className="btn-close"
                    onClick={() => setError('')}
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
                  />
                </div>
              )}
              <form onSubmit={handleSubmit} encType="multipart/form-data">
                <input
                  type="file"
                  id="profile_image"
                  accept="image/*"
                  className="d-none"
                  onChange={handleImageChange}
                />
                <div className="mb-3">
                  <label className="form-label">Username</label>
                  <input
                    type="text"
                    className="form-control"
                    value={user.username}
                    disabled
                    readOnly
                    style={{ backgroundColor: '#e9ecef', cursor: 'not-allowed' }}
                  />
                  <small className="text-muted">Username cannot be changed</small>
                </div>
                <div className="mb-3">
                  <label className="form-label">Name</label>
                  <input
                    type="text"
                    className="form-control"
                    name="name"
                    value={form.name}
                    onChange={handleChange}
                    required
                  />
                </div>
                <div className="mb-3">
                  <label className="form-label">Email</label>
                  <input
                    type="email"
                    className="form-control"
                    name="email"
                    value={form.email}
                    onChange={handleChange}
                    required
                  />
                  <small className="text-muted">
                    This will be used for login and notifications.
                  </small>
                </div>
                <div className="mb-3">
                  <label className="form-label">Phone Number</label>
                  <input
                    type="tel"
                    className="form-control"
                    name="phone"
                    value={form.phone}
                    onChange={handleChange}
                    placeholder="+62 812-3456-7890"
                  />
                  <small className="text-muted">
                    Leave blank if you prefer not to share.
                  </small>
                </div>
                <div className="mb-3">
                  <label className="form-label">Address</label>
                  <textarea
                    className="form-control"
                    name="address"
                    rows={3}
                    value={form.address}
                    onChange={handleChange}
                  />
                </div>
                <div className="mb-3">
                  <label className="form-label">Gender</label>
                  <div className="form-check">
                    <input
                      className="form-check-input"
                      type="radio"
                      name="gender"
                      id="male"
                      value="Male"
                      checked={form.gender === 'Male'}
                      onChange={handleChange}
                    />
                    <label className="form-check-label" htmlFor="male">
                      Male
                    </label>
                  </div>
                  <div className="form-check">
                    <input
                      className="form-check-input"
                      type="radio"
                      name="gender"
                      id="female"
                      value="Female"
                      checked={form.gender === 'Female'}
                      onChange={handleChange}
                    />
                    <label className="form-check-label" htmlFor="female">
                      Female
                    </label>
                  </div>
                </div>
                <div className="mb-3">
                  <label className="form-label">Date of Birth</label>
                  <div className="input-group">
                    <input
                      type="date"
                      className="form-control"
                      name="date_of_birth"
                      value={form.date_of_birth || ''}
                      onChange={handleChange}
                    />
                    <span className="input-group-text">
                      <i className="bi bi-calendar3" />
                    </span>
                  </div>
                </div>
                <div className="d-flex gap-2">
                  <button
                    type="submit"
                    className="btn btn-primary"
                    disabled={submitting}
                  >
                    {submitting ? 'Saving...' : 'Save Changes'}
                  </button>
                  <button
                    type="button"
                    className="btn btn-outline-primary"
                    onClick={() => {
                      setPasswordModalOpen(true);
                      setPasswordError('');
                    }}
                  >
                    Change Password
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      {passwordModalOpen && (
        <div
          className="modal fade show"
          style={{
            display: 'block',
            backgroundColor: 'rgba(0,0,0,0.5)',
          }}
        >
          <div className="modal-dialog">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">Change Password</h5>
                <button
                  type="button"
                  className="btn-close"
                  onClick={() => setPasswordModalOpen(false)}
                />
              </div>
              <form onSubmit={handlePasswordSubmit}>
                <div className="modal-body">
                  {passwordError && (
                    <div className="alert alert-danger" role="alert">
                      {passwordError}
                    </div>
                  )}
                  <div className="mb-3">
                    <label className="form-label">Current Password</label>
                    <input
                      type="password"
                      className="form-control"
                      name="current_password"
                      value={passwordForm.current_password}
                      onChange={handlePasswordChangeField}
                      required
                      disabled={passwordSubmitting}
                    />
                  </div>
                  <div className="mb-3">
                    <label className="form-label">New Password</label>
                    <input
                      type="password"
                      className="form-control"
                      name="password"
                      value={passwordForm.password}
                      onChange={handlePasswordChangeField}
                      required
                      disabled={passwordSubmitting}
                      minLength={8}
                    />
                    <small className="text-muted">Minimum 8 characters</small>
                  </div>
                  <div className="mb-3">
                    <label className="form-label">Confirm New Password</label>
                    <input
                      type="password"
                      className="form-control"
                      name="password_confirmation"
                      value={passwordForm.password_confirmation}
                      onChange={handlePasswordChangeField}
                      required
                      disabled={passwordSubmitting}
                      minLength={8}
                    />
                  </div>
                </div>
                <div className="modal-footer">
                  <button
                    type="button"
                    className="btn btn-secondary"
                    onClick={() => {
                      setPasswordModalOpen(false);
                      setPasswordError('');
                      setPasswordForm({
                        current_password: '',
                        password: '',
                        password_confirmation: '',
                      });
                    }}
                    disabled={passwordSubmitting}
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    className="btn btn-primary"
                    disabled={passwordSubmitting}
                  >
                    {passwordSubmitting ? 'Updating...' : 'Change Password'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default ProfilePage;



