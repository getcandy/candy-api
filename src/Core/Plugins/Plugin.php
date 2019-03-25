<?php

namespace GetCandy\Api\Core\Plugins;

class Plugin
{
    protected $handle;

    protected $dir;

    protected $config;

    public function __construct($handle, $dir)
    {
        $this->handle = $handle;
        $this->dir = $dir;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    protected function getPathToResource($path = null)
    {
        return realpath($this->dir.'/resources/'.($path ?: null));
    }

    public function getJsResources()
    {
        $dir = $this->getPathToResource('js');

        if (! $dir) {
            return [];
        }

        $files = \File::allFiles($dir);

        $resources = [];

        foreach ($files as $file) {
            $filename = basename($file->getFilename(), '.js');
            if ($filename == $this->handle) {
                $resources[] = $this->handle.'/resources/js/'.$filename;
            }
        }

        return $resources;
    }

    public function getResource($type, $file)
    {
        $file = basename($file, '.js');

        return $this->getPathToResource($type.'/'.$file.'.'.$type);
    }

    public function getCssResources()
    {
        return [];
    }

    public function toArray()
    {
        dd('hi!');

        return [
            'foo' => 'bar',
        ];
    }
}
