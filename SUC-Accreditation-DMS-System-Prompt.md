# SYSTEM DEVELOPMENT PROMPT
## SUC Accreditation Document Management System (DMS)
### College of Information and Computing Sciences — Marinduque State University

---

## ROLE INSTRUCTION (for the AI/Developer building this)

Act as a **senior Laravel web developer, system analyst, and database architect**. Design and build a secure, professional, production-grade **Accreditation Document Management System (ADMS)** for the College of Information and Computing Sciences (CICS), Marinduque State University. The system must follow Laravel best practices (SOLID principles, service/repository pattern where appropriate, form requests, policies, resource controllers), a **fully normalized (3NF) relational database**, and a clean, modern **Bootstrap 5** front end themed in **Apple Green**.

---

## 1. PROJECT OVERVIEW

The system digitizes and organizes accreditation documentary requirements for Survey of Unit Compliance (SUC) accreditation. It replaces manual/paper-based document submission with a structured, role-based digital repository that mirrors the accreditation folder hierarchy (Area → Parameter → Category → Subfolder → Files), with controlled upload, viewing, compression, and auditing.

---

## 2. TECH STACK

| Layer | Technology |
|---|---|
| Backend Framework | Laravel 11 (PHP 8.3+) |
| Frontend Framework | Bootstrap 5.3 + vanilla JS / Alpine.js (for lightweight reactivity) |
| Database | MySQL 8 (InnoDB, utf8mb4, normalized 3NF) |
| Auth | Laravel Breeze (Blade) or Laravel Fortify + Sanctum for session guard |
| Authorization | Laravel Policies & Gates + `spatie/laravel-permission` (roles/permissions) |
| File Storage | Laravel Filesystem (local `storage/app/private` or S3-compatible), never `public` disk for raw documents |
| Queue/Jobs | Laravel Queue (Redis or database driver) — for async PDF compression |
| PDF Compression | Ghostscript (`gs`) invoked via Laravel `Process` facade, or `spatie/pdf-to-image` + custom compression pipeline |
| PDF Viewing | PDF.js embedded inside a Bootstrap Extra-Large (`modal-xl`) / fullscreen modal, streamed via a signed, authenticated route (never a public storage URL) |
| Audit Trail | `spatie/laravel-activitylog` or custom `audit_logs` table |
| Search | Laravel Scout (optional) or simple indexed DB search on filenames/metadata |
| Notifications | Laravel Notifications (database + optional email) |
| Testing | Pest or PHPUnit (feature tests for upload, access control, compression) |

---

## 3. USER ROLES & PERMISSIONS

### 3.1 Administrator (Super Admin)
- Full access to **all Areas**, Parameters, Categories, Subfolders, and Files (view, upload, download, rename, delete, restore).
- Create/edit/deactivate **Areas** and **Parameters**.
- **Assign Areas to Faculty** (as Handler/Chairperson or Member) and to **Accreditors** (view-only).
- Manage user accounts (CRUD, activate/deactivate, reset password, assign system role).
- View system-wide dashboard: upload stats, storage usage, compliance completion per area, recent activity.
- View full **audit logs** (who uploaded/viewed/deleted what and when).
- Configure system settings (max upload size, allowed file types, compression threshold).

### 3.2 Faculty (Area Handler / Chairperson / Member)
- Access **only the Area(s) assigned to them** by the Administrator.
- Full CRUD on documents **within their assigned Area(s)** only:
  - Create Subfolders under System Input & Process / Outcomes / Implementation.
  - Upload one or multiple **PDF files** into a Subfolder.
  - Replace/version, rename, soft-delete their own uploads.
  - Compress files larger than 10MB before/upon upload.
- View upload history and status for their Area only.
- Cannot see or access other Areas.

### 3.3 Accreditor
- **View-only** access to the specific Area(s) assigned to them by the Administrator.
- Can browse the folder hierarchy (Area → Parameter → Category → Subfolder → Files) of their assigned Area(s).
- Can **open/preview PDF files** in the Extra-Large modal viewer.
- **Cannot** upload, edit, rename, delete, or download (download permission configurable — default: view only, watermarked).
- Optional: leave remarks/comments/findings per document or per area (recommended enhancement).

### 3.4 Role/Permission Matrix

| Action | Admin | Faculty (assigned area) | Accreditor (assigned area) |
|---|:---:|:---:|:---:|
| View all areas | ✅ | ❌ | ❌ |
| View assigned area(s) | ✅ | ✅ | ✅ |
| Create Area/Parameter | ✅ | ❌ | ❌ |
| Assign Area to Faculty/Accreditor | ✅ | ❌ | ❌ |
| Create Subfolder | ✅ | ✅ (own area) | ❌ |
| Upload PDF | ✅ | ✅ (own area) | ❌ |
| Compress file | ✅ | ✅ | ❌ |
| Delete/Rename file | ✅ | ✅ (own upload) | ❌ |
| View/Preview PDF | ✅ | ✅ | ✅ |
| Download PDF | ✅ | ✅ | ⚙️ configurable |
| View audit logs | ✅ | ❌ (own actions only, optional) | ❌ |
| Manage users | ✅ | ❌ | ❌ |

---

## 4. FOLDER / DOCUMENT HIERARCHY

```
Root
 └── Area (e.g., Area I – Vision, Mission, Goals & Objectives)
      └── Parameter (e.g., Parameter 1.1, 1.2, ...)
           ├── System Input & Process (fixed category)
           │     └── Subfolder(s) (dynamic, user-named)
           │          └── PDF File(s) (multiple)
           ├── Outcomes (fixed category)
           │     └── Subfolder(s)
           │          └── PDF File(s)
           └── Implementation (fixed category)
                 └── Subfolder(s)
                      └── PDF File(s)
```

- **Area** and **Parameter** are Admin-managed (structural/master data).
- **Category** is a **fixed, system-seeded** set of exactly 3 values per Parameter: `System Input and Process`, `Outcomes`, `Implementation`. These are auto-generated the moment a Parameter is created (no user creates or deletes them).
- **Subfolder** is created by the assigned Faculty (or Admin) inside a Category — this is where documents are logically grouped (e.g., "2023 Reports", "Minutes of Meetings").
- **Files** are uploaded inside a Subfolder — **PDF only**, multiple files allowed per subfolder.

---

## 5. DATABASE DESIGN (NORMALIZED — 3NF)

### 5.1 Core Tables

**`roles`**
| Field | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| name | VARCHAR(50) UNIQUE | `admin`, `faculty`, `accreditor` |
| guard_name | VARCHAR(50) | for spatie/permission |
| timestamps | | |

**`users`**
| Field | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| employee_id | VARCHAR(50) NULLABLE UNIQUE | |
| name | VARCHAR(150) | |
| email | VARCHAR(150) UNIQUE | |
| password | VARCHAR(255) | hashed |
| role_id | BIGINT FK → roles.id | primary system role |
| status | ENUM('active','inactive') | default active |
| avatar_path | VARCHAR(255) NULLABLE | |
| last_login_at | TIMESTAMP NULLABLE | |
| timestamps, softDeletes | | |

**`areas`**
| Field | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| code | VARCHAR(20) UNIQUE | e.g., "AREA-I" |
| name | VARCHAR(255) | e.g., "Vision, Mission, Goals and Objectives" |
| description | TEXT NULLABLE | |
| status | ENUM('active','inactive') | |
| created_by | BIGINT FK → users.id | |
| timestamps, softDeletes | | |

**`area_user`** (pivot — assignment table)
| Field | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| area_id | BIGINT FK → areas.id | |
| user_id | BIGINT FK → users.id | |
| assignment_role | ENUM('handler','member','accreditor') | role **within that area** |
| assigned_by | BIGINT FK → users.id | admin who made the assignment |
| assigned_at | TIMESTAMP | |
| timestamps | | |
| **UNIQUE** (area_id, user_id, assignment_role) | | prevents duplicate assignments |

**`parameters`**
| Field | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| area_id | BIGINT FK → areas.id | |
| code | VARCHAR(20) | e.g., "1.1" |
| title | VARCHAR(255) | |
| description | TEXT NULLABLE | |
| sort_order | SMALLINT | for display ordering |
| status | ENUM('active','inactive') | |
| timestamps, softDeletes | | |

**`categories`** (lookup/master table — seeded, fixed 3 rows)
| Field | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| name | VARCHAR(100) UNIQUE | `System Input and Process`, `Outcomes`, `Implementation` |
| slug | VARCHAR(100) UNIQUE | |
| sort_order | TINYINT | |

**`parameter_categories`** (junction — auto-created per Parameter; this is the actual "folder" node)
| Field | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| parameter_id | BIGINT FK → parameters.id | |
| category_id | BIGINT FK → categories.id | |
| timestamps | | |
| **UNIQUE** (parameter_id, category_id) | | |

**`subfolders`**
| Field | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| parameter_category_id | BIGINT FK → parameter_categories.id | |
| name | VARCHAR(255) | |
| created_by | BIGINT FK → users.id | |
| status | ENUM('active','archived') | |
| timestamps, softDeletes | | |

**`documents`**
| Field | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| subfolder_id | BIGINT FK → subfolders.id | |
| uploaded_by | BIGINT FK → users.id | |
| original_filename | VARCHAR(255) | |
| stored_filename | VARCHAR(255) | UUID-based, non-guessable |
| disk | VARCHAR(50) | e.g., `local_private` |
| file_path | VARCHAR(500) | |
| mime_type | VARCHAR(100) | must be `application/pdf` |
| file_size_bytes | BIGINT | current (post-compression) size |
| original_size_bytes | BIGINT NULLABLE | size before compression |
| is_compressed | BOOLEAN DEFAULT FALSE | |
| compression_status | ENUM('none','pending','processing','done','failed') | for queued jobs |
| checksum_sha256 | VARCHAR(64) | integrity + duplicate detection |
| version | SMALLINT DEFAULT 1 | |
| status | ENUM('active','archived') | |
| timestamps, softDeletes | | |

**`document_versions`** (optional but recommended for audit-friendly re-uploads)
| Field | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| document_id | BIGINT FK → documents.id | |
| version | SMALLINT | |
| file_path | VARCHAR(500) | |
| file_size_bytes | BIGINT | |
| uploaded_by | BIGINT FK → users.id | |
| created_at | TIMESTAMP | |

**`audit_logs`**
| Field | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| user_id | BIGINT FK → users.id NULLABLE | |
| action | VARCHAR(100) | `upload`, `view`, `download`, `delete`, `assign_area`, `login`, etc. |
| auditable_type | VARCHAR(150) | polymorphic model class |
| auditable_id | BIGINT | polymorphic model id |
| description | VARCHAR(255) | |
| ip_address | VARCHAR(45) | |
| user_agent | VARCHAR(255) | |
| created_at | TIMESTAMP | |

**`document_remarks`** (optional — for Accreditor findings/comments)
| Field | Type | Notes |
|---|---|---|
| id | BIGINT PK | |
| document_id | BIGINT FK → documents.id | |
| user_id | BIGINT FK → users.id | accreditor who commented |
| remark | TEXT | |
| timestamps | | |

### 5.2 Relationship Summary (ERD logic)
- `users` ⇄ `areas` — many-to-many via `area_user` (with role-in-area).
- `areas` 1—* `parameters`
- `parameters` *—* `categories` via `parameter_categories` (but functionally 1 Parameter always has exactly 3 category rows — enforce in a Model Observer, not by making it purely M:M in the UI).
- `parameter_categories` 1—* `subfolders`
- `subfolders` 1—* `documents`
- `documents` 1—* `document_versions`
- All major actions logged to `audit_logs` (polymorphic).

> This design is fully normalized to **3NF**: no repeating groups, every non-key attribute depends only on the primary key of its own table, and the fixed 3-category rule is enforced at the application layer (Observer/Service) rather than hardcoding categories into the `parameters` table — keeping the schema flexible if requirements ever expand beyond 3 categories.

---

## 6. FILE UPLOAD, VALIDATION & COMPRESSION RULES

1. **Accepted type:** `.pdf` only — validate both by MIME type (`application/pdf`) **and** file signature/magic bytes (`%PDF-`) server-side; never trust the client `Content-Type` header alone.
2. **Max size:** configurable system setting (e.g., 25MB hard limit after compression).
3. **>10MB trigger:** If uploaded file exceeds 10MB, the UI presents a **"Compress before upload"** toggle/option:
   - Client uploads the original file to a temporary/staging path.
   - A **queued job** (`CompressPdfJob`) runs Ghostscript (or equivalent) server-side to reduce size (e.g., downsample images, reduce PDF quality preset `/ebook` or `/screen`).
   - Store both `original_size_bytes` and final `file_size_bytes`; set `is_compressed = true`.
   - If compression fails or doesn't sufficiently reduce size, notify the user and allow manual retry or upload-as-is (subject to hard limit).
   - Show a progress indicator (queued → processing → done) via polling or Laravel Echo/broadcasting.
4. **Multiple file upload:** Support drag-and-drop multi-file upload into a Subfolder, each file processed/validated independently.
5. **Filename handling:** Store files under a **UUID-based filename** on disk; keep `original_filename` for display only — prevents path traversal and filename collision attacks.
6. **Storage location:** Files stored **outside the public webroot** (`storage/app/private/documents/{area_id}/{parameter_id}/{category}/{subfolder_id}/`), served only through an authenticated, policy-checked streaming route.
7. **Duplicate detection:** Compute SHA-256 checksum on upload; warn if identical file already exists in the same subfolder.

---

## 7. SECURITY REQUIREMENTS

- **Authentication:** Laravel Breeze/Fortify with hashed passwords (bcrypt/argon2id), enforced password policy, optional 2FA for Admin accounts.
- **Authorization:** Laravel **Policies** for every model (`AreaPolicy`, `DocumentPolicy`, `SubfolderPolicy`) checked in controllers/Form Requests — never rely on front-end hiding alone.
- **Area-scoping:** Global query scopes / middleware to auto-restrict Faculty and Accreditor queries to their assigned `area_id`s only (defense in depth against IDOR).
- **File serving:** No direct public URLs to storage; all views/downloads go through a signed, authenticated controller route (`GET /documents/{document}/stream`) that re-verifies the policy on every request.
- **Upload security:**
  - Strict MIME + magic-byte validation (reject disguised executables).
  - Re-encode/sanitize PDFs on the server if feasible (strip embedded JavaScript using a library like `qpdf --linearize --decrypt` or a dedicated sanitizer) to mitigate malicious PDF payloads.
  - Virus scan integration hook (ClamAV via `clamscan`) recommended before finalizing an upload.
  - Enforce max file size at both Nginx/PHP (`upload_max_filesize`, `post_max_size`) and Laravel validation layers.
- **CSRF protection** on all forms (Laravel default).
- **Rate limiting** on login and upload endpoints (`throttle` middleware).
- **SQL Injection:** Eloquent ORM / query builder with parameter binding only — no raw concatenated SQL.
- **XSS:** Blade's automatic escaping (`{{ }}`), sanitize any rich text (remarks/comments) with a whitelist sanitizer.
- **Audit logging** of all sensitive actions (login, upload, view, download, delete, area assignment changes).
- **HTTPS enforced** in production (`FORCE_HTTPS`), secure cookies, `SameSite` cookie policy.
- **Soft deletes** on Areas, Parameters, Subfolders, Documents for recoverability + compliance trail (no hard delete by non-admins).
- **Backup strategy:** scheduled DB + storage backups (documented, not necessarily built into the app).

---

## 8. UI/UX & THEME GUIDELINES

- **Framework:** Bootstrap 5.3, mobile-responsive, sidebar layout (collapsible) for navigation.
- **Theme — "Apple Green":**
  - Primary: `#8DB600` / accent gradient `#A4C639 → #6E9B1E`
  - Secondary/dark: `#3E5C1F` (deep olive-green, for headers/sidebar)
  - Neutral background: `#F7F9F3` (soft off-white with a green tint)
  - Accent/alert colors: standard Bootstrap semantic colors (success/warning/danger) tuned to harmonize with green.
  - Typography: clean sans-serif (e.g., "Poppins" or "Inter"), consistent heading scale, generous whitespace for a professional, uncluttered academic look.
- **Branding:** CICS / Marinduque State University logo in navbar/sidebar header; footer with institutional info.
- **Navigation pattern:** Breadcrumb trail reflecting the hierarchy: `Home / Area I / Parameter 1.1 / Outcomes / 2023 Reports`.
- **Folder browser UI:** Card-based or file-explorer-style grid/list toggle showing folder icons (Area, Parameter, Category, Subfolder) and file icons (PDF) with metadata (uploader, date, size, status).
- **Dashboard widgets (role-specific):**
  - Admin: total areas, total documents, storage used, pending compressions, recent activity feed, per-area completion %.
  - Faculty: their assigned area(s) progress, recent uploads, quick-upload button.
  - Accreditor: assigned area(s) list, quick access to recently viewed documents.
- **Accessibility:** proper ARIA labels, keyboard-navigable modals, sufficient color contrast against the green theme.

---

## 9. PDF VIEWER REQUIREMENT

- Clicking a PDF file opens a **Bootstrap Extra-Large Modal** (`modal-xl` or `modal-fullscreen-lg-down`) embedding **PDF.js** (or an `<iframe>`/`<embed>` pointed at the authenticated streaming route) to preview the file in-browser — **no forced download** required to view.
- Modal includes: filename/title, uploader & upload date metadata, zoom controls (native to PDF.js), page navigation, and a close button.
- **Accreditor role:** view-only in this modal; download/print buttons hidden or disabled by default (configurable per system setting), and consider a **watermark overlay** (e.g., "For Accreditation Review Only — [Accreditor Name] — [Date]") rendered dynamically for traceability.
- Every "view" event is logged to `audit_logs` for compliance traceability (who viewed what document and when).

---

## 10. RECOMMENDED APPLICATION STRUCTURE (Laravel)

```
app/
 ├── Http/
 │    ├── Controllers/
 │    │    ├── Admin/ (AreaController, ParameterController, UserController, AssignmentController)
 │    │    ├── Faculty/ (SubfolderController, DocumentController)
 │    │    ├── Accreditor/ (BrowseController, DocumentViewController)
 │    │    └── DocumentStreamController.php (shared authenticated file streaming)
 │    ├── Requests/ (StoreDocumentRequest, StoreAreaRequest, AssignAreaRequest, ...)
 │    └── Middleware/ (EnsureAreaAccess, RoleMiddleware)
 ├── Models/ (User, Role, Area, Parameter, Category, ParameterCategory, Subfolder, Document, DocumentVersion, AuditLog, DocumentRemark)
 ├── Policies/ (AreaPolicy, DocumentPolicy, SubfolderPolicy)
 ├── Services/ (DocumentUploadService, PdfCompressionService, AuditLogService)
 ├── Jobs/ (CompressPdfJob, ScanUploadedFileJob)
 ├── Observers/ (ParameterObserver → auto-creates the 3 parameter_categories)
 └── Notifications/ (AreaAssignedNotification, DocumentUploadedNotification)
```

---

## 11. SUGGESTED DEVELOPMENT PHASES

1. **Phase 1 — Foundations:** Auth, roles/permissions, DB migrations & seeders (roles, categories).
2. **Phase 2 — Master data:** Area & Parameter management (Admin), auto-generation of the 3 fixed categories per Parameter.
3. **Phase 3 — Assignment module:** Admin assigns Faculty/Accreditor to Areas.
4. **Phase 4 — Folder & document module:** Subfolder CRUD, multi-file PDF upload, validation, storage.
5. **Phase 5 — Compression pipeline:** Queue + Ghostscript integration, progress UI.
6. **Phase 6 — PDF viewer:** Modal + PDF.js integration, streaming route, watermarking for accreditors.
7. **Phase 7 — Security hardening:** Policies, audit logs, rate limiting, sanitization, HTTPS.
8. **Phase 8 — UI polish:** Apple Green theme, dashboards, responsive QA.
9. **Phase 9 — Testing & UAT:** Feature tests, role-based access tests, user acceptance testing with CICS faculty.

---

## 12. OPTIONAL ENHANCEMENTS (for future consideration)
- Real-time notifications (Laravel Echo + Pusher/Reverb) when a document is uploaded/assigned.
- Export a full Area's document checklist/status as a PDF/Excel compliance report.
- E-signature/approval workflow before an Area is marked "submission-ready."
- Full-text search across document metadata and filenames.
- Dark mode toggle (retaining Apple Green accent).
