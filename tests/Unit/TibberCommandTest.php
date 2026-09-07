<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Tests\Unit;

use Codeception\Test\Unit;
use Symfony\Component\Console\Tester\CommandTester;
use WebProject\TibberApiClient\Command\ConsumptionCommand;
use WebProject\TibberApiClient\Command\CurrentPriceCommand;
use WebProject\TibberApiClient\Command\PushNotificationCommand;
use WebProject\TibberApiClient\Command\TodayPricesCommand;
use WebProject\TibberApiClient\Command\TomorrowPricesCommand;
use WebProject\TibberApiClient\Command\ViewerCommand;
use WebProject\TibberApiClient\Model\ConsumptionNode;
use WebProject\TibberApiClient\Model\Enum\PriceLevel;
use WebProject\TibberApiClient\Model\Home;
use WebProject\TibberApiClient\Model\Price;
use WebProject\TibberApiClient\Model\SendPushNotificationResult;
use WebProject\TibberApiClient\Model\Viewer;
use WebProject\TibberApiClient\Service\TibberServiceInterface;

class TibberCommandTest extends Unit
{
    public function testViewerCommand(): void
    {
        $mockService = $this->createMock(TibberServiceInterface::class);
        $mockService->expects(self::once())
            ->method('getViewer')
            ->willReturn(new Viewer(
                login: 'spock@starfleet.org',
                userId: 'vulcan-id',
                name: 'Mr. Spock',
                homes: [
                    new Home(id: 'home-42', timeZone: 'Europe/Berlin', size: 90),
                ],
            ));

        $command  = new ViewerCommand($mockService);
        $tester   = new CommandTester($command);
        $exitCode = $tester->execute([]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('Mr. Spock', $tester->getDisplay());
        self::assertStringContainsString('home-42', $tester->getDisplay());
    }

    public function testCurrentPriceCommand(): void
    {
        $mockService = $this->createMock(TibberServiceInterface::class);
        $mockService->expects(self::once())
            ->method('getCurrentPrice')
            ->willReturn(new Price(
                total: 0.385,
                energy: 0.15,
                tax: 0.235,
                currency: 'EUR',
                level: PriceLevel::NORMAL,
            ));

        $command  = new CurrentPriceCommand($mockService);
        $tester   = new CommandTester($command);
        $exitCode = $tester->execute(['--home-id' => 'home-42']);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('0.3850 EUR/kWh', $tester->getDisplay());
        self::assertStringContainsString('NORMAL', $tester->getDisplay());
    }

    public function testTodayPricesCommand(): void
    {
        $mockService = $this->createMock(TibberServiceInterface::class);
        $mockService->expects(self::once())
            ->method('getTodaysPrices')
            ->willReturn([
                new Price(total: 0.25, currency: 'EUR', level: PriceLevel::CHEAP),
            ]);

        $command  = new TodayPricesCommand($mockService);
        $tester   = new CommandTester($command);
        $exitCode = $tester->execute(['--home-id' => 'home-42']);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('0.2500', $tester->getDisplay());
        self::assertStringContainsString('CHEAP', $tester->getDisplay());
    }

    public function testTomorrowPricesCommand(): void
    {
        $mockService = $this->createMock(TibberServiceInterface::class);
        $mockService->expects(self::once())
            ->method('getTomorrowsPrices')
            ->willReturn([]);

        $command  = new TomorrowPricesCommand($mockService);
        $tester   = new CommandTester($command);
        $exitCode = $tester->execute(['--home-id' => 'home-42']);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('Tomorrow’s prices are not yet published', $tester->getDisplay());
    }

    public function testConsumptionCommand(): void
    {
        $mockService = $this->createMock(TibberServiceInterface::class);
        $mockService->expects(self::once())
            ->method('getConsumption')
            ->willReturn([
                new ConsumptionNode(consumption: 1.25, consumptionUnit: 'kWh', totalCost: 0.45, currency: 'EUR'),
            ]);

        $command  = new ConsumptionCommand($mockService);
        $tester   = new CommandTester($command);
        $exitCode = $tester->execute(['--home-id' => 'home-42', '--limit' => '5']);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('1.250', $tester->getDisplay());
        self::assertStringContainsString('0.45', $tester->getDisplay());
    }

    public function testPushNotificationCommand(): void
    {
        $mockService = $this->createMock(TibberServiceInterface::class);
        $mockService->expects(self::once())
            ->method('sendPushNotification')
            ->with('Test Title', 'Test Message', null)
            ->willReturn(new SendPushNotificationResult(successful: true, pushedToNumberOfDevices: 1));

        $command  = new PushNotificationCommand($mockService);
        $tester   = new CommandTester($command);
        $exitCode = $tester->execute([
            'title'   => 'Test Title',
            'message' => 'Test Message',
        ]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('Push notification sent successfully to 1 device(s)', $tester->getDisplay());
    }

    public function testSchemaDumpCommandRequiresToken(): void
    {
        $command = new \WebProject\TibberApiClient\Command\SchemaDumpCommand();
        $tester  = new CommandTester($command);

        $backupEnv = $_ENV['TIBBER_API_TOKEN'] ?? null;
        unset($_ENV['TIBBER_API_TOKEN'], $_SERVER['TIBBER_API_TOKEN']);
        putenv('TIBBER_API_TOKEN');

        try {
            $exitCode = $tester->execute([]);
            self::assertSame(1, $exitCode);
            self::assertStringContainsString('No Tibber API token provided', $tester->getDisplay());
        } finally {
            if (null !== $backupEnv) {
                $_ENV['TIBBER_API_TOKEN'] = $backupEnv;
                putenv('TIBBER_API_TOKEN=' . $backupEnv);
            }
        }
    }
}
