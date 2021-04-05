<?php

namespace Chatter\Core\Models;

use Chatter\Core\Models\Discussion;
use Illuminate\Database\Eloquent\Model;
use Chatter\Core\Traits\Multitenant;

class Category extends Model implements CategoryInterface
{
    use Multitenant;
    protected $table = 'chatter_categories';
    public $timestamps = true;
    public $with = 'parents';

    public function discussions()
    {
        return $this->hasMany(get_class(app(DiscussionInterface::class)), 'category_id');
    }

    public function parents()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order', 'asc');
    }
}
