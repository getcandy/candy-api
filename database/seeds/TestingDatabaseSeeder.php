<?php

namespace Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TestingDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->call(LanguageTableSeeder::class);
        $this->call(CurrencyTableSeeder::class);
        $this->call(AttributesTableSeeder::class);
        $this->call(CustomerGroupTableSeeder::class);
        $this->call(PermissionTableSeeder::class);
        $this->call(ChannelTableSeeder::class);
        $this->call(TaxTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(ProductFamilyTableSeeder::class);
        $this->call(ProductTableSeeder::class);
        $this->call(SettingTableSeeder::class);
        $this->call(ShippingTableSeeder::class);
        $this->call(DiscountTableSeeder::class);
    }
}
