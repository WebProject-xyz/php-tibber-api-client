<?php
declare(strict_types=1);

namespace WebProject\TibberApiClient\Tests\Unit;

use WebProject\TibberApiClient\Tests\Support\UnitTester;

class ExampleTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;

    // tests
    public function testSomeFeature(): void
    {
        /** @phpstan-ignore-next-line method.alreadyNarrowedType */
        self::assertTrue(true);
    }
}
