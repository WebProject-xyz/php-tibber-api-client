<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Tests\Unit;

use Codeception\Test\Unit;
use WebProject\TibberApiClient\Client\TibberClientInterface;
use WebProject\TibberApiClient\Model\Enum\AppScreen;
use WebProject\TibberApiClient\Model\Enum\EnergyResolution;
use WebProject\TibberApiClient\Model\Enum\PriceResolution;
use WebProject\TibberApiClient\Model\UpdateHomeInput;
use WebProject\TibberApiClient\Service\TibberService;

class TibberServiceTest extends Unit
{
    public function testGetViewerAndHomes(): void
    {
        $mockClient = $this->createMock(TibberClientInterface::class);
        $mockClient->expects(self::once())
            ->method('query')
            ->willReturn([
                'viewer' => [
                    'name'  => 'Spock',
                    'login' => 'spock@starfleet.org',
                    'homes' => [
                        ['id' => 'vulcan-1', 'timeZone' => 'Europe/Berlin'],
                    ],
                ],
            ]);

        $service = new TibberService($mockClient);
        $viewer  = $service->getViewer();

        self::assertSame('Spock', $viewer->name);
        self::assertCount(1, $viewer->homes);
        self::assertSame('vulcan-1', $viewer->homes[0]->id);
    }

    public function testGetHomeById(): void
    {
        $mockClient = $this->createMock(TibberClientInterface::class);
        $mockClient->expects(self::once())
            ->method('query')
            ->with(self::callback('is_string'), ['homeId' => 'home-123'])
            ->willReturn([
                'viewer' => [
                    'home' => ['id' => 'home-123', 'size' => 100],
                ],
            ]);

        $service = new TibberService($mockClient);
        $home    = $service->getHome('home-123');

        self::assertNotNull($home);
        self::assertSame('home-123', $home->id);
        self::assertSame(100, $home->size);
    }

    public function testGetCurrentAndTodayPrices(): void
    {
        $mockClient = $this->createMock(TibberClientInterface::class);
        $mockClient->expects(self::exactly(2))
            ->method('query')
            ->willReturn([
                'viewer' => [
                    'home' => [
                        'currentSubscription' => [
                            'priceInfo' => [
                                'current' => ['total' => 0.35, 'currency' => 'EUR'],
                                'today'   => [
                                    ['total' => 0.30, 'currency' => 'EUR'],
                                    ['total' => 0.35, 'currency' => 'EUR'],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $service = new TibberService($mockClient);
        $current = $service->getCurrentPrice('home-123', PriceResolution::HOURLY);
        $today   = $service->getTodaysPrices('home-123', PriceResolution::HOURLY);

        self::assertNotNull($current);
        self::assertSame(0.35, $current->getTotalFloat());
        self::assertCount(2, $today);
    }

    public function testGetConsumption(): void
    {
        $mockClient = $this->createMock(TibberClientInterface::class);
        $mockClient->expects(self::once())
            ->method('query')
            ->with(self::callback('is_string'), [
                'homeId'     => 'home-123',
                'resolution' => 'HOURLY',
                'lastCount'  => 2,
            ])
            ->willReturn([
                'viewer' => [
                    'home' => [
                        'consumption' => [
                            'nodes' => [
                                ['consumption' => 0.5, 'consumptionUnit' => 'kWh'],
                                ['consumption' => 0.8, 'consumptionUnit' => 'kWh'],
                            ],
                        ],
                    ],
                ],
            ]);

        $service = new TibberService($mockClient);
        $nodes   = $service->getConsumption('home-123', EnergyResolution::HOURLY, 2);

        self::assertCount(2, $nodes);
        self::assertSame(0.5, $nodes[0]->getConsumptionFloat());
    }

    public function testSendPushNotification(): void
    {
        $mockClient = $this->createMock(TibberClientInterface::class);
        $mockClient->expects(self::once())
            ->method('query')
            ->with(self::callback('is_string'), [
                'input' => [
                    'title'        => 'Alert',
                    'message'      => 'Energy price cheap now',
                    'screenToOpen' => 'CONSUMPTION',
                ],
            ])
            ->willReturn([
                'sendPushNotification' => [
                    'successful'              => true,
                    'pushedToNumberOfDevices' => 2,
                ],
            ]);

        $service = new TibberService($mockClient);
        $result  = $service->sendPushNotification('Alert', 'Energy price cheap now', AppScreen::CONSUMPTION);

        self::assertTrue($result->successful);
        self::assertSame(2, $result->pushedToNumberOfDevices);
    }

    public function testUpdateHome(): void
    {
        $mockClient = $this->createMock(TibberClientInterface::class);
        $mockClient->expects(self::once())
            ->method('query')
            ->with(self::callback('is_string'), [
                'input' => [
                    'homeId'      => 'home-123',
                    'appNickname' => 'Enterprise HQ',
                ],
            ])
            ->willReturn([
                'updateHome' => [
                    'id'          => 'home-123',
                    'appNickname' => 'Enterprise HQ',
                ],
            ]);

        $service = new TibberService($mockClient);
        $updated = $service->updateHome(new UpdateHomeInput(homeId: 'home-123', appNickname: 'Enterprise HQ'));

        self::assertNotNull($updated);
        self::assertSame('Enterprise HQ', $updated->appNickname);
    }
}
