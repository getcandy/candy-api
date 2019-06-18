<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Query;

use Elastica\Query\DisMax;
use Elastica\Query\MultiMatch;
use Elastica\Query\Wildcard;

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

        if (! empty($this->fields['multi_match'])) {
            $multiMatch = $this->fields['multi_match'] ?? [];

            $prev = null;

            foreach ($multiMatch['types'] ?? [] as $type => $fields) {
                if ($prev && is_string($fields)) {
                    $fields = $prev;
                }
                $multiMatchQuery = new MultiMatch;
                $multiMatchQuery->setType($type);
                $multiMatchQuery->setQuery($this->text);
                $multiMatchQuery->setOperator('and');
                $multiMatchQuery->setFields($fields);
                $disMaxQuery->addQuery($multiMatchQuery);
                if (is_array($fields)) {
                    $prev = $fields;
                }
            }
        }

        $skuTerm = strtolower($this->text);
        $wildcard = new Wildcard('sku.lowercase', "*{$skuTerm}*");
        $disMaxQuery->addQuery($wildcard);

        return $disMaxQuery;
    }
}
