<?php

namespace GetCandy\Api\Core\Assets\Drivers;

class YouTube extends BaseUrlDriver
{
    /**
     * @var Alaouy\Youtube\Youtube
     */
    protected $manager;

    /**
     * @var string
     */
    protected $handle = 'youtube';

    /**
     * @var string
     */
    protected $oemUrl = 'https://www.youtube.com/oembed';

    public function __construct()
    {
        $this->manager = app('youtube');
    }

    /**
     * Get the video unique id.
     *
     * @param string $url
     *
     * @return string
     */
    public function getUniqueId($url)
    {
        return $this->manager->parseVidFromURL($url);
    }

    /**
     * Get the video info.
     *
     * @param string $url
     *
     * @return array
     */
    public function getInfo($url)
    {
        return $this->info = $this->getOemData([
            'format' => 'json',
            'url' => $url,
        ]);
    }
}
