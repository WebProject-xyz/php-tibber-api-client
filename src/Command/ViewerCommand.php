<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function sprintf;

#[AsCommand(
    name: 'tibber:viewer',
    description: 'Fetch and display Tibber account viewer and registered homes information',
)]
class ViewerCommand extends AbstractTibberCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io      = new SymfonyStyle($input, $output);
        $service = $this->getTibberService($input);

        $io->title('Tibber Account Information');

        $viewer = $service->getViewer();

        $io->definitionList(
            ['User ID' => $viewer->userId ?? 'N/A'],
            ['Name' => $viewer->name ?? 'N/A'],
            ['Login' => $viewer->login ?? 'N/A'],
            ['WebSocket URL' => $viewer->websocketSubscriptionUrl ?? 'N/A'],
            ['Total Homes' => count($viewer->homes)],
        );

        if ([] === $viewer->homes) {
            $io->warning('No registered homes found.');

            return self::SUCCESS;
        }

        $io->section('Registered Homes');
        $table = new Table($output);
        $table->setHeaders(['ID', 'Address', 'Type', 'Size (m²)', 'Timezone', 'Real-Time Enabled']);

        foreach ($viewer->homes as $home) {
            $address = null !== $home->address
                ? sprintf('%s, %s %s', $home->address->address1 ?? '', $home->address->postalCode ?? '', $home->address->city ?? '')
                : 'N/A';

            $table->addRow([
                $home->id,
                trim($address, ', '),
                $home->type instanceof \WebProject\TibberApiClient\Model\Enum\HomeType ? $home->type->value : 'N/A',
                null !== $home->size ? (string) $home->size : 'N/A',
                $home->timeZone ?? 'N/A',
                $home->features?->realTimeConsumptionEnabled ? 'Yes' : 'No',
            ]);
        }

        $table->render();

        return self::SUCCESS;
    }
}
