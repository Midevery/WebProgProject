# Seller/Artist Login Information

## Seller Accounts yang Sudah Ada Produk

Berikut adalah daftar seller/artist yang sudah bisa login dan memiliki produk:

### 1. Joma
- **Username:** `joma`
- **Email:** `joma@kisora.com`
- **Password:** `AAAaaa123`
- **Status:** ✅ Sudah ada produk

### 2. Bobo
- **Username:** `bobo`
- **Email:** `bobo@kisora.com`
- **Password:** `AAAaaa123`
- **Status:** ✅ Sudah ada produk

### 3. Steven he
- **Username:** `stevenhe`
- **Email:** `stevenhe@kisora.com`
- **Password:** `AAAaaa123`
- **Status:** ✅ Sudah ada produk

### 4. Sakura Art
- **Username:** `sakura_art`
- **Email:** `sakura@kisora.com`
- **Password:** `AAAaaa123`
- **Status:** ✅ Sudah ada produk

### 5. Moonlight Illustrator
- **Username:** `moonlight_illust`
- **Email:** `moonlight@kisora.com`
- **Password:** `AAAaaa123`
- **Status:** ✅ Sudah ada produk

### 6. Star Drawer
- **Username:** `star_drawer`
- **Email:** `star@kisora.com`
- **Password:** `AAAaaa123`
- **Status:** ✅ Sudah ada produk

### 7. Anime Master
- **Username:** `anime_master`
- **Email:** `anime@kisora.com`
- **Password:** `AAAaaa123`
- **Status:** ✅ Sudah ada produk

### 8. Digital Artist
- **Username:** `digital_artist`
- **Email:** `digital@kisora.com`
- **Password:** `AAAaaa123`
- **Status:** ✅ Sudah ada produk

### 9. Manga Creator
- **Username:** `manga_creator`
- **Email:** `manga@kisora.com`
- **Password:** `AAAaaa123`
- **Status:** ✅ Sudah ada produk

### 10. Art Studio
- **Username:** `art_studio`
- **Email:** `studio@kisora.com`
- **Password:** `AAAaaa123`
- **Status:** ✅ Sudah ada produk

## Cara Login

1. Buka halaman login: `/signin`
2. Masukkan **Username** atau **Email** salah satu seller di atas
3. Masukkan **Password:** `AAAaaa123`
4. Setelah login, akan langsung redirect ke **Artist Dashboard** yang menampilkan produk mereka

## Menjalankan Seeder

Jika produk belum ada, jalankan:

```bash
php artisan db:seed --class=ArtistProductSeeder
```

Atau jalankan semua seeder:

```bash
php artisan db:seed
```

## Catatan

- Semua seller menggunakan password yang sama: `AAAaaa123`
- Setiap seller memiliki 3-5 produk yang sudah dibuat
- Total produk untuk semua seller: 40 produk
- Setelah login, seller akan langsung diarahkan ke dashboard mereka

