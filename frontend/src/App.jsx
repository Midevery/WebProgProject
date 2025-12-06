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
import SellerDashboardPage from './pages/seller/SellerDashboardPage.jsx';
import SellerProfilePage from './pages/seller/SellerProfilePage.jsx';
import SellerProductAnalyticsPage from './pages/seller/SellerProductAnalyticsPage.jsx';
import SellerAddProductPage from './pages/seller/SellerAddProductPage.jsx';
import SellerEditProductPage from './pages/seller/SellerEditProductPage.jsx';
import SellerShippingPage from './pages/seller/SellerShippingPage.jsx';

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
            {/* Seller */}
            <Route path="/seller/dashboard" element={<SellerDashboardPage />} />
            <Route path="/seller/profile" element={<SellerProfilePage />} />
            <Route path="/seller/products/add" element={<SellerAddProductPage />} />
            <Route path="/seller/products/:id/edit" element={<SellerEditProductPage />} />
            <Route path="/seller/shipping" element={<SellerShippingPage />} />
            <Route
              path="/seller/product/:productId/analytics"
              element={<SellerProductAnalyticsPage />}
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
