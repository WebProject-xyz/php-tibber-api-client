# PHP Tibber API Client & CLI

[![CI](https://github.com/WebProject-xyz/php-tibber-api-client/actions/workflows/ci.yml/badge.svg)](https://github.com/WebProject-xyz/php-tibber-api-client/actions/workflows/ci.yml)
[![Latest Stable Version](https://img.shields.io/packagist/v/webproject-xyz/php-tibber-api-client.svg)](https://packagist.org/packages/webproject-xyz/php-tibber-api-client)
[![PHP Version](https://img.shields.io/badge/php-%7E8.5.0-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Codeception](https://img.shields.io/badge/codeception-%5E5.3-red.svg)](https://codeception.com/)

> **Modern, type-safe PHP client, service, and CLI for the Tibber GraphQL API** built with decoupled Symfony 8.1 components (`symfony/http-client`, `symfony/serializer`, `symfony/console`, `symfony/dotenv`).

---

## 🚀 Key Features

- **Decoupled Symfony Components:** Built on `symfony/http-client` and `symfony/serializer` without requiring the heavy `symfony/framework-bundle`.
- **European 15-Minute Resolution Support:** Native support for both `HOURLY` and `QUARTER_HOURLY` price and consumption resolutions.
- **Typed Domain DTOs:** Immutable PHP 8.5 readonly classes (`Home`, `Price`, `PriceInfo`, `ConsumptionNode`, `Viewer`, etc.) and Backed Enums (`PriceResolution`, `PriceLevel`, `EnergyResolution`, `HeatingSource`, `HomeType`, `AppScreen`).
- **Automated Deserialization:** Robust denormalization using Symfony Serializer with custom formatters for RFC3339 `\DateTimeImmutable` and backed enums.
- **CLI Binary Included:** Installs directly to `vendor/bin/tibber` when required via Composer.
- **Strict Quality Standards:** 100% PHPStan Level 8 clean, PSR-12 / WebProject coding style, fully covered by Codeception unit tests.

---

## 📦 Installation

Install the package via Composer:

```bash
composer require webproject-xyz/php-tibber-api-client
```

---

## 🖥️ Configuration

Set your Tibber Personal Access Token (get one at [developer.tibber.com](https://developer.tibber.com/settings/accessToken)):

### Environment Variable (`.env` or `.env.local`)
```env
TIBBER_API_TOKEN=your_tibber_token_here
```

### CLI Option
All CLI commands accept an optional `--token` (or `-t`) flag:
```bash
vendor/bin/tibber <command> --token="your_tibber_token_here"
```

---

## 🛠️ CLI Usage

When installed as a Composer dependency, the binary is available under `vendor/bin/tibber` (or directly `./bin/tibber` inside this repository).

```bash
# View account details and registered homes
vendor/bin/tibber tibber:viewer

# Get current energy price (hourly or 15-minute resolution)
vendor/bin/tibber tibber:prices:current
vendor/bin/tibber tibber:prices:current --resolution=QUARTER_HOURLY

# List all price points for today
vendor/bin/tibber tibber:prices:today
vendor/bin/tibber tibber:prices:today --resolution=QUARTER_HOURLY

# List price points for tomorrow (published around 13:00 CET)
vendor/bin/tibber tibber:prices:tomorrow

# Inspect historical consumption (resolutions: HOURLY, DAILY, WEEKLY, MONTHLY, ANNUAL)
vendor/bin/tibber tibber:consumption --resolution=HOURLY --limit=24

# Send a push notification to your Tibber mobile app
vendor/bin/tibber tibber:push "Electricity Alert" "Energy prices are very cheap right now!" --screen=CONSUMPTION

# Dump the complete live GraphQL schema introspection to resources/schema.json
vendor/bin/tibber tibber:schema:dump
```

---

## 💻 Programmatic Usage

### 1. High-Level Service (`TibberService`)

```php
use WebProject\TibberApiClient\Client\TibberClient;
use WebProject\TibberApiClient\Service\TibberService;
use WebProject\TibberApiClient\Model\Enum\PriceResolution;

$client = new TibberClient($_ENV['TIBBER_API_TOKEN']);
$service = new TibberService($client);

// 1. Get Viewer & Homes
$viewer = $service->getViewer();
echo "Hello, " . $viewer->name . "\n";

foreach ($viewer->homes as $home) {
    echo "Home ID: " . $home->id . " in " . $home->address?->city . "\n";

    // 2. Fetch Current Price (15-min or hourly)
    $currentPrice = $service->getCurrentPrice($home->id, PriceResolution::QUARTER_HOURLY);
    echo sprintf(
        "Current price: %.4f %s (Level: %s)\n",
        $currentPrice->getTotalFloat(),
        $currentPrice->currency,
        $currentPrice->level?->value,
    );

    // 3. Today's Prices
    $todayPrices = $service->getTodaysPrices($home->id);
    foreach ($todayPrices as $price) {
        echo sprintf("%s: %.4f %s\n", $price->startsAt?->format('H:i'), $price->getTotalFloat(), $price->currency);
    }
}
```

### 2. Low-Level GraphQL Client (`TibberClient`)

```php
use WebProject\TibberApiClient\Client\TibberClient;

$client = new TibberClient($_ENV['TIBBER_API_TOKEN']);
$data = $client->query('query { viewer { login name } }');

var_dump($data);
```

---

## 📋 Roadmap & TODO

The following synchronous (and upcoming asynchronous) features are planned for future releases:

### Synchronous Features (HTTP API)
- [x] **Viewer & Homes Query:** Complete account, home, address, owner, and subscription data.
- [x] **Energy Prices:** Current, today, tomorrow with `HOURLY` and `QUARTER_HOURLY` resolution.
- [x] **Consumption:** Historical consumption retrieval across all supported resolutions.
- [x] **Push Notifications:** Send push notifications with target screen routing.
- [x] **Update Home Mutation:** Support in `TibberService::updateHome()`.
- [ ] **CLI Command `tibber:home:update`:** Interactive CLI command for updating home nickname, type, heating source, and fuse size.
- [ ] **Solar Production / Feed-in API:** Add `getProduction()` to `TibberService` to fetch historical feed-in data for solar installations (`production(resolution: ..., last: ...)`).
- [ ] **CLI Command `tibber:production`:** Display historical solar feed-in data in the terminal.
- [ ] **Lightweight Symfony Bundle (`TibberBundle`):** Optional bundle extension for zero-config autowiring in full-stack Symfony applications.

### Asynchronous Features (WebSocket Live Stream)
- [ ] **Real-Time Live Telemetry (`TibberFeed`):** WebSocket client connecting to `wss://websocket-api.tibber.com/v1-beta/gql/subscriptions` via `graphql-transport-ws` protocol for second-by-second Tibber Pulse power readings.

---

## 🧪 Development & Quality Assurance

```bash
composer qa        # Runs test:build, cs:fix, test, and stan
composer stan      # Run static analysis (PHPStan Level 8)
composer test      # Run Codeception unit tests
composer cs:check  # Dry-run coding standard checks
composer cs:fix    # Automatically fix coding standards
```

---

## 📜 License

Distributed under the **MIT** License. See `LICENSE` for more information.

---

## ✉️ Support & Contact

- **Website:** [webproject.xyz](https://www.webproject.xyz)
- **Author:** Benjamin Fahl
