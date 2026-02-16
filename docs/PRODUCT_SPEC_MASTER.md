# CYN Tourism Management System — Complete Product Specification
# Master Development Prompt — All Phases

*This file is the single master specification for the product. See PROJECT_REQUIREMENTS_EDIT.md for recent implementation notes.*

---

## PROJECT CONTEXT

**Current Tech Stack:** PHP 8.x, MySQL/MariaDB, PDO, Dompdf, jQuery, Tailwind CSS  
**Architecture:** MVC — Controllers (`src/Controllers/`), Models (`src/Models/`), Views (`views/`)  
**Database:** `seed.sql` — 15+ tables  
**Target Market:** Tourism & DMC companies in Turkey, Middle East, North Africa  
**Target Price After Completion:** $30,000–$50,000 license or $1,000–$2,000/month SaaS  

---

## PHASE 0 — CORE PRICING & VOUCHER ENHANCEMENTS

### 0.1 Per-Person Tour Pricing
- Tour price = unit for **1 adult**; auto-calc: `total = (adults × adult_price) + (children × child_price) + (infants × infant_price)`
- DB: `price_per_adult`, `price_per_child`, `price_per_infant` (plus services table)
- Forms + JS auto-calc; voucher_show + voucher_pdf breakdown

### 0.2 Hotel Adult/Child + Room Capacity
- Room capacity from admin/XLSX; validate `(adults + children) ≤ capacity`
- Occupancy tier pricing + children × price_child × nights
- DB: hotels child_age_min/max, infant_age_max; hotel_rooms max_adults, max_children

### 0.3 Price-Free Vouchers + Linked Services
- Vouchers show **NO prices**
- **Link existing** tours/transfers (no new forms) via `voucher_services` table
- Searchable dropdown → "Guest Program" on PDF; sync to hotel calendar
- AJAX: `GET /api/search-services?q=...`

---

## PHASE 1 — SECURITY, UI & FOUNDATION
- Security: PDO only, bcrypt, rate limit login, CSRF, CSP headers, session fixation, file upload sanitization
- UI: Professional login, landing, dashboard with KPIs/charts, design system, dark mode, toasts
- Arabic + RTL (`ar.php`, `rtl.css`, PDF Arabic fonts)
- Roles & permissions: `roles`, `permissions`, `role_permissions`; field-level e.g. `invoices.view_prices`

---

## PHASE 2 — CORE TOURISM OPERATIONS
- Seasonal pricing: `pricing_seasons`, `pricing_rates`; blackout, early bird, last minute
- Allotments: `allotments` table; track used/available; auto-release
- Markup/commission: cost_price, markup_percent, selling_price, commission on hotel_vouchers/tours
- Rooming list: `rooming_list` table; Excel import/export

---

## PHASE 3 — FINANCIAL & QUOTATION
- Quotations: `quotations`, `quotation_items`; day-by-day builder; PDF; convert to bookings
- Group files: `group_files`, `group_file_items`; timeline; one-click vouchers
- Statement of account; Credit notes; Tax system (`tax_rates`); Profitability reports

---

## PHASE 4 — PARTNER & CLIENT EXPERIENCE
- Partner portal: online booking, availability search, group dossier, statement, quotation accept/reject
- Client portal: unique URL per booking, itinerary (no prices), mobile, PDF
- WhatsApp; Reviews table + post-trip review link

---

## PHASE 5 — INTEGRATIONS & MOBILE
- Payment (Stripe/PayTabs); ICS export; Maps; Driver/Guide PWA; REST API + Swagger

---

## PHASE 6 — ENTERPRISE & SAAS
- Multi-tenancy (`company_id`); Plugin/module system; Webhooks; PDF template engine; Notification rules; Onboarding; Health check; Version

---

## PHASE 7 — UX EXCELLENCE
- Global search (Ctrl+K); Favorites; Drag-drop calendar; Daily manifest; Internal notes; Reports suite; Dashboard widgets; Smart alerts

---

## PHASE 8 — LEGAL & COMPLIANCE
- Privacy/Terms; Cancellation policies; Audit trail UI; Automated backups; Support tickets

---

## IMPLEMENTATION ROADMAP (from spec)

| Phase | Duration | Price After |
|-------|----------|-------------|
| 0 | 2-3 weeks | Foundation |
| 1 | 3-4 weeks | Demoable |
| 2 | 4-5 weeks | $15K-25K |
| 3 | 4-5 weeks | $25K-35K |
| 4 | 3-4 weeks | $35K-45K |
| 5 | 3-4 weeks | $40K-50K |
| 6 | 4-5 weeks | SaaS $1-2K/mo |
| 7 | 3-4 weeks | Premium |
| 8 | 2-3 weeks | Enterprise |

**Total: 6-9 months**
