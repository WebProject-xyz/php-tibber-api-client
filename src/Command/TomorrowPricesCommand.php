<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Command;

use DateTimeInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WebProject\TibberApiClient\Model\Enum\PriceLevel;
use WebProject\TibberApiClient\Model\Enum\PriceResolution;

use function sprintf;

#[AsCommand(
    name: 'tibber:prices:tomorrow',
    description: "Fetch tomorrow's electricity prices for a Tibber home (available ~13:00 CET)",
)]
class TomorrowPricesCommand extends AbstractTibberCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->addOption(
            'home-id',
            null,
            InputOption::VALUE_OPTIONAL,
            'Tibber home ID (defaults to first available home)',
        );

        $this->addOption(
            'resolution',
            'r',
            InputOption::VALUE_OPTIONAL,
            'Price resolution (HOURLY or QUARTER_HOURLY)',
            PriceResolution::HOURLY->value,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io      = new SymfonyStyle($input, $output);
        $service = $this->getTibberService($input);

        /** @var string|null $homeIdOption */
        $homeIdOption = $input->getOption('home-id');
        $homeId       = $this->resolveHomeId($service, $homeIdOption);

        $resolutionStr = (string) $input->getOption('resolution');
        $resolution    = PriceResolution::tryFrom(strtoupper($resolutionStr)) ?? PriceResolution::HOURLY;

        $io->title(sprintf("Tomorrow's Tibber Energy Prices (Home: %s, Resolution: %s)", $homeId, $resolution->value));

        $prices = $service->getTomorrowsPrices($homeId, $resolution);

        if ([] === $prices) {
            $io->note('Tomorrow’s prices are not yet published. Tibber usually releases them around 13:00 CET.');

            return self::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['Starts At', 'Total', 'Energy', 'Tax', 'Level', 'Currency']);

        foreach ($prices as $price) {
            $table->addRow([
                $price->startsAt instanceof DateTimeInterface ? $price->startsAt->format('Y-m-d H:i') : 'N/A',
                sprintf('%.4f', $price->total ?? 0.0),
                sprintf('%.4f', $price->energy ?? 0.0),
                sprintf('%.4f', $price->tax ?? 0.0),
                $price->level instanceof PriceLevel ? $price->level->value : 'N/A',
                $price->currency ?? 'EUR',
            ]);
        }

        $table->render();

        return self::SUCCESS;
    }
}
