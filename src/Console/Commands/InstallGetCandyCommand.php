<?php

namespace GetCandy\Api\Console\Commands;

use GetCandy;
use GetCandy\Api\Installer\Events\PreflightCompletedEvent;
use GetCandy\Api\Installer\GetCandyInstaller;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;

class InstallGetCandyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'candy:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install GetCandy';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Dispatcher $events, GetCandyInstaller $installer)
    {
        $this->printTitle();
        $events->listen(PreflightCompletedEvent::class, function ($event) {
            $database = $event->response['database'];
            if (! $database['connected']) {
                $this->error('Unable to connect to database');

                exit(1);
            }
        });

        // Run the installer...
        $installer->onCommand($this)->run();

        $this->info('Installation complete');
        $this->info('Make a GET request to the root API endpoint to check everything is running');
    }

    /**
     * Print the title.
     *
     * @return void
     */
    protected function printTitle()
    {
        $this->line('= Welcome to ====================================');
        $this->line('   ______     __  ______                __
  / ____/__  / /_/ ____/___ _____  ____/ /_  __
 / / __/ _ \/ __/ /   / __ `/ __ \/ __  / / / /
/ /_/ /  __/ /_/ /___/ /_/ / / / / /_/ / /_/ /
\____/\___/\__/\____/\__,_/_/ /_/\__,_/\__, /
                                      /____/ ');
        $this->line(str_pad(' '.GetCandy::version().' =', 48, '=', STR_PAD_LEFT));
    }
}
