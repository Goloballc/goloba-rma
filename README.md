# GolobaRMA

Laravel/Bagisto package that extends `bagisto/bagisto-rma` for the **Goloba** marketplace (Colombia).  
Adds support for the **Right of Withdrawal** (*Derecho de Retracto*, Law 1480, Art. 47), a seller-admin
dispute mechanism, and transactional emails in Spanish.

> Spanish documentation: [README_ES.md](README_ES.md)

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | ^8.1 \| ^8.2 |
| Bagisto | ^2.x |
| `bagisto/bagisto-rma` | ^2.2 |
| `bagisto/marketplace` | compatible with Bagisto 2.x |

> `bagisto/bagisto-rma` and `bagisto/marketplace` must be installed and configured before installing this package.

---

## Installation

### 1. Copy the package

Place the `GolobaRMA` directory inside the project's packages folder:

```
packages/
└── Goloba/
    └── GolobaRMA/
```

### 2. Register in `composer.json`

Add the PSR-4 autoload entry to the **project's** `composer.json` (not the package's):

```json
"autoload": {
    "psr-4": {
        "Goloba\\GolobaRMA\\": "packages/Goloba/GolobaRMA/src"
    }
}
```

Then regenerate the autoloader:

```bash
composer dump-autoload
```

### 3. Register the Service Provider

Add the provider to `config/app.php` inside the `providers` array:

```php
Goloba\GolobaRMA\Providers\GolobaRMAServiceProvider::class,
```

> The provider is already declared in the `extra.laravel.providers` section of the package's `composer.json`, so it may be auto-discovered. In Bagisto it is recommended to register it explicitly to guarantee load order.

### 4. Run migrations

```bash
php artisan migrate
```

This creates or modifies the following tables:

| Migration | Change |
|---|---|
| `2025_01_12_000001` | Column `rma_type` on table `rma` |
| `2026_01_13_000002` | Column `is_seller` on `rma_messages` |
| `2026_03_25_000003` | Columns `retracto_expires_at`, `retracto_seal_intact` on `rma` |
| `2026_03_27_000004` | Table `colombian_holidays` |
| `2026_03_30_000005` | Tables `rma_disputes` and `rma_dispute_images` |
| `2026_04_14_000006` | Additional RMA statuses (`paid`, `replaced`) |

### 5. Seed Colombian public holidays

The working-day calculation for the Right of Withdrawal requires data in the `colombian_holidays` table.
Run the included seeder:

```bash
php artisan db:seed --class="Goloba\GolobaRMA\Database\Seeders\ColombianHolidaysSeeder"
```

The seeder loads holidays for **2026 and 2027**. For additional years, add records via the admin panel
(Settings → Public Holidays) or directly in the table:

```sql
INSERT INTO colombian_holidays (date, name, year) VALUES ('2028-01-01', 'Año Nuevo', 2028);
```

> If the table has no data for the current year when a retracto RMA is created, the system will throw a `\RuntimeException`.

### 6. Clear caches

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Configuration

### Environment variables

No additional environment variables are required. The package uses the existing Bagisto and Marketplace configuration.

If the project uses the Servientrega tracking microservice, make sure the following is defined in `.env`:

```
SERVIENTREGA_TRACKING_URL=https://sewebhook.goloba.com
```

### Retracto configuration file

The logic for categories that require a seal-intact checkbox is managed in:

```
src/Config/retracto.php
```

Category IDs configured as "conditioned" (require the seal checkbox): `4, 5, 6, 8, 10, 12, 14, 15`.

---

## What the Service Provider registers

`GolobaRMAServiceProvider` automatically performs the following on boot:

- Loads package migrations.
- Registers admin, seller and shop routes (shop routes are registered inside `app->booted()` to take priority over the vendor).
- Prepends the `rma` view namespace to override vendor views.
- Loads translations under the `goloba-rma` namespace.
- Merges the retracto config and admin/seller menu entries.
- Registers container bindings:
  - `RMAMessages` contract → `Goloba\GolobaRMA\Models\RMAMessages`
  - `RMAMessagesRepository` → `Goloba\GolobaRMA\Repositories\RMAMessagesRepository`
  - Shop `OrderRMADataGrid` → GolobaRMA extended version
- Registers `RetractoService` as a singleton.
- Event listener that automatically enables RMA on new products (`catalog.product.create.after`).

---

## Package structure

```
packages/Goloba/GolobaRMA/
├── composer.json
├── src/
│   ├── Config/
│   │   ├── admin-menu.php          # "Public Holidays" entry in admin menu
│   │   ├── retracto.php            # Conditioned category map
│   │   └── seller-menu.php         # RMA entry in seller menu
│   ├── Database/
│   │   ├── Migrations/             # 6 migrations (see installation section)
│   │   └── Seeders/
│   │       └── ColombianHolidaysSeeder.php
│   ├── DataGrids/
│   │   ├── Shop/OrderRMADataGrid.php       # Shows completed orders only
│   │   └── Seller/SellerRmaDataGrid.php    # Filtered by seller
│   ├── Http/Controllers/
│   │   ├── Admin/
│   │   │   ├── RmaController.php           # Override — seller notifications
│   │   │   ├── DisputeController.php       # Dispute resolution
│   │   │   └── ColombianHolidaysController.php
│   │   ├── Seller/
│   │   │   └── RMAController.php           # Seller panel + submitDispute()
│   │   └── Shop/
│   │       └── GolobaCustomerController.php # Shop customer controller override
│   ├── Mail/                        # Transactional Mailables (5 classes + helper)
│   ├── Models/
│   │   ├── RMA.php
│   │   ├── RMAMessages.php
│   │   ├── RmaDispute.php
│   │   └── RmaDisputeImage.php
│   ├── Providers/
│   │   ├── GolobaRMAServiceProvider.php
│   │   └── EventServiceProvider.php
│   ├── Repositories/
│   │   └── RMAMessagesRepository.php
│   ├── Resources/
│   │   ├── lang/es/app.php          # Translations under 'goloba-rma' namespace
│   │   └── views/
│   │       ├── admin/               # Admin views (holidays, disputes)
│   │       ├── emails/              # Email templates
│   │       ├── seller/rma/          # Seller panel views
│   │       └── shop/customer/rma/   # Overridden shop views
│   ├── Routes/
│   │   ├── admin-routes.php
│   │   ├── seller-routes.php
│   │   └── shop-routes.php
│   └── Services/
│       └── RetractoService.php      # Business-day calculation (Law 1480)
```

---

## Registered routes

### Admin
| Method | URI | Description |
|---|---|---|
| GET/POST | `/admin/rma/...` | Vendor route overrides |
| GET/POST | `/admin/rma/disputes/...` | Dispute management panel |
| GET/POST | `/admin/rma/festivos/...` | Colombian public holidays management |

### Seller
| Method | URI | Name |
|---|---|---|
| GET | `/vendedor/cuenta/rma` | `goloba.seller.rma.index` |
| GET | `/vendedor/cuenta/rma/view/{id}` | `goloba.seller.rma.view` |
| POST | `/vendedor/cuenta/rma/change-status` | `goloba.seller.rma.change_status` |
| POST | `/vendedor/cuenta/rma/submit-dispute` | `goloba.seller.rma.submit_dispute` |
| GET | `/vendedor/cuenta/rma/get-messages` | `goloba.seller.rma.get_messages` |
| POST | `/vendedor/cuenta/rma/send-message` | `goloba.seller.rma.send_message` |

### Shop (customer)
| Method | URI | Description |
|---|---|---|
| GET | `/customer/account/rma/check-retracto` | Checks retracto eligibility for an order |

---

## Transactional emails

The package sends emails in Spanish via `Mail::queue()`. Available Mailables:

| Class | Trigger | Recipients |
|---|---|---|
| `NewRequestCustomer` | New RMA request | Customer |
| `NewRequestSeller` | New RMA request | Seller |
| `StatusUpdate` | Status change | Customer or Seller |
| `DisputeCreatedAdmin` | Dispute opened | Admin |
| `DisputeResolved` | Dispute resolved | Seller + Customer |

> The vendor's original email (`CustomerRmaCreationEmail`, in English) is automatically suppressed to avoid duplicates.

---

## Useful commands

```bash
# Clear all caches
php artisan config:clear && php artisan route:clear && php artisan view:clear

# List package routes
php artisan route:list | findstr rma

# Check migration status
php artisan migrate:status

# Re-run the holidays seeder
php artisan db:seed --class="Goloba\GolobaRMA\Database\Seeders\ColombianHolidaysSeeder"
```

---

## Important notes

- **Tailwind:** Views use solid color variants only (e.g. `bg-blue-500`, not `bg-blue-100`). The Bagisto/Marketplace theme does not compile CSS from external packages.
- **Blade/Vue conflict:** Never place `@if/@endif` directives or `{{ }}` mustaches inside `<script type="text/x-template">` blocks. Use `v-if`/`v-else` for conditionals inside Vue templates.
- **Admin modals:** Use `<teleport to="body">` with `z-[9999]` to escape the sidebar stacking context.
- **2028 holidays:** Pending — add via seeder or through the admin panel.
