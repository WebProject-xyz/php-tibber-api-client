<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Tests\Unit;

use Codeception\Test\Unit;
use DateTimeInterface;
use WebProject\TibberApiClient\Model\Address;
use WebProject\TibberApiClient\Model\ConsumptionNode;
use WebProject\TibberApiClient\Model\Enum\HeatingSource;
use WebProject\TibberApiClient\Model\Enum\HomeType;
use WebProject\TibberApiClient\Model\Enum\PriceLevel;
use WebProject\TibberApiClient\Model\Price;
use WebProject\TibberApiClient\Model\Viewer;
use WebProject\TibberApiClient\Serializer\TibberSerializerFactory;

class TibberSerializerTest extends Unit
{
    public function testDeserializePrice(): void
    {
        $serializer = TibberSerializerFactory::create();
        $json       = json_encode([
            'total'    => 0.4071,
            'energy'   => 0.1707,
            'tax'      => 0.2364,
            'startsAt' => '2026-09-07T23:00:00+02:00',
            'currency' => 'EUR',
            'level'    => 'EXPENSIVE',
        ], JSON_THROW_ON_ERROR);

        /** @var Price $price */
        $price = $serializer->deserialize($json, Price::class, 'json');

        self::assertSame(0.4071, $price->getTotalFloat());
        self::assertSame(0.1707, $price->getEnergyFloat());
        self::assertSame(0.2364, $price->getTaxFloat());
        self::assertSame('EUR', $price->currency);
        self::assertSame(PriceLevel::EXPENSIVE, $price->level);
        self::assertNotNull($price->startsAt);
        self::assertSame('2026-09-07T23:00:00+02:00', $price->startsAt->format(DateTimeInterface::RFC3339));
    }

    public function testDeserializeAddressWithNumericFloatHelpers(): void
    {
        $serializer = TibberSerializerFactory::create();
        $json       = json_encode([
            'address1'   => 'Musterstr. 1',
            'city'       => 'Berlin',
            'postalCode' => '10115',
            'country'    => 'DE',
            'latitude'   => '52.520008',
            'longitude'  => '13.404954',
        ], JSON_THROW_ON_ERROR);

        /** @var Address $address */
        $address = $serializer->deserialize($json, Address::class, 'json');

        self::assertSame('Musterstr. 1', $address->address1);
        self::assertSame('Berlin', $address->city);
        self::assertSame(52.520008, $address->getLatitudeFloat());
        self::assertSame(13.404954, $address->getLongitudeFloat());
    }

    public function testDeserializeViewerAndHomes(): void
    {
        $serializer = TibberSerializerFactory::create();
        $payload    = [
            'login'                    => 'test@example.com',
            'userId'                   => 'user-123',
            'name'                     => 'Spock',
            'websocketSubscriptionUrl' => 'wss://websocket-api.tibber.com/v1-beta/gql/subscriptions',
            'homes'                    => [
                [
                    'id'                   => 'home-999',
                    'timeZone'             => 'Europe/Berlin',
                    'appNickname'          => 'My Home',
                    'size'                 => 120,
                    'type'                 => 'HOUSE',
                    'primaryHeatingSource' => 'GAS',
                    'features'             => [
                        'realTimeConsumptionEnabled' => true,
                    ],
                ],
            ],
        ];

        /** @var Viewer $viewer */
        $viewer = $serializer->denormalize($payload, Viewer::class);

        self::assertSame('test@example.com', $viewer->login);
        self::assertSame('Spock', $viewer->name);
        self::assertCount(1, $viewer->homes);

        $home = $viewer->homes[0];
        self::assertSame('home-999', $home->id);
        self::assertSame(HomeType::HOUSE, $home->type);
        self::assertSame(HeatingSource::GAS, $home->primaryHeatingSource);
        self::assertTrue($home->features?->realTimeConsumptionEnabled);
    }

    public function testDeserializeConsumptionNode(): void
    {
        $serializer = TibberSerializerFactory::create();
        $data       = [
            'from'            => '2026-09-07T22:00:00+02:00',
            'to'              => '2026-09-07T23:00:00+02:00',
            'cost'            => 0.11,
            'unitPrice'       => 0.4267,
            'unitPriceVAT'    => 0.08,
            'consumption'     => 0.212,
            'consumptionUnit' => 'kWh',
            'totalCost'       => 0.11,
            'unitCost'        => 0.4267,
            'currency'        => 'EUR',
        ];

        /** @var ConsumptionNode $node */
        $node = $serializer->denormalize($data, ConsumptionNode::class);

        self::assertSame(0.212, $node->getConsumptionFloat());
        self::assertSame(0.11, $node->getCostFloat());
        self::assertSame('kWh', $node->consumptionUnit);
        self::assertSame('EUR', $node->currency);
    }
}
