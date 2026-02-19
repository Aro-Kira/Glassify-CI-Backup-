# Changelog — Major Changes (start → finish)

Date: 2026-02-08

This document summarizes the major work completed across the codebase during the recent sessions (focused on appointment admin UX, dynamic product customization + live 2D preview, and PayMongo payment flow + tracking).

---

## 1) Admin appointments — Role column fix
- Root problem: `Role` column displayed generic `Customer` instead of the customer's profile role (`beginner` / `professional`).
- Change: `application/controllers/AdminCon.php`
  - Selected `c.role as CustomerRole` when available and prefer it when formatting appointment rows.
  - Normalized display to `Beginner` / `Professional` (capitalized) and fallback to `Customer`.
- Effect: List and modal now surface the correct customer experience role.

## 2) Product-specific dynamic specifications + live 2D preview (Admin modal)
- Goal: In the Ocular / Site Assessment modal, show editable, product-specific customization fields and a live Konva-based 2D preview that updates as specs change.
- Frontend changes:
  - `application/views/admin_page/admin_appointment.php`
    - Reworked Order Specifications into a two-column layout: left Konva container (`#admin-konva-container`) and right dynamic specs container (`#admin-dynamic-specs-container`) plus a static fallback.
    - Included `konva.min.js`, comprehensive renderer scripts and `windows_visual_configs.js` when viewing ocular appointments.
  - `assets/js/admin-js/appointment-management.js`
    - Added `loadProductCustomizationForAppointment(apt)`, `renderAdminDynamicFields()`, `initAdminKonvaPreview()`, and `updateAdminKonvaPreview()`.
    - Populates dynamic fields from the backend endpoint and wires change handlers to re-render the Konva preview live.
    - Save handler extended to collect `dynamic_customization` JSON and include it in appointment update requests.
    - Added bootstrap that populates `window.glassStyles` / `window.frameStyles` from `windowsVisualConfigs` to support the renderer on admin pages.
- Backend changes:
  - `application/controllers/AdminCon.php`
    - New endpoint `get_product_customization_data` (returns product field config, tag prices, tag visual configs, and prior customer selections) used by the admin modal.
    - Updated save logic to accept and merge `dynamic_customization` JSON into `order_items.Customization` when provided.
- Renderer integration:
  - Integrated Comprehensive 2D renderer with admin Konva stage; `Comprehensive2DRenderer.renderProduct2D(...)` called from admin JS.
  - Added defensive fallbacks to ensure the admin preview doesn't fail silently (fallback static image / fallback Konva rectangle on errors).

## 3) Comprehensive 2D renderer hardening
- File: `assets/js/2d-functions/comprehensive_2d_renderer.js`
  - Fixed fragile lookups in `getGlassStyle()` and `getFrameStyle()` so they never return `undefined` (added additional lookups against `windowsVisualConfigs` and safe defaults).
  - Ensures renderer code uses valid `{ fill, opacity }` and `{ color, width }` defaults to avoid uncaught TypeErrors during rendering.

## 4) PayMongo / payment flow changes (checkout & stage payments)
- Goal: Route Place Order and stage payments (downpayment / fabrication / installation) through PayMongo and save PayMongo transaction IDs for tracking.
- Frontend changes:
  - `application/views/shop/checkout.php`
    - Added `initiateStagePayMongoPayment()` and updated stage-payment flows to create staged intents and redirect/attach to PayMongo for completion.
- Backend changes:
  - `application/controllers/ShopCon.php`
    - `create_stage_payment_intent()` endpoint added to create PayMongo intents for stage payments.
    - `attach_payment_method()` updated to accept optional `stage` parameter and route to stage-completion helper.
    - `_complete_stage_payment()` helper added to mark stage payments as completed and store transaction IDs in orders.
    - `payment_complete()` modified to correctly consume stage parameter on return from PayMongo.
- Track order UI:
  - `application/views/shop/order_tracking.php` updated to display Payment ID column in the Payment Breakdown for each stage.

## 5) Database schema changes
- Added columns to store stage payment transaction IDs on the `order` table:
  - `FabricationTransactionID`, `InstallationTransactionID` (and ensured fields exist to persist downpayment IDs as well).
- Relaxed some `ENUM` fields to `VARCHAR` where code relied on additional statuses. (Direct ALTERs/migration scripts executed.)
- Created `database/migrations/add_transaction_id_columns.sql` for tracking.

## 6) Files created / modified (high-level)
- Created / edited key files:
  - application/controllers/AdminCon.php — endpoints + Role handling + save merge logic
  - application/views/admin_page/admin_appointment.php — modal layout with Konva and dynamic specs
  - assets/js/admin-js/appointment-management.js — admin modal JS + Konva integration + dynamic specs
  - assets/js/2d-functions/comprehensive_2d_renderer.js — renderer hardening + exposure of Comprehensive2DRenderer
  - assets/js/2d-functions/comprehensive_renderer_integration.js — integration helpers
  - assets/js/2d-functions/windows_visual_configs.js — visual style configs (loaded by admin views)
  - assets/js/admin-js/* (other small changes)
  - application/controllers/ShopCon.php — PayMongo stage intent + attach/complete helpers
  - application/views/shop/checkout.php — frontend PayMongo changes
  - application/views/shop/order_tracking.php — Payment ID display
  - database/migrations/add_transaction_id_columns.sql

## 7) Bug fixes & hardening
- Fixed renderer crash when visual styles were missing by:
  - Bootstrapping `window.glassStyles` / `window.frameStyles` from `windowsVisualConfigs` on admin pages.
  - Making `getGlassStyle()` / `getFrameStyle()` robust and guaranteed to return fallback objects.
  - Added catch/fallback drawing in `updateAdminKonvaPreview()` so user sees a placeholder instead of a blank/errored canvas.

## 8) Testing & next steps (recommended)
- Required manual tests:
  - Admin appointment modal: Verify dynamic fields load for products, edits persist, and Konva preview updates on field changes.
  - PayMongo sandbox: End-to-end tests for normal order payments and stage payments (downpayment, fabrication, installation); ensure transaction IDs are persisted and shown in Track Order.
  - Verify no regressions in other pages that use the 2D renderer (modeling page, product previews).
- Suggested follow-ups:
  - Add unit/integration tests around `get_product_customization_data` endpoint and admin save flow.
  - Add a lightweight UI test that exercises the Konva preview update on spec changes.

---

If you want, I can:
- Commit and push these changes to a branch and create a PR message draft.
- Run a quick smoke test script (if you want me to run PHP sanity checks or a browser-based smoke test, tell me which environment to use).

