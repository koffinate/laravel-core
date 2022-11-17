<?php

namespace Koffin\Core\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    public function __construct(\Illuminate\Database\Query\Builder $query)
    {
        parent::__construct($query);
    }
}
