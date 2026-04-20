# Boat Check-In Extension Specification

Status: Draft (Planning only, no implementation)
Last updated: 2026-04-20

## 1. Purpose

Add a boat check-in workflow after each use, while preserving the existing fault reporting workflow.

### Goals

- Users open a single shared check-in page.
- The check-in form should follow the same visual presentation style as the existing fault reporting flow, with a common look and feel.
- User submits:
  - Name
  - Contact email address
  - Asset name (selected from list)
  - Date (auto-populated by system)
  - Checklist answers:
    - Has the boat been put away properly and any radios, safety box, sails and foils back in the correct place?
    - Is the boat in a safe / working condition for the next user?
    - Does the boat have faults that should be rectified?
- If Question 2 or Question 3 is Yes, ask if the damage happened during this checkout and capture fault description for the existing fault reports table.
- Add a bosun report view for check-in history (newest first, optionally filter by boat).
- Add Number of Uses to the boat status view.
- Deploy to the same cPanel host and same existing database.

### Non-goals

- Replacing or redesigning the current fault reporting flow.
- Moving to a new hosting platform.
- Rebuilding authentication or user roles.

## 2. Existing System Constraints

The current application already separates public and bosun workflows:

- Public route handling and entry point: [public/index.php](../public/index.php)
- Public fault controller: [src/Controllers/PublicController.php](../src/Controllers/PublicController.php)
- Bosun fault/boat controller: [src/Controllers/BosunController.php](../src/Controllers/BosunController.php)
- Boat model: [src/Models/Boat.php](../src/Models/Boat.php)
- Fault report model: [src/Models/Report.php](../src/Models/Report.php)

Important note:

- [sql/schema.sql](../sql/schema.sql) is not fully aligned with the live code expectations. Treat production DB as source of truth and use additive migrations only.

## 3. High-Level Design

Add a parallel check-in flow, not a modification of the current public fault form.

### Design principle

- Keep existing fault reporting endpoints and behavior unchanged.
- Introduce new check-in endpoints, tables, and bosun check-in report view.
- Reuse existing reports table for faults originating from check-in.
- Reuse the existing public form styling patterns, layout structure, and overall visual language so the check-in flow feels like part of the same application.

## 4. Proposed Data Model

## 4.1 New table: boat_checkins

Suggested columns:

- id (PK)
- boat_id (FK -> boats.id)
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

- idx_boat_checkins_boat_date on (boat_id, checked_in_at)
- idx_boat_checkins_date on (checked_in_at)

## 4.2 Existing boat table extension

No new boat identifier is required.

Rationale:

- The agreed workflow uses one shared check-in entry point.
- User selects the asset from the existing boat list instead of arriving with boat context from a QR code or slug.
- Avoids creating, printing, replacing, and tracking one label per boat.

## 4.3 Optional provenance extension: reports

Recommended optional columns:

- source enum('fault_form','boat_checkin') default 'fault_form'
- boat_checkin_id nullable FK -> boat_checkins.id

Rationale:

- Makes it easy to identify which faults came from boat check-in.

## 5. URL and Route Plan

Public routes (no login required):

- GET /checkin
  - Show check-in form with asset selector.
- POST /checkin
  - Save check-in.
  - If safe_for_next_user = yes OR has_faults_to_rectify = yes, ask if damage happened during this checkout and collect fault description.
  - If fault description is provided under that condition, create row in reports.
  - Redirect to thank-you confirmation.

Bosun routes (authenticated):

- GET /bosun/checkins
  - Check-in history newest to oldest.
  - Filter by boat.

Route integration location:

- [public/index.php](../public/index.php)

## 6. UX Flow (Mobile First)

Single-page check-in flow is recommended:

Presentation requirements:

- Match the current public fault reporting page layout, spacing, typography, button treatment, and general form structure.
- Reuse existing CSS and shared UI patterns where practical rather than creating a visually separate workflow.
- Keep the mobile-first behavior and interaction style consistent with the fault reporting form.

1. User opens the shared check-in page.
2. User selects the asset from the list.
3. Form displays system date/time.
4. User enters name.
5. User enters contact email address.
6. User answers checklist:

- Has the boat been put away properly and any radios, safety box, sails and foils back in the correct place
- Is the boat in a safe condition for the next user?
- Does the boat have faults that should be rectified?

7. If Question 2 or Question 3 is Yes, reveal:

- Did the damage happen during this checkout?
- Fault description field

8. Submit.
9. Show thank-you message.

Alternative two-step flow can be done later, but single-page is lower risk and reduces drop-off.

## 7. Fault Creation Rules from Check-In

When Safe Condition = Yes OR Has Faults to Rectify = Yes:

- Create a normal fault report in existing reports table.
- Populate boat identifier and fault_description.
- Map boat identifier to `reports.boat_id`.
- reporter_name should use check-in user_name.
- reporter_email should use check-in contact email address.
- Set status default to New (same as existing process).
- If captured, record whether damage happened during this checkout.

When Safe Condition = No AND Has Faults to Rectify = No:

- No fault report is created.
- Only boat_checkins row is stored.

## 8. Bosun Reporting Requirements

New page: check-in history report

- Sort order: newest to oldest by checked_in_at.
- Filters:
  - Boat filter (specific boat)
  - Optional: has fault/no fault

Suggested columns:

- Date/time
- Boat name
- User name
- Contact email
- Put away as found (Yes/No)
- Safe condition for next user (Yes/No)
- Faults to be rectified (Yes/No)
- Damage during this checkout (Yes/No/Not asked)
- Fault report link (if generated)

## 9. Boat View Enhancement

Add Number of Uses to Boat Status view:

- Existing target page: [src/Views/bosun/boats.php](../src/Views/bosun/boats.php)
- Use count source: boat_checkins grouped by boat_id

Performance requirement:

- Avoid one query per boat for use count.
- Prefer a single aggregate query or join.

## 10. Security and Data Validation

- Server sets checked_in_at; do not trust client date.
- Validate submitted boat_id to an existing active boat.
- Validate required fields:
  - boat_id
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
3. Pilot on a subset of boats.
4. Roll out to all boats.

Rollback:

- Disable /checkin routes in router.
- Keep schema (non-destructive rollback) unless cleanup is explicitly approved.

## 12. Migration Strategy

Create new migration files in repo for repeatability (examples):

- migrations/2026_04_14_001_add_boat_checkins.sql
- migrations/2026_04_14_002_add_report_source_columns.sql (optional)

Guidelines:

- Only additive changes.
- Keep foreign keys nullable where linking can happen after insert sequence.
- Reuse existing boats table for asset selection; no slug backfill or label generation needed.

## 13. Confirmed Decisions

- Retired boats should not be offered in the public check-in asset selector.
- Faults created from check-in should trigger the same email notification path as the existing public fault form.
- Check-in submissions with no faults do not require optional notes at this time.

## 14. Implementation Checklist (Future)

Phase 1: Database

- [ ] Add boat_checkins table.
- [ ] (Optional) Add reports.source and reports.boat_checkin_id.

Phase 2: Public check-in app

- [ ] Add check-in GET/POST routes.
- [ ] Add controller methods for display + submit.
- [ ] Add check-in view and confirmation view with asset selector.
- [ ] Add validation and error handling.

Phase 3: Fault integration

- [ ] On failed condition check, create standard report row.
- [ ] Link report to check-in (if optional columns used).

Phase 4: Bosun reporting

- [ ] Add /bosun/checkins route + page.
- [ ] Implement newest-first list with boat filter.
- [ ] Add links to related fault reports.

Phase 5: Boat list enhancement

- [ ] Add Number of Uses on boat status page.
- [ ] Ensure aggregate query strategy (no N+1 counts).

Phase 6: Deployment

- [ ] Apply SQL migrations on production DB.
- [ ] Deploy code to cPanel host.
- [ ] Run pilot and collect feedback.

## 15. Acceptance Criteria

- User can open one shared check-in page and choose the asset from the list.
- User can submit Name + checklist in under 30 seconds.
- Contact email address is captured and validated on check-in.
- Date is recorded automatically by server.
- If Safe Condition = Yes OR Faults to Rectify = Yes, follow-up questions are shown and fault is saved to existing reports table.
- Bosun can view check-ins newest-first and filter by boat.
- Boat status screen shows Number of Uses.
- Existing fault reporting route and bosun fault dashboard continue to work unchanged.
- Deployment works on current cPanel host and existing database.
