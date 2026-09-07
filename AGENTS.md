# AI Agent Guidelines: php-tibber-api-client

This document serves as the authoritative operational guide for AI coding assistants working in the `webproject-xyz/php-tibber-api-client` repository.

---

## 1. Project Overview & Boundaries

- **Name:** `webproject-xyz/php-tibber-api-client`
- **Type:** Standalone Composer Library (`"type": "library"`)
- **PHP Version:** `~8.5.0`
- **Namespace:** `WebProject\TibberApiClient\` (mapped to `src/`)
- **Tests Namespace:** `WebProject\TibberApiClient\Tests\` (mapped to `tests/`)
- **Primary Goal:** Modern, type-safe PHP client, service layer, DTO domain models, and CLI tool for the Tibber GraphQL API (`https://api.tibber.com/v1-beta/gql`).

---

## 2. Architecture & Tech Stack

### Decoupled Symfony 8.1 Components
This is a lightweight library, **not** a full-stack Symfony application:
- **HTTP Client:** `symfony/http-client` (with Bearer token authentication).
- **Serializer:** `symfony/serializer`, `symfony/property-access`, `symfony/property-info`, `phpdocumentor/reflection-docblock`.
- **Console / CLI:** `symfony/console`.
- **Environment:** `symfony/dotenv`.
- **Rule:** **Never** add `symfony/framework-bundle` as a package dependency.

### Single Source of Schema Truth
- The authoritative GraphQL schema definition is [resources/schema.json](resources/schema.json), introspected directly from `https://api.tibber.com/v1-beta/gql`.
- **Never** rely on third-party web documentation or outdated packages when schema discrepancies arise. The live GraphQL schema takes precedence over all documentation.
- Update the schema via `vendor/bin/tibber tibber:schema:dump`.

### Synchronous vs. Asynchronous Scope
- **Current Scope:** Strictly synchronous GraphQL HTTP operations (viewer, homes, prices, consumption, push notifications, home mutations).
- **Asynchronous / WebSockets:** Live telemetry streaming (`TibberFeed` over `graphql-transport-ws`) is deferred to future releases and must not be mixed into synchronous HTTP client classes.

---

## 3. Coding Conventions & Standards

### Type Safety & PHPStan (Level 8)
- Every PHP file must begin with `declare(strict_types=1);`.
- **Zero Suppression Policy:** Never use `@phpstan-ignore`, `@phpstan-ignore-next-line`, or baseline files. Type mismatches must always be solved at their root cause.
- The `Serializer` instance in services must be typed as `Symfony\Component\Serializer\Normalizer\DenormalizerInterface&Symfony\Component\Serializer\SerializerInterface` because `denormalize()` is defined on `DenormalizerInterface`.

### Models & DTOs
- Models reside in `src/Model/` and must be declared as `final readonly class`.
- **Numeric Fields:** JSON APIs return integers for zero values (e.g. `0` instead of `0.0`). Type numeric fields in DTOs as `float|int|null` to avoid `NotNormalizableValueException` in Symfony Serializer. Provide helper methods returning `?float` (e.g., `getTotalFloat()`).
- **Dates:** Always represent timestamp attributes as `?\DateTimeImmutable` formatted with `\DateTimeInterface::RFC3339`.
- **Enums:** Use String Backed Enums in `src/Model/Enum/` (`PriceResolution`, `PriceLevel`, `PriceRatingLevel`, `EnergyResolution`, `HomeType`, `HeatingSource`, `AppScreen`).
- **Nullability:** In GraphQL, fields are nullable by default unless marked `NON_NULL` (`!`). DTO properties must reflect schema nullability with `= null` default values to support partial GraphQL sub-selection queries.

### Console Commands & Execution Rules
- Console commands extend `src/Command/AbstractTibberCommand.php`.
- Support token resolution from `--token` (`-t`) option, falling back to `TIBBER_API_TOKEN` in `.env.local` / `.env`.
- In commands, use deterministic `instanceof` checks rather than nullsafe property chains on enums or dates to keep PHPStan analysis clean.
- **Critical Execution Rule:** Never run blind multi-line inline scripts (`php -r '...'`). The UI truncates multi-line bash commands into blind permission dialogs. Always execute standard CLI commands (`bin/tibber ...`), composer scripts, or write explicit test scripts.

### Security
- Never commit `.env` or `.env.local` containing live API tokens.
- Keep `.gitignore` updated (`.env*`, `!.env.example`). Provide mock tokens in tests.

---

## 4. CLI Binary (`bin/tibber`)

The executable [bin/tibber](bin/tibber) is registered in `composer.json` under `"bin": ["bin/tibber"]` and installs to `vendor/bin/tibber`.

Available commands:
| Command | Description |
| :--- | :--- |
| `tibber:viewer` | Fetch viewer account details and registered homes table. |
| `tibber:prices:current` | Fetch current energy price (`--resolution=HOURLY\|QUARTER_HOURLY`). |
| `tibber:prices:today` | Display today's hourly or 15-minute price curve. |
| `tibber:prices:tomorrow` | Display tomorrow's price curve (published ~13:00 CET). |
| `tibber:consumption` | Historical consumption data (`--resolution=...`, `--limit=...`). |
| `tibber:push` | Send push notification to Tibber app (`<title> <message> [--screen=...]`). |
| `tibber:schema:dump` | Introspect live GraphQL schema and update `resources/schema.json`. |

---

## 5. Quality Assurance & Verification Commands

All changes must pass the full QA pipeline before completion:

```bash
composer qa        # Master gate: test:build -> cs:fix -> test -> stan
composer stan      # Run PHPStan Level 8 static analysis
composer test      # Run Codeception 5 unit test suite
composer cs:check  # Check PSR-12 / WebProject coding standards
composer cs:fix    # Automatically fix coding standard violations
```
