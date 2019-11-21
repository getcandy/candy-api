<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ChannelRunner extends AbstractRunner implements InstallRunnerContract
{
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function run()
    {
        if (DB::table('channels')->count()) {
            return;
        }

        $channel = $this->command->anticipate('Choose a new channel name e.g. webstore', ['webstore']);
        $channelUrl = $this->command->ask('Whats the storefront URL this channel points to? (leave blank if unsure)');

        DB::table('channels')->insert([
            'name' => $channel,
            'handle' => Str::slug($channel),
            'url' => $channelUrl ?: url('/'),
            'default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
