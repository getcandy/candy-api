<?php

namespace GetCandy\Api\Console\Commands;

use Illuminate\Console\Command;
use GetCandy\Api\Core\Payments\PaymentContract;

class CleanUpTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'candy:transactions:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans up any transactions';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(PaymentContract $payments)
    {
        $payments->with(
            config('getcandy.payments.gateway')
        )->cleanup();
    }
}
