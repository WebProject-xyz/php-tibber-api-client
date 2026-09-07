<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Service;

use WebProject\TibberApiClient\Model\ConsumptionNode;
use WebProject\TibberApiClient\Model\Enum\AppScreen;
use WebProject\TibberApiClient\Model\Enum\EnergyResolution;
use WebProject\TibberApiClient\Model\Enum\PriceResolution;
use WebProject\TibberApiClient\Model\Home;
use WebProject\TibberApiClient\Model\Price;
use WebProject\TibberApiClient\Model\PriceInfo;
use WebProject\TibberApiClient\Model\SendPushNotificationResult;
use WebProject\TibberApiClient\Model\UpdateHomeInput;
use WebProject\TibberApiClient\Model\Viewer;

interface TibberServiceInterface
{
    /**
     * Fetch complete viewer account information including registered homes.
     */
    public function getViewer(): Viewer;

    /**
     * Fetch all homes registered to the authenticated viewer.
     *
     * @return array<Home>
     */
    public function getHomes(): array;

    /**
     * Fetch a specific home by its unique ID.
     */
    public function getHome(string $homeId): ?Home;

    /**
     * Fetch price info (current, today, tomorrow) for a specific home.
     */
    public function getPriceInfo(string $homeId, PriceResolution $resolution = PriceResolution::HOURLY): ?PriceInfo;

    /**
     * Fetch current energy price for a specific home.
     */
    public function getCurrentPrice(string $homeId, PriceResolution $resolution = PriceResolution::HOURLY): ?Price;

    /**
     * Fetch today's energy prices for a specific home.
     *
     * @return array<Price>
     */
    public function getTodaysPrices(string $homeId, PriceResolution $resolution = PriceResolution::HOURLY): array;

    /**
     * Fetch tomorrow's energy prices for a specific home (usually available after 13:00 CET).
     *
     * @return array<Price>
     */
    public function getTomorrowsPrices(string $homeId, PriceResolution $resolution = PriceResolution::HOURLY): array;

    /**
     * Fetch historical energy consumption nodes for a specific home.
     *
     * @return array<ConsumptionNode>
     */
    public function getConsumption(string $homeId, EnergyResolution $resolution, int $lastCount): array;

    /**
     * Send a push notification to the authenticated user's Tibber mobile app.
     */
    public function sendPushNotification(string $title, string $message, ?AppScreen $screen = null): SendPushNotificationResult;

    /**
     * Update configuration for a specific home.
     */
    public function updateHome(UpdateHomeInput $input): ?Home;
}
