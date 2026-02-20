# CYN Tourism Management System - Developer Guidelines

## Project Tech Stack
- **Language**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Structure**: Custom PHP architecture 
- **Frontend**: HTML/CSS/JS

## Coding Standards & Rules
1. **Security First**: 
   - Always use prepared statements for SQL queries (e.g., `Database::fetchOne()`).
   - All forms MUST include CSRF tokens (`echo csrf_field();`).
   - Always escape user output using `e($userInput)` to prevent XSS.
2. **Form Validation**:
   - Use the custom `Validator` class in PHP for all backend validation.
   - Use `data-validate` attributes for frontend HTML forms.
3. **Logging**:
   - Use the custom `Logger` class for important system events (e.g., `Logger::error`, `Logger::info`, `Logger::activity`).
4. **Directory Rules**:
   - `assets/`: Frontend styles and scripts.
   - `config.php`: Global configuration. Do not hardcode passwords here if sharing code.
   - `api/`: API endpoints returning JSON.
   - `views/` & `templates/`: UI components and layouts.
5. **Translations**: 
   - The app is multi-language (en/tr). Always use the `__('translation_key')` helper function for visible text instead of hardcoding English.

## Agent Team Instructions
When collaborating as a team within this project, use the advanced specialized team setup:

> "I need to execute a massive system-wide enhancement and a specific 'Tour Section' refactor. Create an advanced agent team with 4 specialized teammates. Before starting, ALL teammates must read the project guidelines and MVC architecture rules in CLAUDE.md.
> Spawn the following teammates:
> **1. Database & Security Architect (Domain: `/database/`, `src/Core/`, `index.php`)**
> *Task:* Execute Phase 1 (System Audit). Scan for routing errors, missing methods, and DB mismatches. Enforce security standards (CSRF, Auth checks). Create any missing tables or columns required for the Tour catalog and company settings.
> **2. Backend & Business Logic Engineer (Domain: `src/Controllers/`, `src/Models/`)**
> *Task:* Fix the Calendar Controller tour events. Completely rebuild the TourController logic to stop passing hotel/location fields and route adult/child prices strictly to invoices. Implement the Smart Pricing Engine and Partner Credit logic.
> **3. Frontend & UI/UX Specialist (Domain: `views/`, `assets/`)**
> *Task:* Execute Phase 6 (Responsive UI Overhaul) across all devices. Restructure the Tour Voucher UI: remove hotel/location fields, rename 'Customers' to 'Guest Details', and merge 'Pax Counts' into the Tours section rows. Ensure Bootstrap 5 and Alpine.js are implemented cleanly.
> **4. Document & QA Specialist (Domain: PDF rendering, E2E flow)**
> *Task:* Execute Phase 2 and Phase 5. Ensure the company identity (name, address, phone) is dynamically pulled and flawlessly injected into all Dompdf templates. Verify the automated receipt generation for partner credit recharges works. Test the full CRUD and PDF generation flow once the other agents finish their tasks.
> **Rules for the Lead Agent:**
> Coordinate the task list carefully. Do not let the Frontend or Document agents build views until the Database architect has confirmed the schema. Require my approval before executing any SQL `DROP` or `DELETE` commands, or permanently deleting any files."

### General Rules for Teammates:
- **Communication:** If you change a database query or schema, communicate the change so frontend teammates update their templates.
- **Translations:** If you add a new visible string, add it to both `languages/en.php` and `languages/tr.php`.
- **Restricted Areas:** Do not make changes to `backups/` or `logs/`.
- **Hierarchy:** Let the Team Lead handle overall architecture decisions.
