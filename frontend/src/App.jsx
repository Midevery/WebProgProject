import { BrowserRouter, Routes, Route } from 'react-router-dom';
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
import AdminDashboardPage from './pages/admin/AdminDashboardPage.jsx';
import AdminProductsPage from './pages/admin/AdminProductsPage.jsx';
import AdminAddProductPage from './pages/admin/AdminAddProductPage.jsx';
import AdminEditProductPage from './pages/admin/AdminEditProductPage.jsx';
import AdminOrdersPage from './pages/admin/AdminOrdersPage.jsx';
import AdminEarningPage from './pages/admin/AdminEarningPage.jsx';
import AdminProfilePage from './pages/admin/AdminProfilePage.jsx';
import ArtistDashboardPage from './pages/artist/ArtistDashboardPage.jsx';
import ArtistProfilePage from './pages/artist/ArtistProfilePage.jsx';
import ArtistProductAnalyticsPage from './pages/artist/ArtistProductAnalyticsPage.jsx';

function App() {
  return (
    <CartProvider>
      <BrowserRouter>
        <Routes>
          <Route element={<MainLayout />}>
            <Route path="/" element={<HomePage />} />
            <Route path="/products" element={<ProductsListPage />} />
            <Route path="/products/:id" element={<ProductDetailPage />} />
            <Route path="/cart" element={<CartPage />} />
            <Route path="/dashboard" element={<DashboardPage />} />
            <Route path="/profile" element={<ProfilePage />} />
            <Route path="/wishlist" element={<WishlistPage />} />
            <Route path="/payment" element={<PaymentPage />} />
            <Route path="/payment/:orderId" element={<PaymentDetailPage />} />
            <Route path="/shipping" element={<ShippingPage />} />
            <Route path="/shipping/:orderId" element={<ShippingDetailPage />} />
            {/* Admin */}
            <Route path="/admin/dashboard" element={<AdminDashboardPage />} />
            <Route path="/admin/products" element={<AdminProductsPage />} />
            <Route path="/admin/products/add" element={<AdminAddProductPage />} />
            <Route
              path="/admin/products/:id/edit"
              element={<AdminEditProductPage />}
            />
            <Route path="/admin/orders" element={<AdminOrdersPage />} />
            <Route path="/admin/earning" element={<AdminEarningPage />} />
            <Route path="/admin/profile" element={<AdminProfilePage />} />
            {/* Artist */}
            <Route path="/artist/dashboard" element={<ArtistDashboardPage />} />
            <Route path="/artist/profile" element={<ArtistProfilePage />} />
            <Route
              path="/artist/product/:productId/analytics"
              element={<ArtistProductAnalyticsPage />}
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
