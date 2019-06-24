<?php

namespace GetCandy\Api\Core\Categories;

use Kalnoy\Nestedset\QueryBuilder as KalnoyQueryBuilder;

class QueryBuilder extends KalnoyQueryBuilder
{
    /**
     * Include depth level into the result.
     *
     * @param string $as
     *
     * @return $this
     */
    public function withDepth($as = 'depth')
    {
        if ($this->query->columns === null) {
            $this->query->columns = ['*'];
        }

        $table = $this->wrappedTable();

        [$lft, $rgt] = $this->wrappedColumns();

        $alias = '_d';
        $wrappedAlias = $this->query->getGrammar()->wrapTable($alias);

        $query = $this->model
            ->newUnscopedQuery($alias)
            ->toBase()
            ->selectRaw('count(1) - 1')
            ->from($this->model->getTable().' as '.$alias)
            ->whereRaw("{$table}.{$lft} between {$wrappedAlias}.{$lft} and {$wrappedAlias}.{$rgt}");

        $this->query->selectSub($query, $as);

        return $this;
    }
}
