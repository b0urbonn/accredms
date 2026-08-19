# Changelog

All notable changes to the SUC Accreditation Document Management System are documented here.

## [Unreleased]

### Added

- Added photo evidence capture for Faculty and Admin users.
  - Supports laptop/browser camera capture.
  - Supports smartphone camera capture when accessed through a secure browser context.
  - Supports selecting multiple photos from a device gallery.
  - Allows photos to be tagged to a specific `Documents Needed` checklist item.
- Added private `evidence_photos` storage and database records.
- Added server-side image normalization before storage:
  - Maximum dimension of 2,000px.
  - JPEG conversion and compression at quality 78.
  - Portrait and landscape orientation are preserved.
- Added one statement-level PDF evidence report for all captured photos.
  - Photos are grouped under their corresponding `Documents Needed` sections.
  - Additional evidence tags automatically appear in the same regenerated PDF.
  - Each additional evidence section starts on a new page.
  - Images are centered and rendered proportionally without stretching.
- Added authenticated PDF streaming and download routes for photo evidence.
- Added inline `View PDF` evidence cards for captured photos alongside regular PDF evidence.
- Added a unified `Add Evidence` chooser with two options:
  - Upload PDF Evidence.
  - Capture or Upload Photos.
- Added feature coverage in `tests/Feature/EvidencePhotoTest.php`.

### Changed

- Checklist completion now recognizes both PDF evidence tags and photo evidence tags.
- Photo-only evidence now updates:
  - Checklist completion indicators.
  - Statement review status.
  - Parameter progress.
  - Area progress.
  - Official area report evidence status.
- PDF deletion no longer removes checklist completion when photo evidence remains.
- Evidence deletion resets the statement only when both PDF and photo evidence are absent.
- Captured photos are grouped as one statement-level PDF evidence item instead of separate PDF files per tag.
- The photo report layout was changed from a standalone report/direct-image viewer to the standard PDF evidence viewer flow.
- The capture modal was reorganized into clear Camera, Gallery / Device, and Selected Photos sections.
- PHP local upload limits were raised for multi-photo uploads:
  - `upload_max_filesize=15M`
  - `post_max_size=80M`

### Fixed

- Fixed photo evidence not appearing as completed in the checklist.
- Fixed photo evidence not contributing to area and parameter progress.
- Fixed photos being stored at unnecessarily large original camera resolutions.
- Fixed photo PDF images being stretched by using proportional dimensions.
- Fixed stale PDF viewer responses by adding layout/version cache-busting to photo evidence URLs.
- Fixed blank or misarranged report pages by removing forced breaks before the first evidence section.
- Fixed photo evidence cards rendering when no photos existed.
- Fixed an empty historical migration file that prevented a clean migration run.

### Dependencies

- Added `dompdf/dompdf` for generating one PDF from captured photo evidence.

### Routes

Added or updated the following photo evidence routes:

- `POST /subfolders/{subfolder}/evidence-photos`
- `GET /evidence-photos/{evidencePhoto}/pdf`
- `GET /evidence-photos/{evidencePhoto}/pdf/download`
- `DELETE /evidence-photos/{evidencePhoto}`

Removed the standalone photo report route because photo evidence now uses the standard PDF evidence viewer.

### Files and Areas Affected

- `database/migrations/2026_08_19_000001_create_evidence_photos_table.php`
  - Creates the private photo evidence table with the statement, uploader, checklist tag, file metadata, checksum,
    caption, status, timestamps, and soft deletes.
- `app/Models/EvidencePhoto.php`
  - Adds the photo evidence Eloquent model, uploader/subfolder relationships, casts, and formatted file size.
- `app/Models/Subfolder.php`
  - Adds the `photos()` relationship.
  - Combines PDF `covered_evidences` and photo `checklist_item` values when calculating completed checklist items.
  - Treats either a PDF or a photo as evidence in the subfolder tree.
- `app/Models/Parameter.php`
  - Includes photo counts in parameter progress calculations.
- `app/Services/EvidencePhotoUploadService.php`
  - Validates photo size, normalizes orientation, resizes to a maximum 2,000px dimension, converts to JPEG, compresses,
    stores on the private disk, calculates SHA-256, and creates the database record.
- `app/Services/EvidencePhotoPdfService.php`
  - Loads every photo for the statement, converts supported image formats, groups images by checklist tag, and renders
    the single statement-level PDF.
- `app/Http/Controllers/Faculty/EvidencePhotoController.php`
  - Handles authorized multi-photo uploads and grouped photo deletion.
  - Validates the selected tag against the statement checklist.
  - Updates checklist completion and review status after upload/delete.
- `app/Http/Controllers/EvidencePhotoStreamController.php`
  - Provides policy-checked inline PDF streaming and download responses.
- `app/Http/Controllers/Faculty/DocumentController.php`
  - Preserves photo-based completion when PDF evidence is deleted.
- `app/Http/Controllers/Faculty/SubfolderController.php`
  - Allows checklist actions when either PDF or photo evidence exists.
- `app/Http/Controllers/Admin/AreaController.php` and `app/Http/Controllers/Accreditor/BrowseController.php`
  - Eager-load photos and include them in statement completion progress.
- `app/Http/Controllers/Admin/ReportController.php`
  - Includes photo evidence in official area report completion status.
- `resources/views/components/evidence-upload-choice-modal.blade.php`
  - Adds the first-step PDF versus photo evidence chooser and organized capture styles.
- `resources/views/admin/areas/show.blade.php` and `resources/views/accreditor/show_area.blade.php`
  - Add the chooser, camera/gallery capture flow, selected-photo queue, and PDF viewer refresh handling.
- `resources/views/admin/areas/_subfolder_row.blade.php` and `resources/views/accreditor/_subfolder_row.blade.php`
  - Keep the original PDF evidence card and add photo evidence to the same available-evidence column.
- `resources/views/components/evidence-photo-card.blade.php`
  - Displays all statement photos as one `View PDF` evidence item.
- `resources/views/pdf/evidence_photos.blade.php`
  - Formats the generated PDF with the parameter, category, statement, separate Documents Needed sections, centered
    portrait/landscape photos, compact spacing, and page breaks between evidence sections.
- `resources/views/components/pdf-viewer-modal.blade.php`
  - Resets the PDF viewer zoom when a document is opened.
- `tests/Feature/EvidencePhotoTest.php`
  - Covers multi-photo upload, orientation preservation, compression/resizing, checklist completion, PDF generation,
    authorization, and evidence-card rendering.
- `routes/web.php`
  - Registers the photo upload, PDF stream, PDF download, and delete routes.
- `composer.json` and `composer.lock`
  - Add `dompdf/dompdf` for generated photo evidence PDFs.

### Database and Storage Behavior

- Photos are stored outside the public web root on the `local_private` disk.
- Stored paths follow the accreditation hierarchy:
  `evidence-photos/{area}/{parameter}/{category}/{subfolder}/{uuid}.jpg`.
- Original client filenames remain display metadata only; stored filenames are UUID-based.
- The original image is not stored after normalization. The normalized JPEG is the evidence file used by the PDF renderer.
- Deleting a photo evidence card removes all photos for that statement-level evidence set and updates checklist status.

### User Flow

1. Faculty/Admin clicks `Add Evidence` on a statement.
2. The system asks whether the evidence is a PDF or a captured/uploaded photo.
3. PDF selection continues through the existing PDF upload, checklist tagging, compression, and PDF viewer flow.
4. Photo selection opens the Camera, Gallery / Device, and Selected Photos sections.
5. One or more photos are assigned to a valid Documents Needed checklist item.
6. The selected photos are normalized and stored privately.
7. The selected checklist item becomes complete when photo evidence exists.
8. `View PDF` generates one statement-level PDF containing all photo evidence.
9. The PDF separates each Documents Needed item into its own section and starts each later section on a new page.

### Local Development Changes

- Laravel Herd PHP 8.4 and Composer were used for local validation.
- Herd PHP configuration was updated locally at:
  `~/Library/Application Support/Herd/config/php/84/php.ini`
  - `upload_max_filesize=15M`
  - `post_max_size=80M`
- The application was returned to local-only mode with `APP_URL=http://127.0.0.1:8000`.
- Temporary Expose tunneling configuration was removed after smartphone testing.
- The empty historical migration
  `database/migrations/2026_08_15_000002_create_document_subfolder_table.php`
  was converted into a valid reversible no-op migration so the complete migration chain can run.

### Removed or Superseded

- Removed the standalone photo report controller, report template, and direct image viewer.
- Removed the standalone photo report route.
- Photo evidence now uses the same authenticated PDF viewer pattern as regular PDF evidence.

### Validation

- Full Laravel test suite passes: **49 tests, 283 assertions**.
- Focused photo evidence test covers:
  - Multiple photo uploads.
  - Portrait and landscape preservation.
  - Server-side resizing and JPEG conversion.
  - Checklist auto-completion.
  - Statement-level PDF generation.
  - Authenticated PDF viewing.
  - Inline evidence-card rendering.
- Blade templates compile successfully with `php artisan view:cache`.
- Vite production build passed during local setup.
