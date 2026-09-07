<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Service;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use WebProject\TibberApiClient\Client\TibberClientInterface;
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
use WebProject\TibberApiClient\Serializer\TibberSerializerFactory;

use function is_array;

class TibberService implements TibberServiceInterface
{
    private readonly DenormalizerInterface&SerializerInterface $serializer;

    public function __construct(
        private readonly TibberClientInterface $client,
        (DenormalizerInterface&SerializerInterface)|null $serializer = null,
    ) {
        $this->serializer = $serializer ?? TibberSerializerFactory::create();
    }

    public function getViewer(): Viewer
    {
        $query = <<<'GRAPHQL'
            query getViewer {
              viewer {
                login
                userId
                name
                websocketSubscriptionUrl
                homes {
                  id
                  timeZone
                  appNickname
                  appAvatar
                  size
                  type
                  numberOfResidents
                  primaryHeatingSource
                  hasVentilationSystem
                  mainFuseSize
                  address {
                    address1
                    address2
                    address3
                    postalCode
                    city
                    country
                    latitude
                    longitude
                  }
                  owner {
                    id
                    firstName
                    isCompany
                    name
                    middleName
                    lastName
                    organizationNo
                    language
                    contactInfo {
                      email
                      mobile
                    }
                  }
                  meteringPointData {
                    consumptionEan
                    gridCompany
                    gridAreaCode
                    priceAreaCode
                    productionEan
                    energyTaxType
                    vatType
                    estimatedAnnualConsumption
                  }
                  currentSubscription {
                    id
                    validFrom
                    validTo
                    status
                  }
                  features {
                    realTimeConsumptionEnabled
                  }
                }
              }
            }
            GRAPHQL;

        $data       = $this->client->query($query);
        $viewerData = $data['viewer'] ?? [];

        /** @var Viewer $viewer */
        $viewer = $this->serializer->denormalize($viewerData, Viewer::class);

        return $viewer;
    }

    /**
     * @return array<Home>
     */
    public function getHomes(): array
    {
        return $this->getViewer()->homes;
    }

    public function getHome(string $homeId): ?Home
    {
        $query = <<<'GRAPHQL'
            query getHome($homeId: ID!) {
              viewer {
                home(id: $homeId) {
                  id
                  timeZone
                  appNickname
                  appAvatar
                  size
                  type
                  numberOfResidents
                  primaryHeatingSource
                  hasVentilationSystem
                  mainFuseSize
                  address {
                    address1
                    address2
                    address3
                    postalCode
                    city
                    country
                    latitude
                    longitude
                  }
                  owner {
                    id
                    firstName
                    isCompany
                    name
                    middleName
                    lastName
                    organizationNo
                    language
                    contactInfo {
                      email
                      mobile
                    }
                  }
                  meteringPointData {
                    consumptionEan
                    gridCompany
                    gridAreaCode
                    priceAreaCode
                    productionEan
                    energyTaxType
                    vatType
                    estimatedAnnualConsumption
                  }
                  currentSubscription {
                    id
                    validFrom
                    validTo
                    status
                  }
                  features {
                    realTimeConsumptionEnabled
                  }
                }
              }
            }
            GRAPHQL;

        $data     = $this->client->query($query, ['homeId' => $homeId]);
        $homeData = $data['viewer']['home'] ?? null;

        if (!is_array($homeData)) {
            return null;
        }

        /** @var Home $home */
        $home = $this->serializer->denormalize($homeData, Home::class);

        return $home;
    }

    public function getPriceInfo(string $homeId, PriceResolution $resolution = PriceResolution::HOURLY): ?PriceInfo
    {
        $query = <<<'GRAPHQL'
            query getPriceInfo($homeId: ID!, $resolution: PriceInfoResolution = HOURLY) {
              viewer {
                home(id: $homeId) {
                  currentSubscription {
                    priceInfo(resolution: $resolution) {
                      current {
                        total
                        energy
                        tax
                        startsAt
                        currency
                        level
                      }
                      today {
                        total
                        energy
                        tax
                        startsAt
                        currency
                        level
                      }
                      tomorrow {
                        total
                        energy
                        tax
                        startsAt
                        currency
                        level
                      }
                    }
                  }
                }
              }
            }
            GRAPHQL;

        $data = $this->client->query($query, [
            'homeId'     => $homeId,
            'resolution' => $resolution->value,
        ]);

        $priceInfoData = $data['viewer']['home']['currentSubscription']['priceInfo'] ?? null;
        if (!is_array($priceInfoData)) {
            return null;
        }

        /** @var PriceInfo $priceInfo */
        $priceInfo = $this->serializer->denormalize($priceInfoData, PriceInfo::class);

        return $priceInfo;
    }

    public function getCurrentPrice(string $homeId, PriceResolution $resolution = PriceResolution::HOURLY): ?Price
    {
        return $this->getPriceInfo($homeId, $resolution)?->current;
    }

    /**
     * @return array<Price>
     */
    public function getTodaysPrices(string $homeId, PriceResolution $resolution = PriceResolution::HOURLY): array
    {
        $info = $this->getPriceInfo($homeId, $resolution);

        return $info instanceof PriceInfo ? $info->today : [];
    }

    /**
     * @return array<Price>
     */
    public function getTomorrowsPrices(string $homeId, PriceResolution $resolution = PriceResolution::HOURLY): array
    {
        $info = $this->getPriceInfo($homeId, $resolution);

        return $info instanceof PriceInfo ? $info->tomorrow : [];
    }

    /**
     * @return array<ConsumptionNode>
     */
    public function getConsumption(string $homeId, EnergyResolution $resolution, int $lastCount): array
    {
        $query = <<<'GRAPHQL'
            query getConsumption($homeId: ID!, $resolution: EnergyResolution!, $lastCount: Int!) {
              viewer {
                home(id: $homeId) {
                  consumption(resolution: $resolution, last: $lastCount) {
                    nodes {
                      from
                      to
                      cost
                      unitPrice
                      unitPriceVAT
                      consumption
                      consumptionUnit
                      totalCost
                      unitCost
                      currency
                    }
                  }
                }
              }
            }
            GRAPHQL;

        $data = $this->client->query($query, [
            'homeId'     => $homeId,
            'resolution' => $resolution->value,
            'lastCount'  => $lastCount,
        ]);

        $nodesData = $data['viewer']['home']['consumption']['nodes'] ?? [];
        if (!is_array($nodesData)) {
            return [];
        }

        return array_map(
            fn (mixed $node): ConsumptionNode => $this->serializer->denormalize((array) $node, ConsumptionNode::class),
            $nodesData,
        );
    }

    public function sendPushNotification(string $title, string $message, ?AppScreen $screen = null): SendPushNotificationResult
    {
        $query = <<<'GRAPHQL'
            mutation sendPushNotification($input: PushNotificationInput!) {
              sendPushNotification(input: $input) {
                successful
                pushedToNumberOfDevices
              }
            }
            GRAPHQL;

        $input = [
            'title'   => $title,
            'message' => $message,
        ];

        if (null !== $screen) {
            $input['screenToOpen'] = $screen->value;
        }

        $data       = $this->client->query($query, ['input' => $input]);
        $resultData = $data['sendPushNotification'] ?? [];

        /** @var SendPushNotificationResult $result */
        $result = $this->serializer->denormalize($resultData, SendPushNotificationResult::class);

        return $result;
    }

    public function updateHome(UpdateHomeInput $input): ?Home
    {
        $query = <<<'GRAPHQL'
            mutation updateHome($input: UpdateHomeInput!) {
              updateHome(input: $input) {
                id
                timeZone
                appNickname
                appAvatar
                size
                type
                numberOfResidents
                primaryHeatingSource
                hasVentilationSystem
                mainFuseSize
              }
            }
            GRAPHQL;

        $data     = $this->client->query($query, ['input' => $input->toArray()]);
        $homeData = $data['updateHome'] ?? null;

        if (!is_array($homeData)) {
            return null;
        }

        /** @var Home $home */
        $home = $this->serializer->denormalize($homeData, Home::class);

        return $home;
    }
}
