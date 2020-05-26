<?php

namespace Tests\Unit\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Runners\CurrencyRunner;
use Tests\TestCase;

/**
 * @group installer
 */
class CurrencyRunnerTest extends TestCase
{
    protected $withSeedData = false;

    public function test_install_can_run()
    {
        $runner = app()->make(CurrencyRunner::class);

        $this->assertEquals(0, DB::table('currencies')->count());

        $runner->run();

        $this->assertDatabaseHas('currencies', [
            'code' => 'GBP',
            'name' => 'British Pound',
            'enabled' => true,
            'exchange_rate' => 1,
            'format' => '£{price}',
            'decimal_point' => '.',
            'thousand_point' => ',',
            'default' => true,
        ]);

        $this->assertDatabaseHas('currencies', [
            'code' => 'EUR',
            'name' => 'Euro',
            'enabled' => true,
            'exchange_rate' => 0.87260,
            'format' => '€{price}',
            'decimal_point' => '.',
            'thousand_point' => ',',
            'default' => false,
        ]);
    }
}
