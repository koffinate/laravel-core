<?php

namespace Koffin\Core\Database\Eloquent\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait GeneralScope
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $field
     * @param  string  $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByMd5(Builder $query, string $field, string $value): Builder
    {
        return $query->whereRaw("MD5(CAST({$field} AS VARCHAR)) = '{$value}'");
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $field
     * @param  string  $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByMd5Not(Builder $query, string $field, string $value): Builder
    {
        return $query->whereRaw("MD5(CAST({$field} AS VARCHAR)) <> '{$value}'");
    }
}
