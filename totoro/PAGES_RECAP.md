# Rekapitulasi Semua Halaman Aplikasi Kisora Shop

## 📋 Daftar Semua Halaman

### 🔐 Authentication Pages
1. **Sign In** (`/signin`)
   - Route: `signin`
   - View: `auth.signin`
   - Controller: `AuthController@showSignIn`
   - Deskripsi: Halaman login untuk user

2. **Sign Up** (`/signup`)
   - Route: `signup`
   - View: `auth.signup`
   - Controller: `AuthController@showSignUp`
   - Deskripsi: Halaman registrasi user baru

### 🏠 Public Pages
3. **Home** (`/`)
   - Route: `home`
   - View: `home`
   - Controller: `HomeController@index`
   - Deskripsi: Halaman utama, redirect berdasarkan role user
   - Redirect:
     - Admin → `/admin/dashboard`
     - Artist → `/artist/dashboard`
     - Customer → `/dashboard`

4. **All Products** (`/products`)
   - Route: `products.index`
   - View: `products.index`
   - Controller: `ProductController@index`
   - Deskripsi: Halaman menampilkan semua produk dengan filter

5. **Product Detail** (`/products/{id}`)
   - Route: `products.show`
   - View: `products.show`
   - Controller: `ProductController@show`
   - Deskripsi: Halaman detail produk

6. **Artist Public Profile** (`/artist/{id}`)
   - Route: `artist.show`
   - View: `artist.show`
   - Controller: `ArtistController@show`
   - Deskripsi: Halaman profil artist untuk public (tanpa login)

### 👤 Customer Pages (Auth Required)
7. **Customer Dashboard** (`/dashboard`)
   - Route: `customer.dashboard`
   - View: `customer.dashboard`
   - Controller: `CustomerDashboardController@index`
   - Deskripsi: Dashboard untuk customer, menampilkan order progress, spending, reward points, dll

8. **Cart** (`/cart`)
   - Route: `cart.index`
   - View: `cart.index`
   - Controller: `CartController@index`
   - Deskripsi: Halaman keranjang belanja

9. **Payment** (`/payment`)
   - Route: `payment.index`
   - View: `payment.index`
   - Controller: `PaymentController@index`
   - Deskripsi: Halaman checkout dan payment

10. **Payment Detail** (`/payment/{orderId}`)
    - Route: `payment.show`
    - View: `payment.show`
    - Controller: `PaymentController@show`
    - Deskripsi: Halaman detail payment dengan metode pembayaran

11. **Profile** (`/profile`)
    - Route: `profile.index`
    - View: `profile.index`
    - Controller: `ProfileController@index`
    - Deskripsi: Halaman profil customer untuk edit data

12. **Shipping/Tracking** (`/shipping`)
    - Route: `shipping.index`
    - View: `shipping.index`
    - Controller: `ShippingController@index`
    - Deskripsi: Halaman tracking order dan shipping

13. **Shipping Tracking Detail** (`/shipping/track/{orderId}`)
    - Route: `shipping.tracking`
    - View: `shipping.tracking`
    - Controller: `ShippingController@tracking`
    - Deskripsi: Halaman detail tracking order

14. **Wishlist** (`/wishlist`)
    - Route: `wishlist.index`
    - View: `wishlist.index`
    - Controller: `WishlistController@index`
    - Deskripsi: Halaman wishlist/favorit customer

### 🎨 Artist Pages (Auth Required)
15. **Artist Dashboard** (`/artist/dashboard`)
    - Route: `artist.dashboard`
    - View: `artist.dashboard`
    - Controller: `ArtistController@dashboard`
    - Deskripsi: Dashboard artist dengan statistik penjualan, total products, dll

16. **Artist Profile & Analytics** (`/artist/profile`)
    - Route: `artist.profile`
    - View: `artist.profile`
    - Controller: `ArtistController@profile`
    - Deskripsi: Halaman profil artist dengan analytics detail produk

### 👨‍💼 Admin Pages (Auth Required)
17. **Admin Dashboard** (`/admin/dashboard`)
    - Route: `admin.dashboard`
    - View: `admin.dashboard`
    - Controller: `AdminController@dashboard`
    - Deskripsi: Dashboard admin dengan statistik keseluruhan (orders, users, sales, dll)

18. **Admin Profile & Analytics** (`/admin/profile`)
    - Route: `admin.profile`
    - View: `admin.profile`
    - Controller: `AdminController@profile`
    - Deskripsi: Halaman profil admin dengan analytics

19. **All Products (Admin)** (`/admin/products`)
    - Route: `admin.all-products`
    - View: `admin.all-products`
    - Controller: `AdminController@allProducts`
    - Deskripsi: Halaman manajemen semua produk dengan filter dan search

20. **Add Product** (`/admin/products/add`)
    - Route: `admin.add-product`
    - View: `admin.add-product`
    - Controller: `AdminController@addProduct`
    - Deskripsi: Halaman tambah produk baru dengan upload gambar

21. **Edit Product** (`/admin/products/{id}/edit`)
    - Route: `admin.edit-product`
    - View: `admin.edit-product`
    - Controller: `AdminController@editProduct`
    - Deskripsi: Halaman edit produk

22. **All Orders (Admin)** (`/admin/orders`)
    - Route: `admin.all-orders`
    - View: `admin.all-orders`
    - Controller: `AdminController@allOrders`
    - Deskripsi: Halaman manajemen semua order dengan filter status

23. **Earning (Admin)** (`/admin/earning`)
    - Route: `admin.earning`
    - View: `admin.earning`
    - Controller: `AdminController@earning`
    - Deskripsi: Halaman laporan earning berdasarkan produk, kategori, dan artist

## 🔄 Route Actions (POST/PUT/DELETE)

### Authentication
- `POST /signin` - Login user
- `POST /signup` - Register user baru
- `POST /signout` - Logout user

### Cart Actions
- `POST /cart` - Tambah item ke cart
- `PUT /cart/{id}` - Update quantity cart
- `DELETE /cart/{id}` - Hapus item dari cart

### Payment Actions
- `POST /payment/checkout` - Proses checkout
- `POST /payment/{orderId}/process` - Proses payment

### Profile Actions
- `PUT /profile` - Update profil user
- `PUT /profile/password` - Update password

### Wishlist Actions
- `POST /wishlist/toggle` - Toggle wishlist item
- `DELETE /wishlist/{id}` - Hapus dari wishlist

### Comment Actions
- `POST /comments` - Tambah komentar
- `DELETE /comments/{id}` - Hapus komentar

### Admin Product Actions
- `POST /admin/products` - Simpan produk baru
- `PUT /admin/products/{id}` - Update produk
- `DELETE /admin/products/{id}` - Hapus produk

### Admin Order Actions
- `PUT /admin/orders/{id}/status` - Update status order

## 📊 Role-Based Access

### Public (No Auth)
- Home (redirect ke signin jika belum login)
- All Products
- Product Detail
- Artist Public Profile
- Sign In / Sign Up

### Customer (Auth Required)
- Customer Dashboard
- Cart
- Payment
- Profile
- Shipping/Tracking
- Wishlist
- All Products (browse)
- Product Detail

### Artist (Auth Required)
- Artist Dashboard
- Artist Profile & Analytics
- All Products (browse)
- Product Detail
- Cart (bisa beli juga)
- Wishlist
- Shipping/Tracking

### Admin (Auth Required)
- Admin Dashboard
- Admin Profile & Analytics
- All Products (manage)
- Add Product
- Edit Product
- Delete Product
- All Orders (manage)
- Update Order Status
- Earning Reports

## 🎯 Navigation Structure

### Navbar Menu (Based on Role)

**Admin:**
- Admin Dashboard
- Admin Profile & Analytics
- All Products (manage)
- Add Product
- All Orders
- Earning
- Edit Profile

**Artist:**
- Artist Dashboard
- Seller Profile & Analytics
- All Products (browse)
- My Cart
- Wishlist
- My Orders
- Track Order
- Edit Profile

**Customer:**
- Dashboard
- All Products
- My Cart
- Wishlist
- My Orders
- Track Order
- My Profile

## ✅ Status Implementasi

Semua halaman sudah diimplementasikan dan dapat diakses sesuai dengan role masing-masing user.

