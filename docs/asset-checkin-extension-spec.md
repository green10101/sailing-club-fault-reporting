# Asset Check-In Extension Specification

Status: Draft (Planning only, no implementation)
Last updated: 2026-04-14

## 1. Purpose

Add an asset check-in workflow after each use, while preserving the existing fault reporting workflow.

### Goals

- Users scan an asset-specific QR code and open an asset-specific check-in URL.
- User submits:
  - Name
  - Contact email address
  - Asset name (preselected from QR URL context)
  - Date (auto-populated by system)
  - Checklist answers:
    - Is the asset put away as found?
    - Is the asset in a safe condition for the next user?
    - Does the asset have faults that should be rectified?
- If Question 2 or Question 3 is Yes, ask if the damage happened during this checkout and capture fault description for the existing fault reports table.
- Add a bosun report view for check-in history (newest first, filter by asset).
- Add Number of Uses to the asset status view.
- Deploy to the same cPanel host and same existing database.

### Non-goals

- Replacing or redesigning the current fault reporting flow.
- Moving to a new hosting platform.
- Rebuilding authentication or user roles.

## 2. Existing System Constraints

The current application already separates public and bosun workflows:

- Public route handling and entry point: [public/index.php](../public/index.php)
- Public fault controller: [src/Controllers/PublicController.php](../src/Controllers/PublicController.php)
- Bosun fault/asset controller: [src/Controllers/BosunController.php](../src/Controllers/BosunController.php)
- Asset model (currently named Boat): [src/Models/Boat.php](../src/Models/Boat.php)
- Fault report model: [src/Models/Report.php](../src/Models/Report.php)

Important note:

- [sql/schema.sql](../sql/schema.sql) is not fully aligned with the live code expectations. Treat production DB as source of truth and use additive migrations only.

## 3. High-Level Design

Add a parallel check-in flow, not a modification of the current public fault form.

### Design principle

- Keep existing fault reporting endpoints and behavior unchanged.
- Introduce new check-in endpoints, tables, and bosun check-in report view.
- Reuse existing reports table for faults originating from check-in.

## 4. Proposed Data Model

## 4.1 New table: asset_checkins

Suggested columns:

- id (PK)
- asset_id (FK -> assets.id, or boats.id in current schema)
- user_name (varchar)
- user_email (varchar)
- checked_in_at (datetime, server-side timestamp)
- put_away_ok (boolean)
- safe_for_next_user (boolean)
- has_faults_to_rectify (boolean)
- damage_during_checkout (boolean, nullable)
- checkin_notes (text, nullable)
- fault_report_id (FK -> reports.id, nullable)
- created_at (timestamp)
- updated_at (timestamp)

Indexes:

- idx_asset_checkins_asset_date on (asset_id, checked_in_at)
- idx_asset_checkins_date on (checked_in_at)

## 4.2 Existing asset table extension

Add one asset-specific QR identifier:

- checkin_slug (varchar, unique)

Rationale:

- Enables stable QR URL per asset without exposing internal IDs.
- Current codebase compatibility: if retaining existing naming, add this to the `boats` table first.

## 4.3 Optional provenance extension: reports

Recommended optional columns:

- source enum('fault_form','asset_checkin') default 'fault_form'
- asset_checkin_id nullable FK -> asset_checkins.id

Rationale:

- Makes it easy to identify which faults came from check-in.

## 5. URL and Route Plan

Public routes (no login required):

- GET /checkin/{slug}
  - Show check-in form for one asset.
- POST /checkin/{slug}
  - Save check-in.
  - If safe_for_next_user = yes OR has_faults_to_rectify = yes, ask if damage happened during this checkout and collect fault description.
  - If fault description is provided under that condition, create row in reports.
  - Redirect to thank-you confirmation.

Bosun routes (authenticated):

- GET /bosun/checkins
  - Check-in history newest to oldest.
  - Filter by asset.

Route integration location:

- [public/index.php](../public/index.php)

## 6. UX Flow (Mobile First)

Single-page check-in flow is recommended:

1. QR opens asset-specific URL.
2. Form displays asset name and system date/time.
3. User enters name.
4. User enters contact email address.
5. User answers checklist:

- Is the asset put away as it was found?
- Is the asset in a safe condition for the next user?
- Does the asset have faults that should be rectified?

6. If Question 2 or Question 3 is Yes, reveal:

- Did the damage happen during this checkout?
- Fault description field

7. Submit.
8. Show thank-you message.

Alternative two-step flow can be done later, but single-page is lower risk and reduces drop-off.

## 7. Fault Creation Rules from Check-In

When Safe Condition = Yes OR Has Faults to Rectify = Yes:

- Create a normal fault report in existing reports table.
- Populate asset identifier and fault_description.
- Current schema compatibility: map asset identifier to `reports.boat_id` until table renaming is performed.
- reporter_name should use check-in user_name.
- reporter_email should use check-in contact email address.
- Set status default to New (same as existing process).
- If captured, record whether damage happened during this checkout.

When Safe Condition = No AND Has Faults to Rectify = No:

- No fault report is created.
- Only asset_checkins row is stored.

## 8. Bosun Reporting Requirements

New page: check-in history report

- Sort order: newest to oldest by checked_in_at.
- Filters:
  - Asset filter (specific asset)
  - Optional: has fault/no fault

Suggested columns:

- Date/time
- Asset name
- User name
- Contact email
- Put away as found (Yes/No)
- Safe condition for next user (Yes/No)
- Faults to be rectified (Yes/No)
- Damage during this checkout (Yes/No/Not asked)
- Fault report link (if generated)

## 9. Asset View Enhancement

Add Number of Uses to Asset Status view:

- Existing target page: [src/Views/bosun/boats.php](../src/Views/bosun/boats.php)
- Use count source: asset_checkins grouped by asset_id
- Current schema compatibility: if using `boat_id`, aggregate by `boat_id` until renamed.

Performance requirement:

- Avoid one query per asset for use count.
- Prefer a single aggregate query or join.

## 10. Security and Data Validation

- Server sets checked_in_at; do not trust client date.
- Validate slug to existing active asset.
- Validate required fields:
  - user_name
  - user_email (valid email format)
  - checklist booleans
  - damage_during_checkout and fault_description required only when safe_for_next_user = yes OR has_faults_to_rectify = yes
- Escape all displayed values.
- Add CSRF protection to POST route if session-based form token is available for public forms.
- Rate-limit public submissions if abuse becomes a risk.

## 11. Deployment Plan (cPanel + Existing DB)

Deployment approach:

1. Upload PHP/view/model changes to existing app on cPanel.
2. Apply additive SQL migration to existing DB (do not drop/replace existing tables).
3. Generate asset QR codes from checkin_slug URLs.
4. Pilot on a subset of assets.
5. Roll out to all assets.

Rollback:

- Disable /checkin routes in router.
- Keep schema (non-destructive rollback) unless cleanup is explicitly approved.

## 12. Migration Strategy

Create new migration files in repo for repeatability (examples):

- migrations/2026_04_14_001_add_asset_checkins.sql
- migrations/2026_04_14_002_add_asset_checkin_slug.sql
- migrations/2026_04_14_003_add_report_source_columns.sql (optional)

Guidelines:

- Only additive changes.
- Keep foreign keys nullable where linking can happen after insert sequence.
- Backfill checkin_slug for existing assets before printing QR labels.

## 13. Open Decisions

- Should retired assets still allow check-ins (normally no)?
- Should check-in faults trigger same email notification path as existing public fault form?
- Should check-in form include optional notes even when no fault?

## 14. Implementation Checklist (Future)

Phase 1: Database

- [ ] Add asset_checkins table.
- [ ] Add asset catalogue checkin_slug unique column (or boats.checkin_slug in current schema).
- [ ] (Optional) Add reports.source and reports.asset_checkin_id.

Phase 2: Public check-in app

- [ ] Add check-in GET/POST routes.
- [ ] Add controller methods for display + submit.
- [ ] Add check-in view and confirmation view.
- [ ] Add validation and error handling.

Phase 3: Fault integration

- [ ] On failed condition check, create standard report row.
- [ ] Link report to check-in (if optional columns used).

Phase 4: Bosun reporting

- [ ] Add /bosun/checkins route + page.
- [ ] Implement newest-first list with asset filter.
- [ ] Add links to related fault reports.

Phase 5: Asset list enhancement

- [ ] Add Number of Uses on asset status page.
- [ ] Ensure aggregate query strategy (no N+1 counts).

Phase 6: Deployment

- [ ] Apply SQL migrations on production DB.
- [ ] Deploy code to cPanel host.
- [ ] Generate and print QR labels.
- [ ] Run pilot and collect feedback.

## 15. Acceptance Criteria

- Scanning an asset QR opens that asset's check-in page.
- User can submit Name + checklist in under 30 seconds.
- Contact email address is captured and validated on check-in.
- Date is recorded automatically by server.
- If Safe Condition = Yes OR Faults to Rectify = Yes, follow-up questions are shown and fault is saved to existing reports table.
- Bosun can view check-ins newest-first and filter by asset.
- Asset status screen shows Number of Uses.
- Existing fault reporting route and bosun fault dashboard continue to work unchanged.
- Deployment works on current cPanel host and existing database.
