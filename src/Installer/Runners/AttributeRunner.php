<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;

class AttributeRunner implements InstallRunnerContract
{
    public function run()
    {
        if (!DB::table('attribute_groups')->whereHandle('marketing')->exists()) {
            $marketingGroupId = $this->addMarketingGroup();
        } else {
            $marketingGroupId = DB::table('attribute_groups')->select('id')->whereHandle('marketing')->first()->id;
        }

        if (!DB::table('attribute_groups')->whereHandle('seo')->exists()) {
            $seoGroupId = $this->addSeoGroup();
        } else {
            $seoGroupId = DB::table('attribute_groups')->whereHandle('seo')->select('id')->first()->id;
        }

        if (!DB::table('attributes')->where('group_id', '=', $marketingGroupId)->count()) {
            $this->addMarketingAttributes($marketingGroupId);
        }

        if (!DB::table('attributes')->where('group_id', '=', $seoGroupId)->count()) {
            $this->addSeoAttributes($seoGroupId);
        }
        // $attribute = new Attribute();
        // $attribute->name = ['en' => 'Name'];
        // $attribute->handle = 'name';
        // $attribute->position = 1;
        // $attribute->group_id = $group->id;
        // $attribute->required = true;
        // $attribute->scopeable = 1;
        // $attribute->searchable = 1;
        // $attribute->save();

        // $attribute = new Attribute();
        // $attribute->name = ['en' => 'Short Description'];
        // $attribute->handle = 'short_description';
        // $attribute->position = 2;
        // $attribute->group_id = $group->id;
        // $attribute->channeled = 1;
        // $attribute->required = true;
        // $attribute->type = 'richtext';
        // $attribute->scopeable = 1;
        // $attribute->searchable = 1;
        // $attribute->save();

        // $attribute = new Attribute();
        // $attribute->name = ['en' => 'Description'];
        // $attribute->handle = 'description';
        // $attribute->position = 2;
        // $attribute->group_id = $group->id;
        // $attribute->channeled = 1;
        // $attribute->required = true;
        // $attribute->type = 'richtext';
        // $attribute->scopeable = 1;
        // $attribute->searchable = 1;
        // $attribute->save();

        // // $group = AttributeGroup::create([
        // //     'name' => ['en' => 'General', 'sv' => 'Allmän'],
        // //     'handle' => 'general',
        // //     'position' => 2
        // // ]);

        // $group = AttributeGroup::forceCreate([
        //     'name' => ['en' => 'SEO', 'sv' => 'SEO'],
        //     'handle' => 'seo',
        //     'position' => 3,
        // ]);

        // $attribute = new Attribute();
        // $attribute->name = ['en' => 'Page Title'];
        // $attribute->handle = 'page_title';
        // $attribute->position = 1;
        // $attribute->group_id = $group->id;
        // $attribute->channeled = 1;
        // $attribute->required = false;
        // $attribute->scopeable = 1;
        // $attribute->searchable = 1;
        // $attribute->save();

        // $attribute = new Attribute();
        // $attribute->name = ['en' => 'Meta description'];
        // $attribute->handle = 'meta_description';
        // $attribute->position = 2;
        // $attribute->group_id = $group->id;
        // $attribute->channeled = 1;
        // $attribute->required = false;
        // $attribute->scopeable = 1;
        // $attribute->searchable = 1;
        // $attribute->type = 'textarea';
        // $attribute->save();

        // $attribute = new Attribute();
        // $attribute->name = ['en' => 'Meta Keywords'];
        // $attribute->handle = 'meta_keywords';
        // $attribute->position = 3;
        // $attribute->group_id = $group->id;
        // $attribute->channeled = 1;
        // $attribute->required = false;
        // $attribute->scopeable = 1;
        // $attribute->searchable = 1;
        // $attribute->save();


    }

    protected function addSeoAttributes($groupId)
    {

    }

    protected function addMarketingAttributes($groupId)
    {
        dd(1);
        $attributes = [
            [
                'name' => json_encode(['en' => 'Name']),
                '' => ''
            ]
        ];
    }

    protected function addSeoGroup()
    {
        return DB::table('attribute_groups')->insertGetId([
            'name' => json_encode(['en' => 'SEO']),
            'handle' => 'seo',
            'position' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function addMarketingGroup()
    {
        return DB::table('attribute_groups')->insertGetId([
            'name' => json_encode(['en' => 'Marketing']),
            'handle' => 'marketing',
            'position' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
