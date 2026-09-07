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
use WebProject\TibberApiClient\Model\Enum\EnergyResolution;

use function sprintf;

#[AsCommand(
    name: 'tibber:consumption',
    description: 'Fetch historical energy consumption nodes for a Tibber home',
)]
class ConsumptionCommand extends AbstractTibberCommand
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
            'Energy resolution (HOURLY, DAILY, WEEKLY, MONTHLY, ANNUAL)',
            EnergyResolution::HOURLY->value,
        );

        $this->addOption(
            'limit',
            'l',
            InputOption::VALUE_OPTIONAL,
            'Number of records to retrieve',
            '24',
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
        $resolution    = EnergyResolution::tryFrom(strtoupper($resolutionStr)) ?? EnergyResolution::HOURLY;

        $limit = max(1, (int) $input->getOption('limit'));

        $io->title(sprintf(
            'Tibber Consumption History (Home: %s, Resolution: %s, Limit: %d)',
            $homeId,
            $resolution->value,
            $limit,
        ));

        $nodes = $service->getConsumption($homeId, $resolution, $limit);

        if ([] === $nodes) {
            $io->warning('No consumption data available for the given criteria.');

            return self::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['From', 'To', 'Consumption', 'Unit', 'Unit Price', 'Total Cost', 'Currency']);

        foreach ($nodes as $node) {
            $table->addRow([
                $node->from instanceof DateTimeInterface ? $node->from->format('Y-m-d H:i') : 'N/A',
                $node->to instanceof DateTimeInterface ? $node->to->format('Y-m-d H:i') : 'N/A',
                sprintf('%.3f', $node->consumption ?? 0.0),
                $node->consumptionUnit ?? 'kWh',
                null !== $node->unitPrice ? sprintf('%.4f', $node->unitPrice) : 'N/A',
                null !== $node->totalCost ? sprintf('%.2f', $node->totalCost) : 'N/A',
                $node->currency ?? 'EUR',
            ]);
        }

        $table->render();

        return self::SUCCESS;
    }
}
