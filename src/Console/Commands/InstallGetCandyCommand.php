<?php

namespace GetCandy\Api\Console\Commands;

use Hash;
use Laravel\Passport\Client;
use Illuminate\Console\Command;
use GetCandy\Api\Auth\Models\User;
use GetCandy\Api\Taxes\Models\Tax;
use Spatie\Permission\Models\Role;
use GetCandy\Api\Assets\Models\Transform;
use GetCandy\Api\Settings\Models\Setting;
use GetCandy\Api\Countries\Models\Country;
use GetCandy\Api\Assets\Models\AssetSource;
use GetCandy\Api\Languages\Models\Language;
use GetCandy\Api\Currencies\Models\Currency;
use GetCandy\Api\Attributes\Models\Attribute;
use GetCandy\Api\Customers\Models\CustomerGroup;
use GetCandy\Api\Attributes\Models\AttributeGroup;
use GetCandy\Api\Associations\Models\AssociationGroup;

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
            ['CMS Login', route('hub.login')],
            ['API URL', url('api/'.config('app.api_version', 'v1'))],
            ['CMS Docs', 'https://getcandy.io/documentation/hub'],
            ['CMS Docs', 'https://getcandy.io/documentation/api'],
            ['OAuth Client ID', $client->id],
            ['OAuth Secret', $client->secret],
        ]);
    }

    /**
     * Echo disclaimer on the command line.
     *
     * @return void
     */
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

    /**
     * Check requirements.
     *
     * @return void
     */
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
            'name'     => $name,
            'email'    => $email,
        ]);

        $user->save();

        $user->assignRole('admin');

        $this->user = $user;
    }

    /**
     * Set up the channel and product families
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
            'name'    => $channel,
            'default' => true,
        ]);

        $productFamily = $this->ask('We need to set up an initial product family name');

        $this->info('Setting that up for you now...');

        app('api')->productFamilies()->create([
            'name' => [
                'en' => $productFamily,
            ]
        ]);
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

        $this->createRoles();

        $this->createLanguages();

        $this->createTaxes();

        $this->createAttributes();

        $this->createBaseSettings();

        $this->createCustomerGroups();

        $this->createCurrencies();

        $this->info('Initialising Assets');

        $this->createSources();

        $this->createImageTransforms();

        $this->createAssociationGroups();

        $this->createCountries();

        $this->call('passport:install');
    }

    /**
     * Create Roles.
     *
     * @return void
     */
    public function createRoles()
    {
        $roles = [
            [
                'name' => 'admin',
            ],
            [
                'name' => 'customer',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }

    /**
     * Create languages.
     *
     * @return void
     */
    public function createLanguages()
    {
        $this->info('Adding base languages');

        $languages = [
            [
                'lang'    => 'en',
                'iso'     => 'gb',
                'name'    => 'English',
                'default' => true,
            ],
        ];

        foreach ($languages as $language) {
            Language::create($language);
        }
    }

    /**
     * Create Taxes.
     *
     * @return void
     */
    public function createTaxes()
    {
        $this->info('Adding Taxes...');

        $taxes = [
            [
                'percentage' => 20,
                'name'       => 'VAT',
                'default'    => true,
            ],
        ];

        foreach ($taxes as $tax) {
            Tax::create($tax);
        }
    }

    /**
     * Create base settings.
     *
     * @return void
     */
    public function createBaseSettings()
    {
        $this->info('Adding some base settings');

        $settings = [
            [
                'name'    => 'Products',
                'handle'  => 'products',
                'content' => [
                    'asset_source' => 'products',
                    'transforms'   => [
                        'large_thumbnail',
                    ],
                ],
            ],
        ];

        foreach ($settings as $setting) {
            Setting::forceCreate($setting);
        }
    }

    /**
     * Create Customer Groups.
     *
     * @return void
     */
    public function createCustomerGroups()
    {
        $this->info('Setting up some customer groups');

        $customerGroups = [
            [
                'name'    => 'Retail',
                'handle'  => 'retail',
                'default' => true,
                'system'  => true,
            ],
            [
                'name'    => 'Guest',
                'handle'  => 'guest',
                'default' => false,
                'system'  => true,
            ],
        ];

        foreach ($customerGroups as $group) {
            CustomerGroup::forceCreate($group);
        }
    }

    /**
     * Create Currencies.
     *
     * @return void
     */
    public function createCurrencies()
    {
        $this->info('Adding some currencies');

        $currencies = [
            [
                'code'           => 'GBP',
                'name'           => 'British Pound',
                'enabled'        => true,
                'exchange_rate'  => 1,
                'format'         => '&#xa3;{price}',
                'decimal_point'  => '.',
                'thousand_point' => ',',
                'default'        => true,
            ],
            [
                'code'           => 'EUR',
                'name'           => 'Euro',
                'enabled'        => true,
                'exchange_rate'  => 0.87260,
                'format'         => '&euro;{price}',
                'decimal_point'  => '.',
                'thousand_point' => ',',
            ],
            [
                'code'           => 'USD',
                'name'           => 'US Dollars',
                'enabled'        => true,
                'exchange_rate'  => 0.71,
                'format'         => '${price}',
                'decimal_point'  => '.',
                'thousand_point' => ',',
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::create($currency);
        }
    }

    /**
     * Create sources.
     *
     * @return void
     */
    public function createSources()
    {
        $sources = [
            [
                'name'   => 'Product images',
                'handle' => 'products',
                'disk'   => 'public',
                'path'   => 'products',
            ],
            [
                'name'   => 'Channel images',
                'handle' => 'channels',
                'disk'   => 'public',
            ]
        ];

        foreach ($sources as $source) {
            AssetSource::create($source);
        }
    }

    /**
     * Create Image Transforms.
     *
     * @return void
     */
    public function createImageTransforms()
    {
        $transforms = [
            [
                'name'   => 'Thumbnail',
                'handle' => 'thumbnail',
                'mode'   => 'fit',
                'width'  => 250,
                'height' => 250,
            ],
            [
                'name'   => 'Large Thumbnail',
                'handle' => 'large_thumbnail',
                'mode'   => 'fit',
                'width'  => 485,
                'height' => 400,
            ],
        ];

        foreach ($transforms as $transform) {
            Transform::create($transform);
        }
    }

    /**
     * Create attributes.
     *
     * @return void
     */
    public function createAttributes()
    {
        $this->info('Adding Attributes');

        /** Create groups */
        $marketingGroup = AttributeGroup::forceCreate([
            'name'     => [
                'en' => 'Marketing',
            ],
            'handle'   => 'marketing',
            'position' => 1,
        ]);

        $seoGroup = AttributeGroup::forceCreate([
            'name'     => [
                'en' => 'SEO',
                'sv' => 'SEO',
            ],
            'handle'   => 'seo',
            'position' => 3,
        ]);

        /** Create attributes */
        $attributes = [
            [
                'name'       => [
                    'en' => 'Name',
                    'sv' => 'Name',
                ],
                'handle'     => 'name',
                'position'   => 1,
                'group_id'   => $marketingGroup->id,
                'required'   => true,
                'scopeable'  => 1,
                'searchable' => 1,
            ],
            [
                'name'       => [
                    'en' => 'Short Description',
                ],
                'handle'     => 'short_description',
                'position'   => 2,
                'group_id'   => $marketingGroup->id,
                'channeled'  => 1,
                'required'   => true,
                'type'       => 'richtext',
                'scopeable'  => 1,
                'searchable' => 1,
            ],
            [
                'name'       => [
                    'en' => 'Description',
                ],
                'handle'     => 'description',
                'position'   => 2,
                'group_id'   => $marketingGroup->id,
                'channeled'  => 1,
                'required'   => true,
                'type'       => 'richtext',
                'scopeable'  => 1,
                'searchable' => 1,
            ],
            [
                'name'       => [
                    'en' => 'Page Title',
                ],
                'handle'     => 'page_title',
                'position'   => 1,
                'group_id'   => $seoGroup->id,
                'channeled'  => 1,
                'required'   => false,
                'scopeable'  => 1,
                'searchable' => 1,
            ],
            [
                'name'       => [
                    'en' => 'Meta description',
                ],
                'handle'     => 'meta_description',
                'position'   => 2,
                'group_id'   => $seoGroup->id,
                'channeled'  => 1,
                'required'   => false,
                'scopeable'  => 1,
                'searchable' => 1,
                'type'       => 'textarea',
            ],
            [
                'name'       => [
                    'en' => 'Meta Keywords',
                ],
                'handle'     => 'meta_keywords',
                'position'   => 3,
                'group_id'   => $seoGroup->id,
                'channeled'  => 1,
                'required'   => false,
                'scopeable'  => 1,
                'searchable' => 1,
            ]
        ];

        foreach ($attributes as $attribute) {
            Attribute::create($attribute);
        }
    }

    /**
     * Create association groups.
     *
     * @return void
     */
    public function createAssociationGroups()
    {
        $this->info('Adding association groups');

        $associationGroups = [
            [
                'name'   => 'Upsell',
                'handle' => 'upsell',
            ],
            [
                'name'   => 'Cross-sell',
                'handle' => 'cross-sell',
            ],
            [
                'name'   => 'Alternate',
                'handle' => 'alternate',
            ],
        ];

        foreach ($associationGroups as $group) {
            AssociationGroup::forceCreate($group);
        }
    }

    /**
     * Create countries.
     *
     * @return void
     */
    public function createCountries()
    {
        $countries = json_decode(file_get_contents(__DIR__.'/../../../countries.json'), true);

        foreach ($countries as $country) {
            $name = [
                'en' => $country['name']['common'],
            ];

            foreach ($country['translations'] as $code => $data) {
                $name[$code] = $data['common'];
            }

            Country::create([
                'name'        => json_encode($name),
                'iso_a_2'     => $country['cca2'],
                'iso_a_3'     => $country['cca3'],
                'iso_numeric' => $country['ccn3'],
                'region'      => $country['region'],
                'sub_region'  => $country['subregion'],
            ]);
        }
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
