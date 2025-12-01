# HTTPS Setup untuk Railway/Render/Heroku

File ini menjelaskan setup HTTPS yang sudah dilakukan untuk mengatasi masalah "not secure" dan CSRF 419 error.

## ✅ Yang Sudah Dibuat

### 1. TrustProxies Middleware
**File:** `app/Http/Middleware/TrustProxies.php`

Middleware ini membuat Laravel percaya bahwa request HTTPS dari proxy Railway/Render benar-benar HTTPS.

### 2. AppServiceProvider
**File:** `app/Providers/AppServiceProvider.php`

Menambahkan `URL::forceScheme('https')` untuk memaksa Laravel menggunakan HTTPS di production.

### 3. Bootstrap App
**File:** `bootstrap/app.php`

Mendaftarkan TrustProxies middleware dengan `trustProxies(at: '*')`.

## 🔧 Setup di Railway

### Environment Variables
Pastikan di Railway dashboard, set environment variables:

```
APP_ENV=production
APP_URL=https://your-app.railway.app
APP_DEBUG=false
```

### Setelah Deploy
1. Clear config cache (jika perlu):
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

2. Test form submission - seharusnya tidak ada warning "not secure" lagi

## 🧪 Testing

1. Buka aplikasi di browser: `https://your-app.railway.app`
2. Coba submit form (login, add product, dll)
3. Seharusnya:
   - ✅ Tidak ada warning "not secure"
   - ✅ Form action menggunakan HTTPS
   - ✅ Tidak ada CSRF 419 error
   - ✅ Redirect menggunakan HTTPS

## 📝 Catatan

- TrustProxies dengan `'*'` berarti mempercayai semua proxy (aman untuk Railway/Render)
- `URL::forceScheme('https')` hanya aktif di production/staging
- Di local development, tetap menggunakan HTTP (tidak terpengaruh)

## 🐛 Troubleshooting

Jika masih ada masalah:

1. **Cek APP_ENV di Railway:**
   - Harus `production` atau `staging`
   - Bukan `local` atau `development`

2. **Cek APP_URL:**
   - Harus menggunakan `https://`
   - Contoh: `https://totoro-production.up.railway.app`

3. **Clear cache setelah deploy:**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

4. **Cek browser console:**
   - Lihat apakah masih ada mixed content warning
   - Pastikan semua asset menggunakan HTTPS



