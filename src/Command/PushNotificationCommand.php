<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WebProject\TibberApiClient\Model\Enum\AppScreen;

use function sprintf;

#[AsCommand(
    name: 'tibber:push',
    description: 'Send a push notification to the authenticated user’s Tibber mobile application',
)]
class PushNotificationCommand extends AbstractTibberCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->addArgument(
            'title',
            InputArgument::REQUIRED,
            'Title of the push notification',
        );

        $this->addArgument(
            'message',
            InputArgument::REQUIRED,
            'Message body of the push notification',
        );

        $this->addOption(
            'screen',
            's',
            InputOption::VALUE_OPTIONAL,
            'App screen to open on notification tap (e.g. HOME, CONSUMPTION, INVOICES)',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io      = new SymfonyStyle($input, $output);
        $service = $this->getTibberService($input);

        /** @var string $title */
        $title = $input->getArgument('title');
        /** @var string $message */
        $message = $input->getArgument('message');

        /** @var string|null $screenOption */
        $screenOption = $input->getOption('screen');
        $screen       = null;

        if (null !== $screenOption && '' !== trim($screenOption)) {
            $screen = AppScreen::tryFrom(strtoupper($screenOption));
            if (null === $screen) {
                $io->warning(sprintf('Unknown screen "%s". Proceeding without specific target screen.', $screenOption));
            }
        }

        $io->title('Sending Push Notification to Tibber App');
        $result = $service->sendPushNotification($title, $message, $screen);

        if ($result->successful) {
            $io->success(sprintf(
                'Push notification sent successfully to %d device(s).',
                $result->pushedToNumberOfDevices,
            ));

            return self::SUCCESS;
        }

        $io->error('Failed to send push notification.');

        return self::FAILURE;
    }
}
