# E2E Test Infra: Assin.art v1 Backend

## Test Philosophy
- Opaque-box, requirement-driven.
- Derived directly from `ORIGINAL_REQUEST.md`, `tasks_backend.md`, and frontend contracts in `assinart_v1`.
- Methodology: Category-Partition + Boundary Value Analysis + Pairwise Combinatorial + Real-World Workload Testing.

## Feature Inventory
| # | Feature | Source (Requirement) | Tier 1 | Tier 2 | Tier 3 | Tier 4 |
|---|---------|---------------------|:------:|:------:|:------:|:------:|
| 1 | Username Check | ORIGINAL_REQUEST §R1 | 5 | 5 | ✓ | ✓ |
| 2 | Auth Register | ORIGINAL_REQUEST §R1 | 5 | 5 | ✓ | ✓ |
| 3 | Auth Login (Rate-Limited) | ORIGINAL_REQUEST §R1 | 5 | 5 | ✓ | ✓ |
| 4 | Auth Session & Logout | ORIGINAL_REQUEST §R1 | 5 | 3 | ✓ | ✓ |
| 5 | Password Reset & Email Verify | ORIGINAL_REQUEST §R1 | 5 | 5 | ✓ | ✓ |
| 6 | Onboarding Step 1 (Links & Bio) | ORIGINAL_REQUEST §R2 | 5 | 5 | ✓ | ✓ |
| 7 | Onboarding Step 2 (Background) | ORIGINAL_REQUEST §R2 | 5 | 5 | ✓ | ✓ |
| 8 | Onboarding Step 3 (Buttons & Publish) | ORIGINAL_REQUEST §R2 | 5 | 5 | ✓ | ✓ |
| 9 | Media Uploads & Orphan Cleanup | ORIGINAL_REQUEST §R3 | 5 | 5 | ✓ | ✓ |
| 10 | Buttons CRUD & Ownership | ORIGINAL_REQUEST §R4 | 5 | 5 | ✓ | ✓ |
| 11 | Buttons Reorder (Drag & Drop) | ORIGINAL_REQUEST §R4 | 5 | 5 | ✓ | ✓ |
| 12 | Public Profile 3-State Resolution | ORIGINAL_REQUEST §R5 | 5 | 5 | ✓ | ✓ |
| 13 | Click Tracker Atomic 302 Redirect | ORIGINAL_REQUEST §R5 | 5 | 5 | ✓ | ✓ |
| 14 | CORS & Security Headers | ORIGINAL_REQUEST §R6 | 5 | 3 | ✓ | ✓ |

## Test Architecture
- Test runner: `phpunit` / `php artisan test`
- Location: `tests/Feature/`
- Directory layout:
  - `tests/TestCase.php`: Base test case initializing Laravel application & in-memory SQLite database
  - `tests/Feature/UsernameCheckTest.php`
  - `tests/Feature/AuthTest.php`
  - `tests/Feature/OnboardingTest.php`
  - `tests/Feature/MediaStorageTest.php`
  - `tests/Feature/ButtonTest.php`
  - `tests/Feature/PublicProfileTest.php`
  - `tests/Feature/RedirectTest.php`
  - `tests/Feature/CorsSecurityTest.php`

## Real-World Application Scenarios (Tier 4)
| # | Scenario | Features Exercised | Complexity |
|---|----------|--------------------|------------|
| 1 | Complete Artist Onboarding Journey: Check @username -> Register -> Step 1 (Bio & 3 Links) -> Step 2 (Color & Pattern) -> Step 3 (Shape & Publish) -> Verify Public Profile | F1, F2, F6, F7, F8, F12 | High |
| 2 | Visual Identity Update: Upload avatar, upload cover, change button colors, verify cascade in public profile | F9, F10, F12 | Medium |
| 3 | Link Management: Add standard, pill, and card buttons, reorder via Drag & Drop, delete one, verify updated ordering in public view | F10, F11, F12 | High |
| 4 | Click Tracking & Analytics: Visit public profile, follow redirect `/r/{id}`, verify atomic increment of `clicks_count` | F12, F13 | Medium |
| 5 | Security & Anti-Abuse: Brute force login triggering 429, tampering with other artist's button ID in reorder | F3, F11 | High |

## Coverage Thresholds
- Tier 1 (Feature Coverage): ≥5 per feature
- Tier 2 (Boundary & Corner Cases): ≥5 per feature
- Tier 3 (Cross-Feature Combinations): Pairwise integration of Auth, Onboarding, Buttons, Profile, Redirect
- Tier 4 (Workloads): ≥5 end-to-end scenarios
- Expected pass rate: 100% of tests passing with exit code 0
