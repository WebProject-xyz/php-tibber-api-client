<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Command;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use WebProject\TibberApiClient\Client\TibberClient;
use WebProject\TibberApiClient\Service\TibberService;
use WebProject\TibberApiClient\Service\TibberServiceInterface;

use function is_string;

abstract class AbstractTibberCommand extends Command
{
    public function __construct(
        private ?TibberServiceInterface $tibberService = null,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'token',
            't',
            InputOption::VALUE_REQUIRED,
            'Tibber API access token (falls back to TIBBER_API_TOKEN environment variable)',
        );
    }

    protected function getTibberService(InputInterface $input): TibberServiceInterface
    {
        if (null !== $this->tibberService) {
            return $this->tibberService;
        }

        /** @var string|null $token */
        $token = $input->getOption('token');

        if (null === $token || '' === trim($token)) {
            $token = $_ENV['TIBBER_API_TOKEN'] ?? $_SERVER['TIBBER_API_TOKEN'] ?? getenv('TIBBER_API_TOKEN');
            if (!is_string($token) || '' === trim($token)) {
                throw new InvalidArgumentException('No Tibber API token provided. Pass --token, set TIBBER_API_TOKEN in your environment or .env.local file.');
            }
        }

        return new TibberService(new TibberClient($token));
    }

    protected function resolveHomeId(TibberServiceInterface $service, ?string $homeId): string
    {
        if (null !== $homeId && '' !== trim($homeId)) {
            return $homeId;
        }

        $homes = $service->getHomes();
        if ([] === $homes) {
            throw new RuntimeException('No homes found for the authenticated Tibber account.');
        }

        return $homes[0]->id;
    }
}
