<?php

namespace GetCandy\Api\Core\Orders;

class OrderExport
{
    /**
     * The export content.
     *
     * @var string
     */
    protected $content;

    /**
     * The content format.
     *
     * @var string
     */
    protected $format;

    public function __construct($content, $format = 'csv')
    {
        $this->setContent($content);
        $this->setFormat($format);
    }

    /**
     * Set the value for format.
     *
     * @param string $format
     * @return self
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get the value for format.
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set the value for content.
     *
     * @param string $content
     * @return self
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the base encoded content.
     *
     * @return string
     */
    public function getContent()
    {
        return base64_encode($this->content);
    }
}
