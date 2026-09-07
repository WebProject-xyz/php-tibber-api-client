<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Command;

use DateTimeInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WebProject\TibberApiClient\Model\Enum\PriceLevel;
use WebProject\TibberApiClient\Model\Enum\PriceResolution;

use function sprintf;

#[AsCommand(
    name: 'tibber:prices:current',
    description: 'Fetch the current electricity price for a Tibber home',
)]
class CurrentPriceCommand extends AbstractTibberCommand
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

        $io->title(sprintf('Current Tibber Energy Price (Home: %s, Resolution: %s)', $homeId, $resolution->value));

        $price = $service->getCurrentPrice($homeId, $resolution);

        if (null === $price) {
            $io->warning('No current price information available.');

            return self::FAILURE;
        }

        $io->definitionList(
            ['Total' => sprintf('%.4f %s/kWh', $price->total ?? 0.0, $price->currency ?? 'EUR')],
            ['Energy' => sprintf('%.4f %s/kWh', $price->energy ?? 0.0, $price->currency ?? 'EUR')],
            ['Tax' => sprintf('%.4f %s/kWh', $price->tax ?? 0.0, $price->currency ?? 'EUR')],
            ['Starts At' => $price->startsAt instanceof DateTimeInterface ? $price->startsAt->format(DateTimeInterface::ATOM) : 'N/A'],
            ['Level' => $price->level instanceof PriceLevel ? $price->level->value : 'N/A'],
            ['Currency' => $price->currency ?? 'EUR'],
        );

        return self::SUCCESS;
    }
}
