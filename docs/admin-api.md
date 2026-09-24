# Admin API Documentation

> Base URL: `{{API_BASE_URL}}/api`
> Authentication: Bearer Token (Laravel Sanctum)
> Content-Type: `application/json` (kecuali file upload)

---

## Table of Contents

1. [Authentication](#1-authentication)
2. [Dashboard](#2-dashboard)
3. [User Management](#3-user-management)
4. [KYC Verification](#4-kyc-verification)
5. [Withdrawal Management](#5-withdrawal-management)
6. [Payment Monitoring](#6-payment-monitoring)
7. [Contract Audit](#7-contract-audit)
8. [Error Handling](#8-error-handling)

---

## Common Response Format

### Success (single resource)

```json
{
  "success": true,
  "message": "Deskripsi sukses.",
  "data": { ... }
}
```

### Success (paginated)

```json
{
  "success": true,
  "message": "Deskripsi sukses.",
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  }
}
```

### Error

```json
{
  "success": false,
  "message": "Deskripsi error.",
  "errors": {
    "field": ["Detail validasi per field."]
  }
}
```

---

## 1. Authentication

### 1.1 Login

> **POST** `/admin/auth/login`
> 🔓 Public (no token required)

**Request Body:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `email` | string | ✅ | valid email |
| `password` | string | ✅ | — |

**Example:**

```json
{
  "email": "admin@kostapp.com",
  "password": "secret123"
}
```

**Response 200:**

```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "access_token": "1|abc123...",
    "user": {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "Super Admin",
      "email": "admin@kostapp.com",
      "role": "admin",
      "status_verification": "verified"
    }
  }
}
```

**Error Responses:**

| Code | Message |
|------|---------|
| 401 | Kredensial tidak valid. |
| 403 | Akses ditolak. Endpoint ini khusus Super Admin. |
| 422 | Validasi gagal (field-level errors). |

---

### 1.2 Get Current User

> **GET** `/admin/auth/me`
> 🔒 Requires `Authorization: Bearer <token>`

**Response 200:**

```json
{
  "success": true,
  "message": "Data pengguna berhasil diambil.",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Super Admin",
    "email": "admin@kostapp.com",
    "role": "admin",
    "phone_number": "08123456789",
    "status_verification": "verified",
    "profile_picture": "https://storage.example.com/profiles/admin.jpg",
    "created_at": "2026-09-10T10:00:00.000000Z"
  }
}
```

---

### 1.3 Logout

> **POST** `/admin/auth/logout`
> 🔒 Requires `Authorization: Bearer <token>`

**Response 200:**

```json
{
  "success": true,
  "message": "Logout berhasil."
}
```

---

## 2. Dashboard

### 2.1 Get Dashboard Summary

> **GET** `/admin/dashboard`
> 🔒 Requires `Authorization: Bearer <token>`

**Response 200:**

```json
{
  "success": true,
  "message": "Dashboard data retrieved.",
  "data": {
    "financial": {
      "total_revenue": 50000000,
      "total_transactions": 420,
      "total_withdrawn": 12000000,
      "total_withdrawals": 85,
      "net_platform": 38000000
    },
    "queues": {
      "pending_withdrawals": 3,
      "pending_kyc": 5
    },
    "entities": {
      "total_owners": 12,
      "active_tenants": 35,
      "total_kosts": 20
    },
    "occupancy": {
      "total_rooms": 120,
      "occupied": 85,
      "available": 30,
      "maintenance": 5,
      "occupancy_rate": 70.83
    },
    "monthly_transactions": [
      { "month": "2025-10", "revenue": 12000000, "count": 100 },
      { "month": "2025-11", "revenue": 9800000, "count": 82 },
      { "month": "2025-12", "revenue": 11500000, "count": 95 },
      { "month": "2026-01", "revenue": 10200000, "count": 88 },
      { "month": "2026-02", "revenue": 13000000, "count": 108 },
      { "month": "2026-03", "revenue": 11800000, "count": 98 },
      { "month": "2026-04", "revenue": 12500000, "count": 105 },
      { "month": "2026-05", "revenue": 14000000, "count": 115 },
      { "month": "2026-06", "revenue": 13200000, "count": 110 },
      { "month": "2026-07", "revenue": 15000000, "count": 125 },
      { "month": "2026-08", "revenue": 14500000, "count": 118 },
      { "month": "2026-09", "revenue": 12000000, "count": 96 }
    ]
  }
}
```

**Field Reference:**

| Section | Field | Description |
|---------|-------|-------------|
| `financial` | `total_revenue` | Total nominal pembayaran `completed` |
| | `total_transactions` | Jumlah transaksi `completed` |
| | `total_withdrawn` | Total penarikan `approved` |
| | `total_withdrawals` | Jumlah penarikan `approved` |
| | `net_platform` | `total_revenue` - `total_withdrawn` |
| `queues` | `pending_withdrawals` | Antrean penarikan dana |
| | `pending_kyc` | Antrean verifikasi KYC baru |
| `entities` | `total_owners` | Total user role `owner` |
| | `active_tenants` | User dengan kontrak `active` |
| | `total_kosts` | Total properti kost |
| `occupancy` | `total_rooms` | Seluruh kamar |
| | `occupied` | Kamar terisi |
| | `available` | Kamar kosong |
| | `maintenance` | Kamar dalam perawatan |
| | `occupancy_rate` | Persentase okupansi (0-100) |
| `monthly_transactions` | `month` | Format `YYYY-MM`, 12 bulan terakhir |
| | `revenue` | Total pendapatan bulan tsb |
| | `count` | Jumlah transaksi bulan tsb |

---

## 3. User Management

### 3.1 List Users

> **GET** `/admin/users`
> 🔒 Requires admin token

**Query Parameters:**

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `role` | string | ❌ | Filter: `admin`, `owner`, `user` |
| `status_verification` | string | ❌ | Filter: `unverified`, `pending`, `verified`, `rejected` |
| `search` | string | ❌ | Pencarian di `name`, `email`, `phone_number` |
| `per_page` | integer | ❌ | 1-100, default `15` |

**Example:** `GET /admin/users?role=owner&status_verification=verified&per_page=10`

**Response 200 (paginated):**

```json
{
  "success": true,
  "message": "Daftar pengguna berhasil diambil.",
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "role": "owner",
      "phone_number": "08123456789",
      "status_verification": "verified",
      "bank_name": "BCA",
      "bank_account_number": "1234567890",
      "bank_account_holder": "Budi Santoso",
      "profile_picture": null,
      "created_at": "2026-09-10T10:00:00.000000Z",
      "updated_at": "2026-09-10T10:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 48
  }
}
```

---

### 3.2 Create Owner Account

> **POST** `/admin/users/owners`
> 🔒 Requires admin token

**Request Body:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `name` | string | ✅ | max 255 chars |
| `email` | string | ✅ | valid email, unique in `users` |
| `password` | string | ✅ | min 8 chars |
| `phone_number` | string | ✅ | max 20 chars |
| `auto_verified` | boolean | ❌ | Default `false`. Jika `true`, langsung verified. |
| `bank_name` | string | ❌ | max 100 chars |
| `bank_account_number` | string | ❌ | max 50 chars |
| `bank_account_holder` | string | ❌ | max 255 chars |

**Example:**

```json
{
  "name": "Budi Santoso",
  "email": "budi@example.com",
  "password": "securepass",
  "phone_number": "08123456789",
  "auto_verified": true,
  "bank_name": "BCA",
  "bank_account_number": "1234567890",
  "bank_account_holder": "Budi Santoso"
}
```

**Response 201:**

```json
{
  "success": true,
  "message": "Akun owner berhasil dibuat.",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "role": "owner",
    "phone_number": "08123456789",
    "status_verification": "verified",
    "bank_name": "BCA",
    "bank_account_number": "1234567890",
    "bank_account_holder": "Budi Santoso",
    "profile_picture": null,
    "created_at": "2026-09-12T01:00:00.000000Z",
    "updated_at": "2026-09-12T01:00:00.000000Z"
  }
}
```

---

### 3.3 Get User Detail

> **GET** `/admin/users/{id}`
> 🔒 Requires admin token

**Path Parameter:** `id` (UUID)

**Response 200:** Sama dengan format item di [List Users](#31-list-users).

**Error 404:**

```json
{
  "success": false,
  "message": "Pengguna tidak ditemukan."
}
```

---

### 3.4 Update User

> **PUT** `/admin/users/{id}`
> 🔒 Requires admin token

**Path Parameter:** `id` (UUID)

**Request Body (semua field optional):**

| Field | Type | Rules |
|-------|------|-------|
| `name` | string | max 255 chars |
| `email` | string | valid email, unique (ignore current user) |
| `phone_number` | string | max 20 chars |
| `role` | string | `admin`, `owner`, `user` |
| `password` | string | min 8 chars |

**Example:**

```json
{
  "name": "Budi Santoso updated",
  "role": "owner"
}
```

**Response 200:** Sama dengan format item di [List Users](#31-list-users).

---

### 3.5 Delete User

> **DELETE** `/admin/users/{id}`
> 🔒 Requires admin token

**Path Parameter:** `id` (UUID)

**Response 200:**

```json
{
  "success": true,
  "message": "Pengguna berhasil dihapus."
}
```

**Error Responses:**

| Code | Message |
|------|---------|
| 403 | Admin tidak dapat menghapus akun sendiri. |
| 404 | Pengguna tidak ditemukan. |

---

## 4. KYC Verification

### 4.1 List Pending KYC

> **GET** `/admin/kyc`
> 🔒 Requires admin token

Hanya mengembalikan user dengan `status_verification = 'pending'`.

**Query Parameters:**

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `search` | string | ❌ | Pencarian di `name`, `email`, `no_ktp`, `phone_number` |
| `per_page` | integer | ❌ | 1-100, default `15` |

**Example:** `GET /admin/kyc?search=budi&per_page=10`

**Response 200 (paginated):**

```json
{
  "success": true,
  "message": "Antrean verifikasi KYC berhasil diambil.",
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "phone_number": "08123456789",
      "no_ktp": "3201234567890001",
      "npwp": "123456789012345",
      "ktp_picture": "https://storage.example.com/ktp/budi.jpg",
      "ktp_picture_person": "https://storage.example.com/ktp-person/budi.jpg",
      "status_verification": "pending",
      "rejection_feedback": null,
      "created_at": "2026-09-10T10:00:00.000000Z",
      "updated_at": "2026-09-11T08:30:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 10,
    "total": 5
  }
}
```

---

### 4.2 Get KYC Detail

> **GET** `/admin/kyc/{id}`
> 🔒 Requires admin token

**Response 200:** Sama dengan format item di [List Pending KYC](#41-list-pending-kyc).

**Error 404:**

```json
{
  "success": false,
  "message": "Pengguna tidak ditemukan."
}
```

---

### 4.3 Approve KYC

> **POST** `/admin/kyc/{id}/approve`
> 🔒 Requires admin token
> 📝 Tidak ada request body.

**Response 200:**

```json
{
  "success": true,
  "message": "KYC pengguna berhasil disetujui.",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "phone_number": "08123456789",
    "no_ktp": "3201234567890001",
    "npwp": "123456789012345",
    "ktp_picture": "https://storage.example.com/ktp/budi.jpg",
    "ktp_picture_person": "https://storage.example.com/ktp-person/budi.jpg",
    "status_verification": "verified",
    "rejection_feedback": null,
    "created_at": "2026-09-10T10:00:00.000000Z",
    "updated_at": "2026-09-12T01:00:00.000000Z"
  }
}
```

**Error Responses:**

| Code | Message |
|------|---------|
| 404 | Pengguna tidak ditemukan. |
| 422 | Hanya pengguna dengan status pending yang dapat disetujui. |

---

### 4.4 Reject KYC

> **POST** `/admin/kyc/{id}/reject`
> 🔒 Requires admin token

**Request Body:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `rejection_feedback` | string | ✅ | min 10, max 1000 chars |

**Example:**

```json
{
  "rejection_feedback": "Foto KTP tidak jelas, mohon unggah ulang dengan kualitas lebih baik."
}
```

**Response 200:**

```json
{
  "success": true,
  "message": "KYC pengguna berhasil ditolak.",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "phone_number": "08123456789",
    "no_ktp": "3201234567890001",
    "npwp": "123456789012345",
    "ktp_picture": "https://storage.example.com/ktp/budi.jpg",
    "ktp_picture_person": "https://storage.example.com/ktp-person/budi.jpg",
    "status_verification": "rejected",
    "rejection_feedback": "Foto KTP tidak jelas, mohon unggah ulang dengan kualitas lebih baik.",
    "created_at": "2026-09-10T10:00:00.000000Z",
    "updated_at": "2026-09-12T01:00:00.000000Z"
  }
}
```

**Error Responses:**

| Code | Message |
|------|---------|
| 404 | Pengguna tidak ditemukan. |
| 422 | Hanya pengguna dengan status pending yang dapat ditolak. / Catatan penolakan wajib diisi. / min 10 chars. |

---

## 5. Withdrawal Management

### 5.1 List Withdrawals

> **GET** `/admin/withdrawals`
> 🔒 Requires admin token

**Query Parameters:**

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `status` | string | ❌ | Filter: `pending`, `approved`, `rejected` |
| `per_page` | integer | ❌ | 1-100, default `15` |

**Example:** `GET /admin/withdrawals?status=pending&per_page=10`

**Response 200 (paginated):**

```json
{
  "success": true,
  "message": "Daftar permohonan pencairan dana berhasil diambil.",
  "data": [
    {
      "id": "660e8400-e29b-41d4-a716-446655440001",
      "owner_id": "550e8400-e29b-41d4-a716-446655440000",
      "amount": "2500000.00",
      "target_bank": "BCA",
      "target_account_number": "1234567890",
      "target_account_holder": "Budi Santoso",
      "proof": null,
      "status": "pending",
      "rejection_reason": null,
      "owner": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "Budi Santoso",
        "email": "budi@example.com",
        "bank_name": "BCA",
        "bank_account_number": "1234567890",
        "bank_account_holder": "Budi Santoso",
        "balance": "5000000.00"
      },
      "created_at": "2026-09-11T14:00:00.000000Z",
      "updated_at": "2026-09-11T14:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 3
  }
}
```

---

### 5.2 Get Withdrawal Detail

> **GET** `/admin/withdrawals/{id}`
> 🔒 Requires admin token

**Response 200:** Sama dengan format item di [List Withdrawals](#51-list-withdrawals).

**Error 404:**

```json
{
  "success": false,
  "message": "Permohonan pencairan dana tidak ditemukan."
}
```

---

### 5.3 Approve Withdrawal

> **POST** `/admin/withdrawals/{id}/approve`
> 🔒 Requires admin token
> 📝 Content-Type: `multipart/form-data`

**Form Fields:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `proof` | file | ✅ | `mimes:jpg,jpeg,png,pdf`, max 5MB |

**Example (cURL):**

```bash
curl -X POST "https://api.example.com/api/admin/withdrawals/{id}/approve" \
  -H "Authorization: Bearer {token}" \
  -F "proof=@/path/to/bukti_transfer.pdf"
```

**Response 200:**

```json
{
  "success": true,
  "message": "Permohonan pencairan dana berhasil disetujui.",
  "data": {
    "id": "660e8400-e29b-41d4-a716-446655440001",
    "owner_id": "550e8400-e29b-41d4-a716-446655440000",
    "amount": "2500000.00",
    "target_bank": "BCA",
    "target_account_number": "1234567890",
    "target_account_holder": "Budi Santoso",
    "proof": "withdrawal-proofs/abc123.pdf",
    "status": "approved",
    "rejection_reason": null,
    "owner": {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "bank_name": "BCA",
      "bank_account_number": "1234567890",
      "bank_account_holder": "Budi Santoso",
      "balance": "2500000.00"
    },
    "created_at": "2026-09-11T14:00:00.000000Z",
    "updated_at": "2026-09-12T01:00:00.000000Z"
  }
}
```

**Error Responses:**

| Code | Message |
|------|---------|
| 404 | Permohonan pencairan dana tidak ditemukan. |
| 422 | Hanya permohonan dengan status pending yang dapat disetujui. / Bukti transfer wajib diunggah. / max 5MB. |

---

### 5.4 Reject Withdrawal

> **POST** `/admin/withdrawals/{id}/reject`
> 🔒 Requires admin token

**Request Body:**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `rejection_reason` | string | ✅ | min 10, max 1000 chars |

> **⚠️ Note:** Reject akan mengembalikan saldo ke pemilik kost secara otomatis (dalam database transaction + row lock).

**Example:**

```json
{
  "rejection_reason": "Bukti transfer tidak valid, mohon lampirkan bukti transfer dari mobile banking."
}
```

**Response 200:**

```json
{
  "success": true,
  "message": "Permohonan pencairan dana berhasil ditolak. Saldo telah dikembalikan ke pemilik.",
  "data": {
    "id": "660e8400-e29b-41d4-a716-446655440001",
    "owner_id": "550e8400-e29b-41d4-a716-446655440000",
    "amount": "2500000.00",
    "target_bank": "BCA",
    "target_account_number": "1234567890",
    "target_account_holder": "Budi Santoso",
    "proof": null,
    "status": "rejected",
    "rejection_reason": "Bukti transfer tidak valid, mohon lampirkan bukti transfer dari mobile banking.",
    "owner": {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "bank_name": "BCA",
      "bank_account_number": "1234567890",
      "bank_account_holder": "Budi Santoso",
      "balance": "5000000.00"
    },
    "created_at": "2026-09-11T14:00:00.000000Z",
    "updated_at": "2026-09-12T01:00:00.000000Z"
  }
}
```

**Error Responses:**

| Code | Message |
|------|---------|
| 404 | Permohonan pencairan dana tidak ditemukan. |
| 422 | Hanya permohonan dengan status pending yang dapat ditolak. / Alasan penolakan wajib diisi. / min 10 chars. |

---

## 6. Payment Monitoring

### 6.1 List Payments

> **GET** `/admin/payments`
> 🔒 Requires admin token

**Query Parameters:**

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `status` | string | ❌ | Filter: `pending`, `completed`, `failed`, `expired` |
| `order_id` | string | ❌ | Pencarian partial pada `order_id` |
| `per_page` | integer | ❌ | 1-100, default `15` |

**Example:** `GET /admin/payments?status=completed&order_id=ORD-2026&per_page=20`

**Response 200 (paginated):**

```json
{
  "success": true,
  "message": "Daftar transaksi pembayaran berhasil diambil.",
  "data": [
    {
      "id": "770e8400-e29b-41d4-a716-446655440002",
      "user_id": "550e8400-e29b-41d4-a716-446655440000",
      "contract_id": "880e8400-e29b-41d4-a716-446655440003",
      "order_id": "ORD-2026-001",
      "amount": "1500000.00",
      "status": "completed",
      "payment_type": "credit_card",
      "payment_date": "2026-09-11T10:30:00.000000Z",
      "user": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "Budi Santoso",
        "email": "budi@example.com"
      },
      "contract": {
        "id": "880e8400-e29b-41d4-a716-446655440003",
        "status": "active",
        "monthly_price": "1500000.00"
      },
      "created_at": "2026-09-11T10:30:00.000000Z",
      "updated_at": "2026-09-11T10:30:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 45
  }
}
```

---

### 6.2 Get Payment Detail

> **GET** `/admin/payments/{id}`
> 🔒 Requires admin token

**Response 200:** Sama dengan format item di [List Payments](#61-list-payments).

**Error 404:**

```json
{
  "success": false,
  "message": "Transaksi pembayaran tidak ditemukan."
}
```

---

## 7. Contract Audit

### 7.1 List Contracts

> **GET** `/admin/contracts`
> 🔒 Requires admin token

**Query Parameters:**

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `status` | string | ❌ | Filter: `pending_payment`, `active`, `in_renewal`, `completed`, `cancelled` |
| `per_page` | integer | ❌ | 1-100, default `15` |

**Example:** `GET /admin/contracts?status=active&per_page=10`

**Response 200 (paginated):**

```json
{
  "success": true,
  "message": "Daftar riwayat kontrak sewa berhasil diambil.",
  "data": [
    {
      "id": "880e8400-e29b-41d4-a716-446655440003",
      "owner_id": "550e8400-e29b-41d4-a716-446655440000",
      "user_id": "990e8400-e29b-41d4-a716-446655440004",
      "room_id": "aa0e8400-e29b-41d4-a716-446655440005",
      "start_date": "2026-09-01",
      "end_date": "2026-12-01",
      "monthly_price": "1500000.00",
      "deposit_amount": "3000000.00",
      "status": "active",
      "contract_type": "initial",
      "verification_contract": "completed",
      "rejection_feedback": null,
      "owner": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "Budi Santoso",
        "email": "budi@example.com"
      },
      "user": {
        "id": "990e8400-e29b-41d4-a716-446655440004",
        "name": "Andi Wijaya",
        "email": "andi@example.com"
      },
      "room": {
        "id": "aa0e8400-e29b-41d4-a716-446655440005",
        "room_number": "A-101",
        "room_size": "3x4",
        "price": "1500000.00",
        "status": "occupied",
        "kost": {
          "id": "bb0e8400-e29b-41d4-a716-446655440006",
          "name": "Kost Bahagia",
          "address": "Jl. Sudirman No. 10, Jakarta"
        }
      },
      "payments_count": 4,
      "created_at": "2026-08-28T09:00:00.000000Z",
      "updated_at": "2026-09-01T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 4,
    "per_page": 10,
    "total": 32
  }
}
```

---

### 7.2 Get Contract Detail

> **GET** `/admin/contracts/{id}`
> 🔒 Requires admin token

Response termasuk riwayat pembayaran terurut terbaru.

**Response 200:** Sama dengan format item di [List Contracts](#71-list-contracts).

**Error 404:**

```json
{
  "success": false,
  "message": "Kontrak sewa tidak ditemukan."
}
```

---

## 8. Error Handling

### HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 401 | Unauthenticated / token invalid |
| 403 | Forbidden (bukan admin atau akses ditolak) |
| 404 | Resource not found |
| 422 | Validation error |
| 500 | Server error |

### Validation Error Format

```json
{
  "success": false,
  "message": "Validasi gagal.",
  "errors": {
    "email": ["Email sudah digunakan."],
    "password": ["Password minimal 8 karakter."]
  }
}
```

### Authentication Error

```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

> Gunakan header `Authorization: Bearer <token>` pada semua request terproteksi.

---

## Appendix: Enum Values

### User Roles

| Value | Description |
|-------|-------------|
| `admin` | Super Admin |
| `owner` | Pemilik kost |
| `user` | Penyewa / tenant |

### KYC Status

| Value | Description |
|-------|-------------|
| `unverified` | Belum mengajukan KYC |
| `pending` | Menunggu verifikasi |
| `verified` | Telah disetujui |
| `rejected` | Ditolak |

### Payment Status

| Value | Description |
|-------|-------------|
| `pending` | Menunggu pembayaran |
| `completed` | Pembayaran berhasil |
| `failed` | Pembayaran gagal |
| `expired` | Pembayaran kedaluwarsa |

### Withdrawal Status

| Value | Description |
|-------|-------------|
| `pending` | Menunggu persetujuan admin |
| `approved` | Disetujui & sudah ditransfer |
| `rejected` | Ditolak (saldo dikembalikan) |

### Contract Status

| Value | Description |
|-------|-------------|
| `pending_payment` | Menunggu pembayaran pertama |
| `active` | Kontrak aktif |
| `in_renewal` | Proses perpanjangan |
| `completed` | Kontrak selesai |
| `cancelled` | Kontrak dibatalkan |

### Room Status

| Value | Description |
|-------|-------------|
| `available` | Kamar kosong |
| `occupied` | Kamar terisi |
| `maintenance` | Kamar dalam perawatan |

### Contract Type

| Value | Description |
|-------|-------------|
| `initial` | Kontrak awal |
| `renewal` | Perpanjangan kontrak |
