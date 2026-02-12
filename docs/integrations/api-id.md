# Dokumentasi Teknis Integrasi API ID

Dokumentasi ini mencakup detail teknis integrasi sistem EPI-OSS dengan layanan pihak ketiga **API ID** (`api.co.id`). Layanan ini digunakan sebagai penyedia data wilayah administratif Indonesia (Regional) dan kalkulasi ongkos kirim (Shipping Cost).

## 1. Informasi Umum

*   **Provider**: API ID
*   **Base URL (Default)**: `https://use.api.co.id`
*   **Protokol**: HTTPS
*   **Format Data**: JSON
*   **Service Class**: `App\Services\ApiIdService`

## 2. Autentikasi

Setiap request ke API ID wajib menyertakan API Key yang valid melalui HTTP Header.

| Header Name | Value | Deskripsi |
| :--- | :--- | :--- |
| `x-api-co-id` | `{YOUR_API_KEY}` | Kunci akses API yang didapatkan dari dashboard API ID. |

**Contoh Implementasi Header:**
```php
Http::withHeaders([
    'x-api-co-id' => 'API_KEY_ANDA'
])->get(...)
```

## 3. Daftar Endpoint

### A. Data Wilayah (Regional Data)

API ini menyediakan hierarki wilayah administratif Indonesia: Provinsi → Kota/Kabupaten → Kecamatan → Kelurahan/Desa.

#### 1. Ambil Data Provinsi
Mengambil daftar seluruh provinsi di Indonesia.

*   **Endpoint**: `/regional/indonesia/provinces`
*   **Method**: `GET`
*   **Parameter**: Tidak ada

**Contoh Response:**
```json
{
    "data": [
        {
            "code": "11",
            "name": "ACEH"
        },
        {
            "code": "12",
            "name": "SUMATERA UTARA"
        }
    ]
}
```

#### 2. Ambil Data Kota/Kabupaten
Mengambil daftar kota/kabupaten berdasarkan kode provinsi.

*   **Endpoint**: `/regional/indonesia/regencies`
*   **Method**: `GET`
*   **Parameter**:
    *   `province_code` (Wajib): Kode Provinsi (misal: `11`)

**Contoh Response:**
```json
{
    "data": [
        {
            "code": "1101",
            "province_code": "11",
            "name": "KAB. ACEH SELATAN"
        }
    ]
}
```

#### 3. Ambil Data Kecamatan (District)
Mengambil daftar kecamatan berdasarkan kode kota/kabupaten.

*   **Endpoint**: `/regional/indonesia/districts`
*   **Method**: `GET`
*   **Parameter**:
    *   `regency_code` (Wajib): Kode Kota/Kabupaten (misal: `1101`)

**Contoh Response:**
```json
{
    "data": [
        {
            "code": "110101",
            "regency_code": "1101",
            "name": "BAKONGAN"
        }
    ]
}
```

#### 4. Ambil Data Kelurahan/Desa (Village)
Mengambil daftar kelurahan/desa berdasarkan kode kecamatan.

*   **Endpoint**: `/regional/indonesia/villages`
*   **Method**: `GET`
*   **Parameter**:
    *   `district_code` (Wajib): Kode Kecamatan (misal: `110101`)

**Contoh Response:**
```json
{
    "data": [
        {
            "code": "1101012001",
            "district_code": "110101",
            "name": "KEUDE BAKONGAN"
        }
    ]
}
```

### B. Ongkos Kirim (Shipping Cost)

#### Hitung Biaya Pengiriman
Mengambil daftar layanan kurir dan biaya pengiriman antar dua kelurahan.

*   **Endpoint**: `/expedition/shipping-cost`
*   **Method**: `GET`
*   **Parameter**:

| Parameter | Tipe | Wajib | Deskripsi |
| :--- | :--- | :--- | :--- |
| `origin_village_code` | String | Ya | Kode Kelurahan asal pengiriman |
| `destination_village_code` | String | Ya | Kode Kelurahan tujuan pengiriman |
| `weight` | Integer | Ya | Berat paket dalam satuan **Kilogram (kg)** |

**Catatan Khusus**:
Sistem API ID mewajibkan berat dalam satuan Kg. Dalam implementasi `ApiIdService`, berat dalam gram akan dibulatkan ke atas (ceil) ke Kg terdekat.
*   500 gram → 1 kg
*   1200 gram → 2 kg

**Contoh Response:**
```json
{
    "data": {
        "origin_village_code": "3172051003",
        "destination_village_code": "3204402005",
        "weight": 1,
        "couriers": [
            {
                "courier_code": "jne",
                "courier_name": "JNE Express",
                "service": "REG",
                "description": "Layanan Reguler",
                "price": 12000,
                "estimation": "1-2 Hari"
            },
            {
                "courier_code": "sicepat",
                "courier_name": "SiCepat Ekspres",
                "service": "SIUNT",
                "description": "SiUntung",
                "price": 11500,
                "estimation": "2-3 Hari"
            }
        ]
    }
}
```

## 4. Kode Status & Error Handling

Berikut adalah kode status HTTP yang umum dikembalikan oleh API:

| Kode Status | Keterangan | Penyebab Umum |
| :--- | :--- | :--- |
| `200 OK` | Berhasil | Request valid dan data ditemukan. |
| `401 Unauthorized` | Gagal Autentikasi | API Key salah, tidak dikirim, atau kadaluarsa. |
| `404 Not Found` | Tidak Ditemukan | Endpoint salah atau data wilayah tidak ditemukan. |
| `500 Internal Error` | Server Error | Gangguan pada sisi server API ID. |

**Struktur Response Error:**
```json
{
    "success": false,
    "message": "Connection Failed: Invalid API Key",
    "status_code": 401
}
```

## 5. Implementasi Client-Side (Best Practices)

Berikut adalah panduan untuk tim developer dalam mengimplementasikan integrasi ini:

### a. Caching Data Wilayah
Data wilayah (Provinsi, Kota, Kecamatan) bersifat statis dan jarang berubah. Sangat disarankan untuk menyimpan response API ini ke dalam database lokal atau cache (Redis) untuk mengurangi latensi dan penggunaan kuota API.

### b. Strategi Pencarian Lokasi
Pencarian lokasi (Destination Search) pada `ApiIdService` menggunakan strategi *fallback*:
1.  Coba cari berdasarkan nama Kelurahan (`/villages?name=query`).
2.  Jika tidak ditemukan, cari berdasarkan nama Kecamatan (`/districts?name=query`).
3.  Jika query mengandung spasi, coba cari berdasarkan kata terakhir.

### c. Penanganan Timeout
Selalu bungkus panggilan API dalam blok `try-catch` untuk menangani kemungkinan *timeout* atau *connection error*.

### Contoh Kode (PHP/Laravel)

```php
use Illuminate\Support\Facades\Http;

class ShippingService {
    
    public function getShippingCost($originCode, $destCode, $weightGram) {
        $apiKey = config('services.api_id.key');
        $baseUrl = 'https://use.api.co.id';
        
        // Konversi gram ke kg (min 1kg)
        $weightKg = ceil($weightGram / 1000);
        $weightKg = ($weightKg < 1) ? 1 : $weightKg;

        try {
            $response = Http::withHeaders([
                'x-api-co-id' => $apiKey
            ])->get($baseUrl . '/expedition/shipping-cost', [
                'origin_village_code' => $originCode,
                'destination_village_code' => $destCode,
                'weight' => $weightKg
            ]);

            if ($response->successful()) {
                return $response->json('data.couriers');
            }
            
            // Handle error
            Log::error('API ID Error: ' . $response->body());
            return [];
            
        } catch (\Exception $e) {
            Log::error('API ID Connection Failed: ' . $e->getMessage());
            return [];
        }
    }
}
```
