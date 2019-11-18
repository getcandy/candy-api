<?php

namespace GetCandy\Api\Console\Commands;

use GetCandy\Api\Core\Associations\Models\AssociationGroup;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use GetCandy\Api\Core\Auth\Models\User;
use GetCandy\Api\Core\Currencies\Models\Currency;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Taxes\Models\Tax;
use Hash;
use Illuminate\Console\Command;
use Laravel\Passport\Client;
use Spatie\Permission\Models\Role;
use GetCandy\Api\Installer\GetCandyInstaller;
use Illuminate\Contracts\Events\Dispatcher;
use GetCandy\Api\Installer\Events\PreflightCompletedEvent;

class InstallGetCandyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'candy:install';

    protected $user;

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
        $events->listen(PreflightCompletedEvent::class, function ($event) {
            $database = $event->response['database'];
            if (!$database['connected']) {
                $this->error('Unable to connect to database');
                return;
            }
            $this->info('Preflight complete');
        });

        // Run the installer...
        $installer->onCommand($this)->run();

        // $this->preflight();
        // $this->printTitle();
        // $this->stepOne();
        // $this->stepTwo();

        // $this->line('--------------------------------------------------------');

        // $this->info('All done! Here is some useful info to get started');

        // $headers = ['', ''];

        // $client = Client::first();

        // $this->table($headers, [
        //     ['Username / Email', $this->user->email],
        //     ['Password', '[hidden]'],
        //     ['API URL', url('api/'.config('app.api_version', 'v1'))],
        //     ['CMS Docs', 'https://getcandy.io/documentation/hub'],
        //     ['CMS Docs', 'https://getcandy.io/documentation/api'],
        //     ['OAuth Client ID', $client->id],
        //     ['OAuth Secret', $client->secret],
        // ]);
    }

    /**
     * Step one.
     *
     * @return void
     */
    protected function stepOne()
    {
        $this->info('Lets start with the basics...');
        // Set up new user
        $name = $this->ask('What\'s your name?');

        $firstAndLast = explode(' ', $name);

        $email = $this->ask("Nice to meet you {$name}, what's your email?");

        $tries = 0;

        while (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($tries < 3) {
                $message = 'Oops! That email looks invalid, can we try again?';
            } elseif ($tries >= 3 && $tries <= 6) {
                $message = 'No really, give me a proper email...';
            } elseif ($tries >= 6 && $tries <= 9) {
                $message = 'Seriously now, lets not do this all day... what is it?';
            } elseif ($tries >= 10) {
                $this->error('I give up');
                exit();
            }

            $email = $this->ask($message);
            $tries++;
        }

        $password = $this->secret('Choose a password (hidden)');

        $passwordConfirm = $this->secret('Confirm it (hidden)');

        while ($password != $passwordConfirm) {
            $password = $this->secret('Oop! Passwords didn\'t match try again');
            $passwordConfirm = $this->secret('Aaaand confirm it');
        }

        $this->info('Just creating your account now');

        // Get our auth user model..

        $model = config('auth.providers.users.model');

        $user = new $model;

        $user->fill([
            'password' => Hash::make($password),
            'name' => $name,
            'email' => $email,
        ]);

        $user->save();

        $user->assignRole('admin');

        $this->user = $user;
    }

    /**
     * Set up the channel and product families.
     *
     * @return void
     */
    protected function stepTwo()
    {
        $this->line('--------------------------------------------------------');
        $this->info('About your store...');

        $this->info('Channels where you products will live, for example if you sell online, consider "webstore" as your channel name');
        $channel = $this->ask('Choose a new channel name');

        $this->info('Sounds good to me, lets get that set up...');

        app('api')->channels()->create([
            'name' => $channel,
            'default' => true,
        ]);

        $productFamily = $this->ask('We need to set up an initial product family name');

        $this->info('Setting that up for you now...');

        // Get all our attributes and assign to the product family.
        $attributes = Attribute::all();

        $family = app('api')->productFamilies()->create([
            'name' => [
                'en' => $productFamily,
            ],
        ]);

        $family->attributes()->attach($attributes);
    }

    /**
     * Do some behind the scenes stuff first.
     *
     * @return void
     */
    protected function preflight()
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'customer']);

        $this->call('passport:install');
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
        $this->line('==================================== v0.0.1-alpha');
    }
}
