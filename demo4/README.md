# FlipPlay Modul 4 - REST API

API Laravel untuk data media dan koleksi pribadi pengguna, melanjutkan model data FlipPlay Modul 3. Repository GitHub dan project Laravel sudah tersedia; tidak ada repository baru yang dibuat.

## Menjalankan aplikasi

```powershell
cd demo4
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Server berjalan di `http://127.0.0.1:8000`. Seeder menambahkan tiga media dan satu pengguna, Rizal (`rizal@example.com`, password `password`), serta data koleksi. Akun tersebut hanya untuk pengembangan lokal.

## Endpoint

### Media

| Method | Endpoint | Keterangan |
| --- | --- | --- |
| `GET` | `/api/media` | Daftar media berurutan berdasarkan ID menaik; opsional `search`, `type`, `sort`, `direction` |
| `POST` | `/api/media` | Tambah media |
| `GET` | `/api/media/{id}` | Detail media |
| `GET` | `/api/media/{id}/users` | Pengguna yang menyimpan media |
| `PUT`, `PATCH` | `/api/media/{id}` | Perbarui media |
| `DELETE` | `/api/media/{id}` | Hapus media |
| `GET` | `/api/users` | Daftar pengguna (ID, nama, dan email) |
| `POST` | `/api/users` | Tambah pengguna dengan `name` dan `email`; `password` opsional |
| `PATCH` | `/api/users/{id}` | Perbarui pengguna |
| `DELETE` | `/api/users/{id}` | Hapus pengguna |
| `GET` | `/api/users/{userId}/collection` | Lihat koleksi pengguna |
| `POST` | `/api/users/{userId}/collection/{mediaId}` | Tambah media ke koleksi pengguna |
| `DELETE` | `/api/users/{userId}/collection/{mediaId}` | Hapus media dari koleksi pengguna |

Tipe yang diterima: `anime`, `drama`, `manga`, dan `novel`. Respons media menggunakan `MediaResource`; validasi create/update menggunakan Form Request dan error validasi dikirim sebagai JSON `422` dengan `message` dan `errors`. Update kosong ditolak; `slug`, ID AniList, dan email yang duplikat juga menghasilkan `422`. Slug media otomatis dibuat dari judul jika tidak dikirim.

Endpoint `POST`, `PATCH`, dan `DELETE /api/users` tidak memerlukan token untuk memudahkan demo lokal. Lindungi endpoint ini sebelum aplikasi dipublikasikan. Untuk membuat akun yang dapat langsung login dan memperoleh token, gunakan `POST /api/auth/register` dengan `name`, `email`, `password`, dan `password_confirmation`. Pada `POST /api/users`, jika `password` tidak diberikan, sistem membuat password acak.

Contoh payload:

```json
{
  "title": "Blue Period",
  "type": "anime",
  "episodes": 12,
  "year": 2021,
  "score": 8.2,
  "genres": ["Drama", "Slice of Life"]
}
```

### Autentikasi dan koleksi

| Method | Endpoint | Akses |
| --- | --- | --- |
| `POST` | `/api/auth/register` | Publik |
| `POST` | `/api/auth/login` | Publik |
| `GET` | `/api/me` | Token |
| `POST` | `/api/auth/logout` | Token |
| `GET` | `/api/collection` | Token |
| `PUT` | `/api/collection/{mediaId}` | Token |
| `DELETE` | `/api/collection/{mediaId}` | Token |

Sertakan `Authorization: Bearer <token>` untuk endpoint bertanda Token. Registrasi memerlukan `name`, `email`, `password`, dan `password_confirmation`. Untuk menambahkan media ke koleksi, kirim `status` (`planned`, `watching`, `completed`, atau `dropped`) dan opsional `progress`.

Pengguna dapat menyimpan beberapa media dengan memanggil `PUT /api/collection/{mediaId}` untuk tiap media. Media yang sama juga dapat disimpan oleh banyak pengguna; daftar penggunanya tersedia di `GET /api/media/{mediaId}/users`.

Untuk menambah media ke koleksi user tertentu, gunakan `POST /api/users/{userId}/collection/{mediaId}` dengan body JSON opsional `{"status":"planned","progress":0}`. Tanpa body, status otomatis `planned`.

## Verifikasi

```powershell
php artisan route:list --path=api
php artisan test
```
