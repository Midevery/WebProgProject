import { useEffect, useState } from 'react';
import { BrowserRouter, Routes, Route, Navigate, useLocation } from 'react-router-dom';
import { CartProvider } from './contexts/CartContext.jsx';
import MainLayout from './layouts/MainLayout.jsx';
import AuthLayout from './layouts/AuthLayout.jsx';
import HomePage from './pages/HomePage.jsx';
import SignInPage from './pages/auth/SignInPage.jsx';
import SignUpPage from './pages/auth/SignUpPage.jsx';
import ProductsListPage from './pages/ProductsListPage.jsx';
import ProductDetailPage from './pages/ProductDetailPage.jsx';
import CartPage from './pages/CartPage.jsx';
import DashboardPage from './pages/DashboardPage.jsx';
import ProfilePage from './pages/ProfilePage.jsx';
import WishlistPage from './pages/WishlistPage.jsx';
import PaymentPage from './pages/PaymentPage.jsx';
import PaymentDetailPage from './pages/PaymentDetailPage.jsx';
import ShippingPage from './pages/ShippingPage.jsx';
import ShippingDetailPage from './pages/ShippingDetailPage.jsx';
import SellerDashboardPage from './pages/seller/SellerDashboardPage.jsx';
import SellerProfilePage from './pages/seller/SellerProfilePage.jsx';
import SellerProductAnalyticsPage from './pages/seller/SellerProductAnalyticsPage.jsx';
import SellerAddProductPage from './pages/seller/SellerAddProductPage.jsx';
import SellerEditProductPage from './pages/seller/SellerEditProductPage.jsx';
import SellerShippingPage from './pages/seller/SellerShippingPage.jsx';
import { api } from './api/client.js';

function RequireAuth({ children, allowedRoles }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const location = useLocation();

  useEffect(() => {
    let active = true;
    async function load() {
      try {
        const res = await api.get('/me');
        if (active) setUser(res.data.user);
      } catch {
        if (active) setUser(null);
      } finally {
        if (active) setLoading(false);
      }
    }
    load();
    return () => {
      active = false;
    };
  }, []);

  if (loading) {
    return (
      <div className="container my-4">
        <p>Checking session...</p>
      </div>
    );
  }

  if (!user) {
    return <Navigate to="/signin" replace state={{ from: location.pathname }} />;
  }

  if (allowedRoles && !allowedRoles.includes(user.role)) {
    return <Navigate to="/signin" replace />;
  }

  return children;
}

function App() {
  return (
    <CartProvider>
      <BrowserRouter>
        <Routes>
          <Route element={<MainLayout />}>
            <Route path="/" element={<HomePage />} />
            <Route path="/products" element={<ProductsListPage />} />
            <Route
              path="/products/:id"
              element={
                <RequireAuth allowedRoles={['customer', 'seller']}>
                  <ProductDetailPage />
                </RequireAuth>
              }
            />
            <Route
              path="/cart"
              element={
                <RequireAuth allowedRoles={['customer']}>
                  <CartPage />
                </RequireAuth>
              }
            />
            <Route
              path="/dashboard"
              element={
                <RequireAuth allowedRoles={['customer']}>
                  <DashboardPage />
                </RequireAuth>
              }
            />
            <Route
              path="/profile"
              element={
                <RequireAuth allowedRoles={['customer', 'seller']}>
                  <ProfilePage />
                </RequireAuth>
              }
            />
            <Route
              path="/wishlist"
              element={
                <RequireAuth allowedRoles={['customer']}>
                  <WishlistPage />
                </RequireAuth>
              }
            />
            <Route
              path="/payment"
              element={
                <RequireAuth allowedRoles={['customer']}>
                  <PaymentPage />
                </RequireAuth>
              }
            />
            <Route
              path="/payment/:orderId"
              element={
                <RequireAuth allowedRoles={['customer']}>
                  <PaymentDetailPage />
                </RequireAuth>
              }
            />
            <Route
              path="/shipping"
              element={
                <RequireAuth allowedRoles={['customer']}>
                  <ShippingPage />
                </RequireAuth>
              }
            />
            <Route
              path="/shipping/:orderId"
              element={
                <RequireAuth allowedRoles={['customer']}>
                  <ShippingDetailPage />
                </RequireAuth>
              }
            />
            {/* Seller */}
            <Route
              path="/seller/dashboard"
              element={
                <RequireAuth allowedRoles={['seller']}>
                  <SellerDashboardPage />
                </RequireAuth>
              }
            />
            <Route
              path="/seller/profile"
              element={
                <RequireAuth allowedRoles={['seller']}>
                  <SellerProfilePage />
                </RequireAuth>
              }
            />
            <Route
              path="/seller/products/add"
              element={
                <RequireAuth allowedRoles={['seller']}>
                  <SellerAddProductPage />
                </RequireAuth>
              }
            />
            <Route
              path="/seller/products/:id/edit"
              element={
                <RequireAuth allowedRoles={['seller']}>
                  <SellerEditProductPage />
                </RequireAuth>
              }
            />
            <Route
              path="/seller/shipping"
              element={
                <RequireAuth allowedRoles={['seller']}>
                  <SellerShippingPage />
                </RequireAuth>
              }
            />
            <Route
              path="/seller/product/:productId/analytics"
              element={
                <RequireAuth allowedRoles={['seller']}>
                  <SellerProductAnalyticsPage />
                </RequireAuth>
              }
            />
          </Route>
          <Route element={<AuthLayout />}>
            <Route path="/signin" element={<SignInPage />} />
            <Route path="/signup" element={<SignUpPage />} />
          </Route>
        </Routes>
      </BrowserRouter>
    </CartProvider>
  );
}

export default App;
