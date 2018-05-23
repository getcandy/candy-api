<?php

namespace Seeds;

use Illuminate\Database\Seeder;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;

class AttributesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $group = AttributeGroup::create([
            'name' => ['en' => 'Marketing', 'sv' => 'Marknadsförande'],
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
        $attribute->name = ['en' => 'Short Description', 'sv' => 'Beskrivning'];
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
        $attribute->name = ['en' => 'Description', 'sv' => 'Beskrivning'];
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

        $group = AttributeGroup::create([
            'name' => ['en' => 'SEO', 'sv' => 'SEO'],
            'handle' => 'seo',
            'position' => 3,
        ]);

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Page Title', 'sv' => 'Titre de la page'];
        $attribute->handle = 'page_title';
        $attribute->position = 1;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = false;
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Meta description', 'sv' => 'Meta description'];
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
        $attribute->name = ['en' => 'Meta Keywords', 'sv' => 'Titre de la page'];
        $attribute->handle = 'meta_keywords';
        $attribute->position = 3;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = false;
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();
    }
}
