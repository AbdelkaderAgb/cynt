# Project Edit Requirements — Professional Brief

## 1. Pricing model

### 1.1 Tours
- **Unit price**: Stored and displayed as **per 1 pax**.
- **Calculation**: For 2 pax the total = unit price × 2; for N pax = unit price × N.
- **Child vs adult**: Support **separate prices** for adults and children (e.g. adult price, child price).

### 1.2 Hotels
- **Per pax**: Support **adult price** and **child price** (e.g. per night or per stay) for rooms/tours where applicable.
- **Room capacity**: Every room type has a **capacity** (max pax). This must be:
  - Editable in **admin** (hotel profile).
  - Importable via **XLSX** (hotel/room import).

---

## 2. Vouchers

- **No prices on vouchers**: Vouchers must **not** include prices (amounts, totals, or line-item prices).
- **Additional services**: If the booking includes a **tour** or **transfer**, these can be added as **additional services** on the voucher.
- **Hotel calendar**: The voucher (with optional tour/transfer as additional services) should be **sendable to the hotel calendar** (integrate or export so the hotel receives the relevant info).

---

## 3. Summary of implementation tasks

| # | Area        | Task |
|---|-------------|------|
| 1 | Tours       | Store/display tour price as per 1 pax; compute total = price × pax count. |
| 2 | Tours       | Add and use separate adult vs child pricing for tours. |
| 3 | Hotels      | Add and use separate adult vs child pricing for hotel rooms. |
| 4 | Hotels      | Add room capacity; make it editable in admin hotel profile and in XLSX import. |
| 5 | Vouchers    | Remove all prices from voucher content/templates. |
| 6 | Vouchers    | Allow adding tour/transfer as additional services on the voucher. |
| 7 | Integration | Ensure voucher (with optional tour/transfer) can be sent to hotel calendar. |

---

## 4. Implementation status

| # | Task | Status |
|---|------|--------|
| 1 | Tour price per 1 pax; total = adult×price + child×price | Done: `price_per_person`, `price_child`, forms & controller |
| 2 | Separate adult/child pricing for tours | Done: `price_child` in DB; create/edit forms |
| 3 | Adult/child pricing for hotels | Already present: `price_child` in `hotel_rooms` |
| 4 | Room capacity (admin + XLSX) | Already present: Capacity in hotel profile form & XLSX column 7 |
| 5 | Vouchers do not include prices | Done: Hotel & tour voucher PDFs no longer show any prices |
| 6 | Additional services (tour/transfer) on voucher | Done: `additional_services` JSON; form; PDF section; hotel calendar |
| 7 | Send voucher to hotel calendar | Done: Hotel calendar shows vouchers; additional services shown in event popup |

**Run migration:** `database/migration_fix_columns.sql` (adds `tours.price_child`, `hotel_vouchers.additional_services`).

---

*Use this document as the single source of truth when editing the project.*
