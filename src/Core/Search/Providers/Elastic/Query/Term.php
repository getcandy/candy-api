<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Query;

use Elastica\Query\Match;
use Elastica\Query\DisMax;
use Elastica\Query\Nested;
use Elastica\Query\BoolQuery;
use Elastica\Query\MultiMatch;

class Term
{
    /**
     * The query value.
     *
     * @var string
     */
    protected $text;

    /**
     * The query fields.
     *
     * @var array
     */
    protected $fields;

    public function __construct($text, $fields)
    {
        $this->text = $text;
        $this->fields = $fields;
    }

    /**
     * Get the text value.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    public function getQuery()
    {

        $disMaxQuery = new DisMax;
        $disMaxQuery->setBoost(1.5);
        $disMaxQuery->setTieBreaker(1);

        if (!empty($this->fields['multi_match'])) {

            $multiMatch = $this->fields['multi_match'] ?? [];

            $prev = null;

            foreach ($multiMatch['types'] ?? [] as $type => $fields) {
                if ($prev && is_string($fields)) {
                    $fields = $prev;
                }
                $multiMatchQuery = new MultiMatch;
                $multiMatchQuery->setType($type);
                $multiMatchQuery->setQuery($this->text);
                $multiMatchQuery->setFields($fields);
                $disMaxQuery->addQuery($multiMatchQuery);
                if (is_array($fields)) {
                    $prev = $fields;
                }
            }

            $nested = $this->fields['nested'] ?? [];

            foreach ($nested as $path => $fields) {

                $nestedQuery = new Nested;
                $nestedQuery->setPath($path);
                $bool = new BoolQuery;

                $fields = array_map(function ($field) use ($path) {
                    return $path . '.' . $field;
                }, $fields);

                $match = new MultiMatch;
                $match->setType('phrase');
                $match->setQuery($this->text);
                $match->setFields(
                    $fields
                );
                $bool->addMust($match);

                $nestedQuery->setQuery($bool);

                $disMaxQuery->addQuery($nestedQuery);
            }
        }
        return $disMaxQuery;
    }
}
