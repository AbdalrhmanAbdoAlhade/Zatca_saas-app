<div align="center">

# ⚡ ZATCA SaaS API

**نظام فوترة إلكترونية متكامل متوافق مع هيئة الزكاة والضريبة والجمارك السعودية (ZATCA)**

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)](https://php.net)[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)[![ZATCA](https://img.shields.io/badge/ZATCA-Phase%202%20Ready-00A86B?logo=gov.uk&logoColor=white)](https://zatca.gov.sa)[![License](https://img.shields.io/badge/License-MIT-3DA639?logo=open-source-initiative&logoColor=white)](LICENSE)[![Maintenance](https://img.shields.io/badge/Maintenance-Active-2EA44F?logo=clockify&logoColor=white)](https://github.com/AbdalrhmanAbdoAlhade/Zatca_saas-app)

**SaaS متعدد المستأجرين | REST API كامل | أتمتة فواتير ZATCA**

[المميزات](#-%D8%A7%D9%84%D9%85%D9%85%D9%8A%D8%B2%D8%A7%D8%AA) • [التثبيت](#-%D8%A7%D9%84%D8%AA%D8%AB%D8%A8%D9%8A%D8%AA) • [الاستخدام](#-%D8%A7%D9%84%D8%A7%D8%B3%D8%AA%D8%AE%D8%AF%D8%A7%D9%85) • [API Reference](#-api-reference) • [ZATCA Onboarding](#-zatca-onboarding) • [الدعم](#-%D8%A7%D9%84%D8%AF%D8%B9%D9%85)

</div>

---

## 🎯 نظرة عامة

**ZATCA SaaS API** هو حل برمجي متكامل يُمكّن الشركات والمطورين من الامتثال الكامل لمتطلبات **الفوترة الإلكترونية Phase 2** الخاصة بهيئة الزكاة والضريبة والجمارك السعودية (ZATCA). يوفر النظام واجهة برمجة تطبيقات (REST API) سهلة الاستخدام لتوليد الفواتير الإلكترونية، التوقيع الرقمي، توليد QR Code، والربط المباشر مع بوابات ZATCA.

> 🏗️ **مشروع مفتوح المصدر** | 🔄 **Phase 1 & 2** | 🚀 **جاهز للإنتاج** | 🏢 **SaaS متعدد المستأجرين**

---

## ✨ المميزات

| الميزة | الوصف |
| --- | --- |
| 🔐 **توليد CSR** | توليد طلبات الشهادات (CSR) وإدارة المفاتيح الخاصة |
| 📝 **توقيع الفواتير** | توقيع رقمي (XAdES) للفواتير باستخدام شهادات ZATCA |
| 📱 **QR Code** | توليد رموز QR متوافقة مع معيار ZATCA (TLV) |
| 🌐 **RESTful API** | واجهة برمجة تطبيقات RESTful كاملة مع Postman Collection |
| 🏢 **SaaS متعدد المستأجرين** | دعم متعدد الشركات (Multi-tenant) مع عزل كامل للبيانات |
| 📊 **أنواع الفواتير** | فواتير مبسطة، فواتير قياسية، إشعارات مدينة/دائنة، فواتير مشتريات |
| 🔄 **Clearance & Reporting** | إرسال الفواتير للتسوية والإبلاغ عبر ZATCA APIs |
| 🛡️ **أمان عالي** | تشفير البيانات، إدارة الجلسات، وإدارة آمنة للشهادات |
| 📦 **Docker Ready** | جاهز للتشغيل عبر Docker مع Docker Compose |
| 🌍 **دعم اللغتين** | دعم كامل للعربية والإنجليزية |
| 📋 **إدارة كاملة** | عملاء، موردين، منتجات، مستخدمين، مدفوعات، تقارير |
| 📈 **تقارير مالية** | تقارير يومية، شهرية، ربع سنوية، سنوية |

---

## 🏗️ بنية النظام

```
┌─────────────────────────────────────────────────────────────┐
│                      ZATCA SaaS API                          │
├─────────────────────────────────────────────────────────────┤
│  🔐 Auth        │  🏢 Company    │  👥 Users     │  🧾 Invoices │
│  • Register     │  • Profile     │  • CRUD       │  • Create    │
│  • Login        │  • Settings    │  • Roles      │  • Generate  │
│  • Sessions     │  • ZATCA Conf  │  • Permissions│  • Sign XML  │
│  • Logout       │                │               │  • Submit    │
├─────────────────────────────────────────────────────────────┤
│  👤 Customers   │  📦 Products   │  🏭 Suppliers │  💳 Payments │
│  • CRUD         │  • CRUD        │  • CRUD       │  • Record    │
│  • VAT Number   │  • Stock       │  • VAT Number │  • History   │
│  • Address      │  • Tax Config  │  • Address    │              │
├─────────────────────────────────────────────────────────────┤
│  📊 Reports     │  🏛️ ZATCA      │  📋 Activity  │  💰 Subs     │
│  • Daily        │  • Onboarding  │  • Audit Log  │  • Plans     │
│  • Monthly      │  • Compliance  │  • Tracking   │  • Payments  │
│  • Quarterly    │  • Production  │               │  • Limits    │
│  • Yearly       │  • Submit      │               │              │
└─────────────────────────────────────────────────────────────┘
```

---

## 🛠️ المتطلبات

- **PHP** >= 8.0

- **Composer** - لإدارة الحزم

- **Laravel** >= 10.x

- **MySQL** >= 8.0 أو **PostgreSQL** >= 13

- **Redis** - للـ Queues والـ Caching (اختياري)

- **OpenSSL** - لتوليد المفاتيح والتوقيع

- **ext-mbstring** - لدعم اللغة العربية

- **ext-dom** - لمعالجة XML

---

## 📦 التثبيت

### 1️⃣ استنساخ المشروع

```bash
git clone https://github.com/AbdalrhmanAbdoAlhade/Zatca_saas-app.git
cd Zatca_saas-app
```

### 2️⃣ تثبيت الحزم

```bash
composer install
npm install && npm run build
```

### 3️⃣ إعداد البيئة

```bash
cp .env.example .env
php artisan key:generate
```

### 4️⃣ تكوين قاعدة البيانات

```bash
# عدل ملف .env ببيانات قاعدة البيانات
php artisan migrate --seed
```

### 5️⃣ إعداد Laravel Queues (للـ Async Jobs )

```bash
# في .env
QUEUE_CONNECTION=database

# تشغيل الـ Worker
php artisan queue:work
```

### 6️⃣ تشغيل التطبيق

```bash
php artisan serve
```

📡 **الـ API متاح على:** `http://localhost:8000/api/v1/`

---

## 🐳 Docker (الطريقة السريعة )

```bash
# بناء وتشغيل الحاويات
docker-compose up -d --build

# تشغيل الميجريشنز
docker-compose exec app php artisan migrate --seed

# تشغيل الـ Queue Worker
docker-compose exec app php artisan queue:work
```

---

## 🚀 الاستخدام السريع

### 1️⃣ تسجيل شركة جديدة

```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -H "Accept-Language: ar" \
  -d '{
    "company": {
      "trade_name_ar": "شركة التقنية المتقدمة",
      "trade_name_en": "Advanced Tech Co.",
      "vat_number": "300000000000003",
      "commercial_registration_number": "1234567890",
      "country": "SA",
      "city": "Riyadh"
    },
    "owner": {
      "name": "المالك التجريبي",
      "email": "owner@example.com",
      "phone": "0500000000",
      "password": "password123",
      "password_confirmation": "password123"
    },
    "device_name": "API Client"
  }'
```

**الرد:**

```json
{
  "status": "success",
  "message": "تم إنشاء الشركة بنجاح",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": { ... },
    "company": { ... }
  }
}
```

### 2️⃣ تسجيل الدخول

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "owner@example.com",
    "password": "password123",
    "device_name": "Postman"
  }'
```

### 3️⃣ إنشاء فاتورة

```bash
curl -X POST http://localhost:8000/api/v1/invoices \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept-Language: ar" \
  -d '{
    "invoice_type": "tax_invoice",
    "customer_id": 1,
    "issue_date": "2026-07-14",
    "due_date": "2026-08-14",
    "currency": "SAR",
    "notes": "فاتورة تجريبية",
    "items": [
      {
        "product_id": 1,
        "name_ar": "منتج تجريبي",
        "quantity": 2,
        "unit_price": 100,
        "tax_percentage": 15
      }
    ]
  }'
```

### 4️⃣ معالجة الفاتورة كاملة (Generate → Sign → Submit )

```bash
curl -X POST http://localhost:8000/api/v1/invoices/1/process \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept-Language: ar"
```

---

## 📡 API Reference

### 🔐 المصادقة (Auth )

| الطريقة | النقطة | الوصف |
| --- | --- | --- |
| `POST` | `/api/v1/register` | تسجيل شركة جديدة + مالك |
| `POST` | `/api/v1/login` | تسجيل الدخول |
| `GET` | `/api/v1/me` | بيانات المستخدم الحالي |
| `POST` | `/api/v1/logout` | تسجيل الخروج (الجهاز الحالي) |
| `POST` | `/api/v1/logout-all` | تسجيل الخروج من كل الأجهزة |
| `GET` | `/api/v1/sessions` | قائمة الجلسات |
| `DELETE` | `/api/v1/sessions/{id}` | إلغاء جلسة معينة |

### 🏢 الشركة (Company)

| الطريقة | النقطة | الوصف |
| --- | --- | --- |
| `GET` | `/api/v1/company` | بيانات الشركة |
| `PUT` | `/api/v1/company` | تحديث بيانات الشركة |
| `GET` | `/api/v1/company/settings` | إعدادات الشركة |
| `PUT` | `/api/v1/company/settings` | تحديث الإعدادات |

### 👥 المستخدمين (Users)

| الطريقة | النقطة | الوصف |
| --- | --- | --- |
| `GET` | `/api/v1/users` | قائمة المستخدمين |
| `POST` | `/api/v1/users` | إضافة مستخدم جديد |
| `GET` | `/api/v1/users/{id}` | بيانات مستخدم |
| `PUT` | `/api/v1/users/{id}` | تحديث مستخدم |
| `DELETE` | `/api/v1/users/{id}` | حذف مستخدم |

> **الأدوار:** `company-owner` | `sales` | `accountant` | `viewer`

### 🧾 الفواتير (Invoices) — القلب النابض للنظام

| الطريقة | النقطة | الوصف | الحالة |
| --- | --- | --- | --- |
| `GET` | `/api/v1/invoices` | قائمة الفواتير | — |
| `POST` | `/api/v1/invoices` | إنشاء فاتورة جديدة | `draft` |
| `GET` | `/api/v1/invoices/{id}` | بيانات فاتورة | — |
| `PUT` | `/api/v1/invoices/{id}` | تحديث فاتورة (draft فقط) | `draft` |
| `DELETE` | `/api/v1/invoices/{id}` | حذف فاتورة (draft فقط) | `draft` |
| `POST` | `/api/v1/invoices/{id}/generate-xml` | توليد XML (UBL 2.1) | `xml_generated` |
| `POST` | `/api/v1/invoices/{id}/sign-xml` | توقيع XML (XAdES) | `signed` |
| `POST` | `/api/v1/invoices/{id}/submit-to-zatca` | إرسال لـ ZATCA | `submitted` |
| `POST` | `/api/v1/invoices/{id}/process` | **العملية كاملة** | `submitted` |

> **أنواع الفواتير:** `tax_invoice` | `simplified_tax_invoice` | `credit_note` | `debit_note` | `purchase_invoice` | `expense_invoice`**حالات الفاتورة:** `draft` → `xml_generated` → `signed` → `submitted` → `cancelled`

### 👤 العملاء (Customers)

| الطريقة | النقطة | الوصف |
| --- | --- | --- |
| `GET` | `/api/v1/customers` | قائمة العملاء |
| `POST` | `/api/v1/customers` | إضافة عميل |
| `GET` | `/api/v1/customers/{id}` | بيانات عميل |
| `PUT` | `/api/v1/customers/{id}` | تحديث عميل |
| `DELETE` | `/api/v1/customers/{id}` | حذف عميل |

### 📦 المنتجات (Products)

| الطريقة | النقطة | الوصف |
| --- | --- | --- |
| `GET` | `/api/v1/products` | قائمة المنتجات |
| `POST` | `/api/v1/products` | إضافة منتج |
| `GET` | `/api/v1/products/{id}` | بيانات منتج |
| `PUT` | `/api/v1/products/{id}` | تحديث منتج |
| `DELETE` | `/api/v1/products/{id}` | حذف منتج |

### 🏭 الموردين (Suppliers)

| الطريقة | النقطة | الوصف |
| --- | --- | --- |
| `GET` | `/api/v1/suppliers` | قائمة الموردين |
| `POST` | `/api/v1/suppliers` | إضافة مورد |
| `GET` | `/api/v1/suppliers/{id}` | بيانات مورد |
| `PUT` | `/api/v1/suppliers/{id}` | تحديث مورد |
| `DELETE` | `/api/v1/suppliers/{id}` | حذف مورد |

### 💳 المدفوعات (Payments)

| الطريقة | النقطة | الوصف |
| --- | --- | --- |
| `GET` | `/api/v1/payments` | قائمة المدفوعات |
| `POST` | `/api/v1/payments` | إضافة دفعة |
| `GET` | `/api/v1/payments/{id}` | بيانات دفعة |

### 📊 التقارير (Reports)

| الطريقة | النقطة | الوصف |
| --- | --- | --- |
| `GET` | `/api/v1/reports` | قائمة التقارير |
| `POST` | `/api/v1/reports` | طلب تقرير جديد |
| `GET` | `/api/v1/reports/{id}` | بيانات تقرير |
| `DELETE` | `/api/v1/reports/{id}` | حذف تقرير |

> **أنواع التقارير:** `daily` | `monthly` | `quarterly` | `yearly` | `custom`

### 🏛️ ZATCA Onboarding

| الطريقة | النقطة | الوصف | المرحلة |
| --- | --- | --- | --- |
| `GET` | `/api/v1/zatca` | إعدادات ZATCA | — |
| `POST` | `/api/v1/zatca/compliance-csid` | توليد Compliance CSID | 1 |
| `POST` | `/api/v1/zatca/compliance-check` | فحص Compliance | 2 |
| `POST` | `/api/v1/zatca/production-csid` | طلب Production CSID | 3 |
| `POST` | `/api/v1/zatca/activate-production` | تفعيل Production | 4 |

### 📋 سجل الأنشطة (Activity Logs)

| الطريقة | النقطة | الوصف |
| --- | --- | --- |
| `GET` | `/api/v1/activity-logs` | قائمة الأنشطة |
| `GET` | `/api/v1/activity-logs/{id}` | بيانات نشاط |

### 🏥 Health Check

| الطريقة | النقطة | الوصف |
| --- | --- | --- |
| `GET` | `/api/health` | فحص صحة النظام (Public) |

---

## 🔐 آلية المصادقة

- كل الـ endpoints (ما عدا التسجيل والدخول وـ Health Check) بتحتاج `Authorization: Bearer {token}`

- التوكن بيتعمل وقت تسجيل الدخول أو تسجيل شركة جديدة

- التوكن بيتسجل مع بيانات الجهاز (IP, User-Agent, Device Name)

- ممكن تسجيل الخروج من جهاز واحد أو كل الأجهزة

```bash
# مثال على استخدام التوكن
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Accept-Language: ar" \
     http://localhost:8000/api/v1/me
```

---

## 🏢 Multi-Tenancy (متعدد المستأجرين )

- كل مستخدم تابع لشركة واحدة

- المستخدم ميقدرش يشوف/يعدل بيانات شركة تانية

- الـ `company_id` بيتحط تلقائي من التوكن

- عزل كامل للبيانات على مستوى قاعدة البيانات والـ Queries

---

## 🏛️ ZATCA Onboarding Guide

### المرحلة 1: توليد Compliance CSID

```bash
curl -X POST http://localhost:8000/api/v1/zatca/compliance-csid \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"otp": "123456"}'
```

> الـ OTP بيجي من بوابة ZATCA. لازم تكون مسجل في بوابة ZATCA.

### المرحلة 2: فحص Compliance

```bash
curl -X POST http://localhost:8000/api/v1/zatca/compliance-check \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "invoice_base64": "PD94bWwgdmVyc2lvbj0iMS4wIj8+",
    "invoice_hash": "abc123...",
    "uuid": "550e8400-e29b-41d4-a716-446655440000"
  }'
```

### المرحلة 3: طلب Production CSID

```bash
curl -X POST http://localhost:8000/api/v1/zatca/production-csid \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### المرحلة 4: تفعيل Production

```bash
curl -X POST http://localhost:8000/api/v1/zatca/activate-production \
  -H "Authorization: Bearer YOUR_TOKEN"
```

> ⚠️ **تحذير:** بعد تفعيل Production، كل الفواتير هتتبعت لـ ZATCA فعلياً. مينفعش ترجع لـ Sandbox.

---

## 🧾 سير عمل الفاتورة (Invoice Lifecycle )

```
┌─────────┐    ┌─────────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│  Create │───▶│ Generate XML│───▶│ Sign XML │───▶│  Submit  │───▶│  Done!   │
│  Draft  │    │  (UBL 2.1)  │    │ (XAdES)  │    │  to ZATCA│    │          │
└─────────┘    └─────────────┘    └──────────┘    └──────────┘    └──────────┘
     │               │                  │                │
     ▼               ▼                  ▼                ▼
  editable      xml_generated      signed          submitted
  deletable     (locked)           (locked)        (locked)
```

> ⚠️ **ملاحظات مهمة:**- الفاتورة لازم تعدي المراحل بالترتيب- مينفعش تعدل/تحذف فاتورة بعد ما تتبعت لـ ZATCA- الـ ICV (Invoice Counter Value) بيزيد تلقائي لكل شركة- الـ Invoice Hash بيرتبط بالفاتورة السابقة (سلسلة متصلة)- الـ Subscription Limit بيمنعك لو وصلت للحد الأقصى

---

## 🧪 الاختبار

```bash
# تشغيل الاختبارات
php artisan test

# اختبار تكامل ZATCA
php artisan test --filter=ZATCAIntegrationTest

# اختبار وحدة معينة
php artisan test --filter=InvoiceTest
```

---

## 📁 بنية المشروع

```
Zatca_saas-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # API Controllers
│   │   ├── Middleware/            # Auth, Tenant, Subscription
│   │   └── Requests/            # Form Requests (Validation)
│   ├── Models/                   # Eloquent Models
│   ├── Services/
│   │   ├── ZATCA/               # خدمات ZATCA الرئيسية
│   │   │   ├── CSRGenerator.php
│   │   │   ├── InvoiceSigner.php
│   │   │   ├── XMLGenerator.php
│   │   │   ├── Certificate.php
│   │   │   └── ZATCAClient.php
│   │   ├── Tenant/              # إدارة المستأجرين
│   │   └── Invoice/             # معالجة الفواتير
│   ├── Jobs/                     # Async Jobs (Generate, Sign, Submit)
│   └── Providers/
├── config/
│   └── zatca.php                # إعدادات ZATCA
├── database/
│   ├── migrations/              # Migration files
│   └── seeders/                 # Seeders
├── routes/
│   └── api.php                  # API Routes
├── storage/
│   └── certs/                   # الشهادات والمفاتيح
├── tests/
│   ├── Feature/                 # Feature Tests
│   └── Unit/                    # Unit Tests
├── docker/
│   ├── Dockerfile
│   └── docker-compose.yml
├── postman/
│   └── ZATCA_SaaS_API_Postman_Collection.json
└── README.md
```

---

## 🚀 استخدام Postman Collection

لتسهيل اختبار وتطوير الـ API، تم توفير Postman Collection شاملة تحتوي على جميع نقاط النهاية الرئيسية.

### 📥 استيراد المجموعة

1. قم بتحميل ملف `Zatca_saas-app_Postman_Collection.json` من مجلد `postman/` في هذا المستودع.

1. افتح تطبيق Postman.

1. اضغط على زر **Import** في الزاوية العلوية اليسرى.

1. اختر الملف الذي قمت بتحميله.

### ⚙️ إعداد المتغيرات

تحتوي المجموعة على متغيرات بيئة (Environment Variables) لتبسيط الاستخدام:

- `baseUrl`: رابط الـ API الأساسي (مثال: `http://localhost:8000/` ).

- `token`: يتم تحديثه تلقائياً بعد تسجيل الدخول بنجاح.

- `user_id`, `company_id`, `customer_id`, `supplier_id`, `product_id`, `invoice_id`: يتم تحديثها تلقائياً بعد إنشاء الموارد ذات الصلة.

- `otp`, `invoice_base64`, `invoice_hash`, `uuid`: تستخدم لعمليات تكامل ZATCA.

**خطوات البدء السريع:**

1. بعد استيراد المجموعة، قم بإنشاء بيئة جديدة في Postman أو استخدم البيئة الافتراضية.

1. قم بتعيين قيمة `baseUrl` لتشير إلى عنوان الـ API الخاص بك (مثال: `http://localhost:8000` ).

1. ابدأ بطلب **Register Company** لإنشاء شركة ومستخدم مالك.

1. ثم استخدم طلب **Login** لتسجيل الدخول والحصول على `token`، والذي سيتم حفظه تلقائياً في متغير البيئة `token`.

1. يمكنك الآن استخدام الـ `token` في جميع الطلبات الأخرى التي تتطلب مصادقة.

### 📂 هيكل المجموعة

تم تنظيم الطلبات في مجلدات منطقية:

- **Authentication:** تسجيل، دخول، خروج، إدارة الجلسات.

- **Company Management:** تحديث بيانات الشركة وإعداداتها.

- **User Management:** إضافة، تحديث، حذف المستخدمين (يتطلب صلاحيات المالك).

- **Customer Management:** إدارة بيانات العملاء.

- **Supplier Management:** إدارة بيانات الموردين.

- **Product Management:** إدارة بيانات المنتجات.

- **Invoice Management:** إنشاء، عرض، تحديث، حذف الفواتير، بالإضافة إلى عمليات توليد XML، التوقيع، والإرسال إلى ZATCA.

- **ZATCA Integration:** عمليات الربط مع هيئة الزكاة والضريبة والجمارك (ZATCA Onboarding).

- **Admin Routes:** مسارات خاصة بمسؤول النظام (Super Admin) لإدارة الشركات والخطط والمستخدمين.

**ملاحظة:** بعض الطلبات تتطلب تحديث متغيرات مثل `customer_id` أو `invoice_id` يدوياً بعد إنشاء المورد لأول مرة، أو سيتم تحديثها تلقائياً إذا كانت تحتوي على `test` script في Postman. تأكد من مراجعة الـ `response` لكل طلب للحصول على الـ IDs اللازمة.

---

## 🌐 بيئات ZATCA

| البيئة | URL | الاستخدام |
| --- | --- | --- |
| **Sandbox** | `https://gw-fatoora.zatca.gov.sa/e-invoicing/sandbox` | التطوير والاختبار |
| **Simulation** | `https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation` | المحاكاة قبل الإنتاج |
| **Production** | `https://gw-fatoora.zatca.gov.sa/e-invoicing/core` | الإنتاج الفعلي |

---

## 🛡️ الأمان

- **تشفير الشهادات:** المفاتيح الخاصة مخزنة بشكل مشفر

- **عزل البيانات:** Multi-tenancy مع عزل كامل

- **إدارة الجلسات:** تتبع كل جهاز مسجل دخول

- **صلاحيات مرنة:** RBAC (Role-Based Access Control )

- **Rate Limiting:** حماية من الـ DDoS

- **Input Validation:** Validation شاملة على كل الـ endpoints

---

## 🤝 المساهمة

نرحب بالمساهمات! إذا كنت ترغب في المساهمة:

1. 🍴 Fork المشروع

1. 🌿 أنشئ فرعًا جديدًا (`git checkout -b feature/AmazingFeature`)

1. 💾 Commit التغييرات (`git commit -m 'Add some AmazingFeature'`)

1. 📤 Push للفرع (`git push origin feature/AmazingFeature`)

1. 🔃 افتح Pull Request

---

## 📄 الترخيص

هذا المشروع مرخص بموجب [MIT License](LICENSE).

---

## 👨‍💻 المطور

<div align="center">

**عبدالرحمن عبدالهادي**

[![GitHub](https://img.shields.io/badge/GitHub-AbdalrhmanAbdoAlhade-181717?style=for-the-badge&logo=github)](https://github.com/AbdalrhmanAbdoAlhade)

</div>

---

## 📞 الدعم والتواصل

- 🐛 **Issues:** [GitHub Issues](https://github.com/AbdalrhmanAbdoAlhade/Zatca_saas-app/issues)

- 💬 **Discussions:** [GitHub Discussions](https://github.com/AbdalrhmanAbdoAlhade/Zatca_saas-app/discussions)

---

<div align="center">

⭐ **لا تنسَ دعم المشروع بـ Star!** ⭐

**صنع ب❤️ في المملكة العربية السعودية**

</div>
