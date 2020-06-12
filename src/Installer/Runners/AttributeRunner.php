<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;

class AttributeRunner extends AbstractRunner implements InstallRunnerContract
{
    public function run()
    {
        if (! DB::table('attribute_groups')->whereHandle('marketing')->exists()) {
            $marketingGroupId = $this->addMarketingGroup();
        } else {
            $marketingGroupId = DB::table('attribute_groups')->select('id')->whereHandle('marketing')->first()->id;
        }

        if (! DB::table('attribute_groups')->whereHandle('seo')->exists()) {
            $seoGroupId = $this->addSeoGroup();
        } else {
            $seoGroupId = DB::table('attribute_groups')->whereHandle('seo')->select('id')->first()->id;
        }

        if (! DB::table('attributes')->where('group_id', '=', $marketingGroupId)->count()) {
            $this->addMarketingAttributes($marketingGroupId);
        }

        if (! DB::table('attributes')->where('group_id', '=', $seoGroupId)->count()) {
            $this->addSeoAttributes($seoGroupId);
        }
    }

    /**
     * Add the SEO attribute group.
     *
     * @return int
     */
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

    /**
     * Add the marketing attribute group.
     *
     * @return int
     */
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

    /**
     * Add the SEO attributes.
     *
     * @param  int  $groupId
     * @return void
     */
    protected function addSeoAttributes($groupId)
    {
        DB::table('attributes')->insert(
            $this->getSeoAttributes($groupId)
        );
    }

    /**
     * Add the marketing attributes.
     *
     * @param  int  $groupId
     * @return void
     */
    protected function addMarketingAttributes($groupId)
    {
        DB::table('attributes')->insert(
            $this->getMarketingAttributes($groupId)
        );
    }

    /**
     * Get the marketing attributes.
     *
     * @param  int  $groupId
     * @return array
     */
    public function getMarketingAttributes($groupId)
    {
        return [
            [
                'name' => json_encode(['en' => 'Name']),
                'handle' => 'name',
                'position' => 1,
                'group_id' => $groupId,
                'required' => true,
                'scopeable' => 1,
                'searchable' => 1,
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode(['en' => 'Short Description']),
                'handle' => 'short_description',
                'position' => 2,
                'group_id' => $groupId,
                'required' => false,
                'scopeable' => 1,
                'searchable' => 1,
                'type' => 'textarea',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode(['en' => 'Description']),
                'handle' => 'description',
                'position' => 3,
                'group_id' => $groupId,
                'required' => false,
                'scopeable' => 1,
                'searchable' => 1,
                'type' => 'richtext',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
    }

    /**
     * Get the SEO attributes to be installed.
     *
     * @param  int  $groupId
     * @return array
     */
    public function getSeoAttributes($groupId)
    {
        return [
            [
                'name' => json_encode(['en' => 'Page Title']),
                'handle' => 'page_title',
                'position' => 1,
                'group_id' => $groupId,
                'required' => true,
                'scopeable' => 1,
                'searchable' => 1,
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode(['en' => 'Meta description']),
                'handle' => 'meta_description',
                'position' => 2,
                'group_id' => $groupId,
                'required' => false,
                'scopeable' => 1,
                'searchable' => 1,
                'type' => 'textarea',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode(['en' => 'Meta Keywords']),
                'handle' => 'meta_keywords',
                'position' => 3,
                'group_id' => $groupId,
                'required' => false,
                'scopeable' => 1,
                'searchable' => 1,
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
    }
}
