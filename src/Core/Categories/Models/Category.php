<?php

namespace GetCandy\Api\Core\Categories\Models;

use GetCandy\Api\Core\Categories\QueryBuilder;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\Assetable;
use GetCandy\Api\Core\Traits\HasAttributes;
use GetCandy\Api\Core\Traits\HasChannels;
use GetCandy\Api\Core\Traits\HasCustomerGroups;
use GetCandy\Api\Core\Traits\HasLayouts;
use GetCandy\Api\Core\Traits\HasRoutes;
use Kalnoy\Nestedset\NodeTrait;
use NeonDigital\Drafting\Draftable;
use NeonDigital\Versioning\Versionable;

class Category extends BaseModel
{
    use NodeTrait,
        HasAttributes,
        HasLayouts,
        Assetable,
        HasChannels,
        HasRoutes,
        HasCustomerGroups,
        Draftable,
        Versionable;

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'main';

    /**
     * @var string
     */
    protected $settings = 'categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'attribute_data', 'parent_id',
    ];

    public function getParentIdAttribute($val)
    {
        return $val;
    }

    public function hasChildren()
    {
        return (bool) $this->children()->count();
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->withoutGlobalScopes()->defaultOrder();
    }

    public function parent()
    {
        return $this->belongsTo($this, 'parent_id')->withoutGlobalScopes();
    }

    public function getProductCount()
    {
        return $this->belongsToMany(Product::class, 'product_categories')->count();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories')
            ->withPivot('position')
            ->orderBy('product_categories.position', 'asc');
    }

    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'category_channel')->withPivot('published_at');
    }

    /**
     * @param  null|string $table
     * @return \GetCandy\Api\Core\Categories\QueryBuilder
     */
    public function newUnscopedQuery($table = null)
    {
        return $this->applyNestedSetScope($this->newQuery()->withoutGlobalScopes(), $table);
    }

    /**
     * Set the value of model's parent id key.
     * Behind the scenes node is appended to found parent node.
     *
     * @param  int  $value
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function setParentIdAttribute($value)
    {
        if ($this->getParentId() == $value) {
            return;
        }

        if ($value) {
            $this->appendToNode($this->newScopedQuery()->withDrafted()->findOrFail($value));
        } else {
            $this->makeRoot();
        }
    }

    /**
     * Get a new base query that includes deleted nodes.
     * @since 1.1
     *
     * @param  null|string $table
     * @return \GetCandy\Api\Core\Categories\QueryBuilder
     */
    public function newNestedSetQuery($table = null)
    {
        $builder = $this->usesSoftDelete()
            ? $this->withTrashed()
            : $this->newQuery();

        return $this->applyNestedSetScope($builder, $table);
    }

    /**
     * Call pending action.
     *
     * @return void
     */
    protected function callPendingAction()
    {
        // If we're drafting we don't want to do any nested set stuff.
        if ($this->isDraft()) {
            return;
        }
        $this->moved = false;

        if (! $this->pending && ! $this->exists) {
            $this->makeRoot();
        }

        if (! $this->pending) {
            return;
        }

        $method = 'action'.ucfirst(array_shift($this->pending));
        $parameters = $this->pending;

        $this->pending = null;

        $this->moved = call_user_func_array([$this, $method], $parameters);
    }

    protected function deleteDescendants()
    {
        if ($this->isDraft()) {
            return;
        }
        $lft = $this->getLft();
        $rgt = $this->getRgt();

        $method = $this->usesSoftDelete() && $this->forceDeleting
            ? 'forceDelete'
            : 'delete';

        $this->descendants()->{$method}();

        if ($this->hardDeleting()) {
            $height = $rgt - $lft + 1;

            $this->newNestedSetQuery()->makeGap($rgt + 1, -$height);

            // In case if user wants to re-create the node
            $this->makeRoot();

            static::$actionsPerformed++;
        }
    }

    /**
     * We use our own QueryBuilder here as withDepth was causing
     * a serious query issue when looking through category channels.
     * @since 2.0
     *
     * @return \GetCandy\Api\Core\Categories\QueryBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new QueryBuilder($query);
    }
}
