<?php

namespace GetCandy\Api\Console\Commands;

use Hash;
use Laravel\Passport\Client;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use GetCandy\Api\Core\Auth\Models\User;
use GetCandy\Api\Core\Taxes\Models\Tax;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Currencies\Models\Currency;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use GetCandy\Api\Core\Associations\Models\AssociationGroup;

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
    public function handle()
    {
        $this->disclaimer();
        $this->requirements();
        $this->preflight();
        $this->printTitle();
        $this->stepOne();
        $this->stepTwo();

        $this->line('--------------------------------------------------------');

        $this->info('All done! Here is some useful info to get started');

        $headers = ['', ''];

        $client = Client::first();

        $this->table($headers, [
            ['Username / Email', $this->user->email],
            ['Password', '[hidden]'],
            ['API URL', url('api/'.config('app.api_version', 'v1'))],
            ['CMS Docs', 'https://getcandy.io/documentation/hub'],
            ['CMS Docs', 'https://getcandy.io/documentation/api'],
            ['OAuth Client ID', $client->id],
            ['OAuth Secret', $client->secret],
        ]);
    }

    protected function disclaimer()
    {
        $this->warn('*** BEFORE YOU CONTINUE ***');
        $this->line(' ');
        $this->warn('Please note, this software is very much considered in an Alpha Release state, if you are installing this on an existing project, please reconsider.');
        $this->line(' ');
        $this->warn('We do not want to cause any damage to your data and at this stage we cannot guarantee this wont\'t happen.');
        $this->warn('The API needs certain data to "work" at this point so this installer will add data for things such as Taxes, Currencies, Attributes etc');

        if ($this->confirm('Are you happy to continue?')) {
            $this->info('Sweet :)');
        } else {
            exit;
        }
    }

    protected function requirements()
    {
        $this->info('Checking requirements');
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
        $this->info('Initialising...');
        $this->call('migrate');

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'customer']);

        $this->info('Adding base languages');

        Language::create([
            'lang' => 'en',
            'iso' => 'gb',
            'name' => 'English',
            'default' => true,
        ]);

        $this->info('Adding VAT...');

        Tax::create([
            'percentage' => 20,
            'name' => 'VAT',
            'default' => true,
        ]);

        $this->info('Adding Attributes');

        $group = AttributeGroup::forceCreate([
            'name' => ['en' => 'Marketing'],
            'handle' => 'marketing',
            'position' => 1,
        ]);

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Name', 'sv' => 'Namn'];
        $attribute->handle = 'name';
        $attribute->position = 1;
        $attribute->group_id = $group->id;
        $attribute->required = true;
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Short Description'];
        $attribute->handle = 'short_description';
        $attribute->position = 2;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = true;
        $attribute->type = 'richtext';
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Description'];
        $attribute->handle = 'description';
        $attribute->position = 2;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = true;
        $attribute->type = 'richtext';
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();

        // $group = AttributeGroup::create([
        //     'name' => ['en' => 'General', 'sv' => 'Allmän'],
        //     'handle' => 'general',
        //     'position' => 2
        // ]);

        $group = AttributeGroup::forceCreate([
            'name' => ['en' => 'SEO', 'sv' => 'SEO'],
            'handle' => 'seo',
            'position' => 3,
        ]);

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Page Title'];
        $attribute->handle = 'page_title';
        $attribute->position = 1;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = false;
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Meta description'];
        $attribute->handle = 'meta_description';
        $attribute->position = 2;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = false;
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->type = 'textarea';
        $attribute->save();

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Meta Keywords'];
        $attribute->handle = 'meta_keywords';
        $attribute->position = 3;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = false;
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();

        $this->info('Adding some base settings');

        \GetCandy\Api\Core\Settings\Models\Setting::forceCreate([
            'name' => 'Products',
            'handle' => 'products',
            'content' => [
                'asset_source' => 'products',
                'transforms' => ['large_thumbnail'],
            ],
        ]);

        \GetCandy\Api\Core\Settings\Models\Setting::forceCreate([
            'name' => 'Categories',
            'handle' => 'categories',
            'content' => [
                'asset_source' => 'categories',
                'transforms' => ['large_thumbnail'],
            ],
        ]);

        \GetCandy\Api\Core\Settings\Models\Setting::forceCreate([
            'name' => 'Orders',
            'handle' => 'orders',
            'content' => [],
        ]);

        $this->info('Setting up some customer groups');

        CustomerGroup::forceCreate([
            'name' => 'Retail',
            'handle' => 'retail',
            'default' => true,
            'system' => true,
        ]);

        CustomerGroup::forceCreate([
            'name' => 'Guest',
            'handle' => 'guest',
            'default' => false,
            'system' => true,
        ]);

        $this->info('Adding some currencies');

        Currency::create([
            'code' => 'GBP',
            'name' => 'British Pound',
            'enabled' => true,
            'exchange_rate' => 1,
            'format' => '&#xa3;{price}',
            'decimal_point' => '.',
            'thousand_point' => ',',
            'default' => true,
        ]);

        Currency::create([
            'code' => 'EUR',
            'name' => 'Euro',
            'enabled' => true,
            'exchange_rate' => 0.87260,
            'format' => '&euro;{price}',
            'decimal_point' => '.',
            'thousand_point' => ',',
        ]);

        Currency::create([
            'code' => 'USD',
            'name' => 'US Dollars',
            'enabled' => true,
            'exchange_rate' => 0.71,
            'format' => '${price}',
            'decimal_point' => '.',
            'thousand_point' => ',',
        ]);

        $this->info('Initialising Assets');

        $sources = [
            [
                'name' => 'Product images',
                'handle' => 'products',
                'disk' => 'public',
                'path' => 'products',
            ],
            [
                'name' => 'Category images',
                'handle' => 'categories',
                'disk' => 'public',
                'path' => 'categories',
            ],
            [
                'name' => 'Channel images',
                'handle' => 'channels',
                'disk' => 'public',
            ],
        ];

        foreach ($sources as $source) {
            \GetCandy\Api\Core\Assets\Models\AssetSource::create($source);
        }

        \GetCandy\Api\Core\Assets\Models\Transform::create([
            'name' => 'Thumbnail',
            'handle' => 'thumbnail',
            'mode' => 'fit',
            'width' => 250,
            'height' => 250,
        ]);

        \GetCandy\Api\Core\Assets\Models\Transform::create([
            'name' => 'Large Thumbnail',
            'handle' => 'large_thumbnail',
            'mode' => 'fit',
            'width' => 485,
            'height' => 400,
        ]);

        $this->info('Adding association groups');

        AssociationGroup::forceCreate([
            'name' => 'Upsell',
            'handle' => 'upsell',
        ]);
        AssociationGroup::forceCreate([
            'name' => 'Cross-sell',
            'handle' => 'cross-sell',
        ]);
        AssociationGroup::forceCreate([
            'name' => 'Alternate',
            'handle' => 'alternate',
        ]);

        $countries = json_decode(file_get_contents(__DIR__.'/../../../countries.json'), true);

        foreach ($countries as $country) {
            \GetCandy\Api\Core\Countries\Models\Country::create([
                'name' => $country['name']['common'],
                'iso_a_2' => $country['cca2'],
                'iso_a_3' => $country['cca3'],
                'iso_numeric' => $country['ccn3'],
                'region' => $country['region'],
                'sub_region' => $country['subregion'],
            ]);
        }

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
