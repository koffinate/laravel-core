<?php

namespace Koffin\Core\Database\Eloquent\Scopes;

use Koffin\Core\Database\Eloquent\Builder;

trait GeneralScope
{
    /**
     * @param  Builder  $query
     * @param  string  $field
     * @param $value
     *
     * @return mixed
     */
    public function scopeByMd5(Builder $query, string $field, $value)
    {
        return $query->whereRaw("MD5(CAST({$field} AS VARCHAR)) = '{$value}'");
    }

    /**
     * @param  Builder  $query
     * @param  string  $field
     * @param $value
     *
     * @return Builder
     */
    public function scopeByMd5Not(Builder $query, string $field, $value)
    {
        return $query->whereRaw("MD5(CAST({$field} AS VARCHAR)) <> '{$value}'");
    }
}
