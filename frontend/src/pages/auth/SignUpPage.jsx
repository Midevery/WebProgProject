import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import 'mdb-react-ui-kit/dist/css/mdb.min.css';
import { MDBContainer, MDBRow, MDBCol, MDBInput, MDBRadio } from 'mdb-react-ui-kit';
import { api } from '../../api/client.js';

export default function SignUpPage() {
  const [form, setForm] = useState({
    firstName: '',
    lastName: '',
    username: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    date_of_birth: '',
    gender: '',
    address: '',
    role: 'customer',
  });

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

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const fullName = `${form.firstName || ''} ${form.lastName || ''}`.trim();
      const formData = new FormData();

      formData.append('name', fullName || form.username);
      formData.append('username', form.username);
      formData.append('email', form.email);
      formData.append('password', form.password);
      formData.append('password_confirmation', form.password_confirmation);
      formData.append('role', form.role);

      if (form.phone) formData.append('phone', form.phone);
      if (form.address) formData.append('address', form.address);
      if (form.date_of_birth) formData.append('date_of_birth', form.date_of_birth);
      if (form.gender) formData.append('gender', form.gender);

      const res = await api.post('/auth/signup', formData);
      const role = res.data?.user?.role;

      if (role === 'seller') {
        navigate('/seller/dashboard');
      } else {
        navigate('/dashboard');
      }
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
    <div className="auth-hero">
      <MDBContainer fluid className="px-3 px-md-4" style={{ maxWidth: '1600px' }}>
        

        <MDBRow className="g-4 align-items-center justify-content-center">
          <MDBCol lg="7" md="12">
            <div className="bg-white rounded-4 p-4 p-md-5 mx-auto signup-card">
              <h1 className="auth-title text-center mb-4">Sign Up</h1>

              {error && (
                <div className="alert alert-danger mb-3" role="alert">
                  {error}
                </div>
              )}

              <form onSubmit={handleSubmit}>
                <MDBRow className="mb-3">
                  <MDBCol md="6">
                    <MDBInput
                      wrapperClass="mb-4"
                      label="First Name"
                      size="lg"
                      name="firstName"
                      value={form.firstName}
                      onChange={handleChange}
                      required
                    />
                  </MDBCol>
                  <MDBCol md="6">
                    <MDBInput
                      wrapperClass="mb-4"
                      label="Last Name"
                      size="lg"
                      name="lastName"
                      value={form.lastName}
                      onChange={handleChange}
                    />
                  </MDBCol>
                </MDBRow>

                <MDBRow className="mb-3">
                  <MDBCol md="6">
                    <MDBInput
                      wrapperClass="mb-4"
                      label="Email"
                      size="lg"
                      name="email"
                      type="email"
                      value={form.email}
                      onChange={handleChange}
                      required
                    />
                  </MDBCol>
                  <MDBCol md="6">
                    <MDBInput
                      wrapperClass="mb-4"
                      label="Phone"
                      size="lg"
                      name="phone"
                      type="tel"
                      value={form.phone}
                      onChange={handleChange}
                    />
                  </MDBCol>
                </MDBRow>

                <MDBRow className="mb-3">
                  <MDBCol md="6">
                    <MDBInput
                      wrapperClass="mb-4"
                      label="Username"
                      size="lg"
                      name="username"
                      value={form.username}
                      onChange={handleChange}
                      required
                    />
                  </MDBCol>
                  <MDBCol md="6">
                    <MDBInput
                      wrapperClass="mb-4"
                      label="Date of Birth"
                      size="lg"
                      name="date_of_birth"
                      type="date"
                      value={form.date_of_birth}
                      onChange={handleChange}
                    />
                  </MDBCol>
                </MDBRow>

                <MDBRow className="mb-3">
                  <MDBCol md="6">
                    <MDBInput
                      wrapperClass="mb-4"
                      label="Password"
                      size="lg"
                      name="password"
                      type="password"
                      value={form.password}
                      onChange={handleChange}
                      required
                    />
                  </MDBCol>
                  <MDBCol md="6">
                    <MDBInput
                      wrapperClass="mb-4"
                      label="Confirm Password"
                      size="lg"
                      name="password_confirmation"
                      type="password"
                      value={form.password_confirmation}
                      onChange={handleChange}
                      required
                    />
                  </MDBCol>
                </MDBRow>

                <MDBRow className="mb-3">
                  <MDBCol md="6">
                    <h6 className="fw-bold mb-2">Gender</h6>
                    <div className="d-flex flex-column gap-2">
                      <MDBRadio
                        name="gender"
                        value="Male"
                        label="Male"
                        checked={form.gender === 'Male'}
                        onChange={handleChange}
                      />
                      <MDBRadio
                        name="gender"
                        value="Female"
                        label="Female"
                        checked={form.gender === 'Female'}
                        onChange={handleChange}
                      />
                    </div>
                  </MDBCol>
                  <MDBCol md="6">
                    <label className="auth-form-label">Address</label>
                    <textarea
                      name="address"
                      className="auth-input"
                      rows="3"
                      value={form.address}
                      onChange={handleChange}
                      style={{ width: '100%' }}
                    />
                  </MDBCol>
                </MDBRow>

                <MDBRow className="mb-4">
                  <MDBCol md="6">
                    <h6 className="fw-bold mb-2">Register as</h6>
                    <div className="d-flex gap-3 align-items-center">
                      <MDBRadio
                        name="role"
                        value="customer"
                        label="Customer"
                        inline
                        checked={form.role === 'customer'}
                        onChange={handleChange}
                      />
                      <MDBRadio
                        name="role"
                        value="seller"
                        label="Seller"
                        inline
                        checked={form.role === 'seller'}
                        onChange={handleChange}
                      />
                    </div>
                  </MDBCol>
                </MDBRow>

                <button type="submit" className="auth-button-primary w-100" disabled={loading}>
                  {loading ? 'Signing up...' : 'Sign Up'}
                </button>

                <div className="auth-footer-text text-center mt-3">
                  Already have an account? <Link to="/signin">Sign In</Link>
                </div>
              </form>
            </div>
          </MDBCol>

          <MDBCol lg="5" className="d-none d-lg-flex justify-content-center">
            <div className="text-success fw-bold" style={{ fontSize: '34px', lineHeight: 1.25 }}>
              Welcome to
              <br />
              RefurbWorks
            </div>
          </MDBCol>
        </MDBRow>
      </MDBContainer>
    </div>
  );
}