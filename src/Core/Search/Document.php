<?php

namespace GetCandy\Api\Core\Search;

class Document
{
    protected $data = [];

    protected $id;

    public $lang;

    public function __get($attribute)
    {
        if (isset($this->data[$attribute])) {
            return $this->data[$attribute];
        }
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Adds an items to an array.
     *
     * @param  string  $key
     * @param  string  $value
     * @return $this
     */
    public function add($key, $value)
    {
        if (empty($this->data[$key])) {
            $this->set($key, $value);
        }
        $current = $this->data[$key];
        if (! is_array($current)) {
            $this->data[$key] = [];
        }
        array_push($this->data[$key], $value);

        return $this;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }
}
