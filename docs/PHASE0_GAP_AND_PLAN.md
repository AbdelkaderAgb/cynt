# Phase 0 — Gap Analysis & Implementation Plan

This document compares the **Product Spec Master** Phase 0 requirements with the **current implementation** and lists concrete next steps.

---

## 0.1 Per-Person Tour Pricing

| Spec requirement | Current state | Gap |
|------------------|---------------|-----|
| Unit price for **1 adult** | ✅ `price_per_person` = adult per 1 pax | — |
| `total = (adults×adult) + (children×child) + (infants×infant)` | ✅ Adult + child; **infant price not stored** | Add `price_per_infant`, use in calc |
| DB: `price_per_adult`, `price_per_child`, `price_per_infant` | We use `price_per_person` + `price_child`; no infant | Optional: add `price_per_infant` column |
| Services table: `price_adult`, `price_child`, `price_infant` | `services` has single `price` only | Add columns + ServiceController support |
| voucher_show: price breakdown | Not implemented | Add breakdown section |
| voucher_pdf: price breakdown | PDF has no prices (per “vouchers don’t include prices”) | Spec says “Render price breakdown in PDF” — **conflict with “price-free vouchers”**; treat as internal only or omit on guest-facing PDF |
| Frontend auto-calc bound to all 6 inputs | ✅ Alpine bound for adult/child; infant not in calc | Add infant field + bind `price_per_infant` |

**Conclusion:** Add `price_per_infant` to tours (and optionally rename to price_per_adult for clarity). Add infant to form and server-side total calculation. Add services table columns + support in ServiceController. Add price breakdown on voucher_show (and optionally internal PDF only).

---

## 0.2 Hotel Adult/Child + Room Capacity

| Spec requirement | Current state | Gap |
|------------------|---------------|-----|
| Room capacity in admin + XLSX | ✅ Capacity in form and XLSX column 7 | — |
| Validate `(adults + children) ≤ room capacity` | Not enforced on create/update | Add validation in HotelController + portal |
| Occupancy tier (single/double/triple/quad) + children×price_child×nights | Logic described in spec; not fully wired | Ensure voucher/booking form uses tier + child calc |
| DB: hotels `child_age_min`, `child_age_max`, `infant_age_max` | Not present | Add columns + optional UI |
| DB: hotel_rooms `max_adults`, `max_children` | Not present; we have single `capacity` | Spec wants both; can add or derive from capacity |
| XLSX: max_adults / max_children columns | Only capacity in XLSX | Add columns to import if we add columns |

**Conclusion:** Enforce capacity validation on hotel voucher and portal booking. Optionally add hotel age limits and room max_adults/max_children; then XLSX and forms to match.

---

## 0.3 Price-Free Vouchers + Linked Services (Guest Program)

| Spec requirement | Current state | Gap |
|------------------|---------------|-----|
| Vouchers show **no prices** | ✅ Hotel & tour PDFs have no prices | — |
| **Link existing** tours/transfers — **no new forms** | We have free-text “additional_services” (Tour: … / Transfer: …) | Spec wants **real links** to `tours.id` and `vouchers.id` (transfers) |
| New table `voucher_services` (voucher_id, service_type, reference_id, sort_order) | Not present | **Create table** + migration |
| Searchable dropdown querying `tours` + `transfers` | Textarea only | **Add AJAX search** `GET /api/search-services?q=...` returning tours + transfers |
| Guest Program on PDF: Date, Time, Service, Pickup | PDF has “Additional Services” as type + description only | **Add Guest Program table** with date/time from linked tour/transfer |
| Calendar shows linked services within stay | Calendar shows `additional_services_text` from JSON | After `voucher_services`: calendar should load linked tours/transfers and show in stay block |

**Conclusion:** Implement proper “link existing” flow:

1. Add **`voucher_services`** table and migration.
2. **HotelController:** save/load voucher_services; add **search endpoint** for tours + transfers (e.g. `/api/search-services` or in HotelController).
3. **Voucher form (create/edit):** replace or supplement additional_services textarea with **searchable dropdown** + sortable list of linked services; save to `voucher_services`.
4. **Voucher PDF:** replace current “Additional Services” with **Guest Program** table (date, time, service, pickup) from linked records.
5. **Calendar:** optionally enhance to show linked tour/transfer events within voucher stay.

---

## Phase 0 Master Checklist (from spec)

| Item | Status | Notes |
|------|--------|--------|
| Tour per-pax pricing (adult/child/infant auto-calc) | Partial | Adult+child done; add infant |
| Hotel room capacity validation + XLSX import | Partial | Capacity present; add validation |
| Hotel occupancy-tier pricing | Partial | Data present; ensure used in calc |
| Price-free vouchers | Done | PDFs have no prices |
| Link **existing** tours/transfers to vouchers (no new forms) | Gap | Need `voucher_services` + search UI |
| Guest Program on voucher PDF | Partial | Need proper table from linked services |
| Calendar sync with linked services | Partial | Currently text; can enhance with real links |

---

## Recommended Implementation Order

1. **0.3 First (biggest gap):**  
   - Create `voucher_services` table.  
   - Add search endpoint (tours + transfers).  
   - Voucher form: searchable “Link service” + list; save to `voucher_services`.  
   - PDF: Guest Program table from `voucher_services` (date, time, service, pickup).  
   - Calendar: load from `voucher_services` and display in stay block.

2. **0.1 Next:**  
   - Add `price_per_infant` to tours; include in total calc and forms.  
   - Optionally add price_adult/price_child/price_infant to `services` and ServiceController.  
   - Add price breakdown on tour voucher_show (and optionally internal PDF).

3. **0.2 Then:**  
   - Validate `(adults + children) ≤ room capacity` in hotel voucher and portal.  
   - Optionally add hotel age limits and room max_adults/max_children + XLSX.

This order gets “link existing services + Guest Program” (spec core) done first, then completes pricing and capacity.
