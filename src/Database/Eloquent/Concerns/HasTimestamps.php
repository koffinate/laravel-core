<?php

namespace Koffin\Core\Database\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps as BaseHasTimestamps;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Fluent;

trait HasTimestamps
{
    use BaseHasTimestamps;

    /**
     * Set the value of the "created at" attribute.
     *
     * @param  mixed  $value
     *
     * @return $this
     */
    public function setCreatedAt($value): static
    {
        $this->{$this->getCreatedAtColumn()} = $value;
        if (config('koffinate.core.model.use_perform_by') && ! request()->has($this->getCreatedByColumn())) {
            $this->setPerformedBy();
            $this->{$this->getCreatedByColumn()} = $this->performBy;
        }

        return $this;
    }

    /**
     * Set the value of the "updated at" attribute.
     *
     * @param  mixed  $value
     *
     * @return $this
     */
    public function setUpdatedAt($value): static
    {
        $this->{$this->getUpdatedAtColumn()} = $value;
        if (config('koffinate.core.model.use_perform_by') && ! request()->has($this->getUpdatedByColumn())) {
            $this->setPerformedBy();
            $this->{$this->getUpdatedByColumn()} = $this->performBy;
        }

        return $this;
    }

    /**
     * Get the name of the "created by" column.
     *
     * @return string
     */
    public function getCreatedByColumn(): string
    {
        return defined('static::CREATED_BY') ? static::CREATED_BY : 'created_by';
    }

    /**
     * Get the name of the "updated by" column.
     *
     * @return string
     */
    public function getUpdatedByColumn(): string
    {
        return defined('static::UPDATED_BY') ? static::UPDATED_BY : 'updated_by';
    }

    /**
     * Creator of the relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\Illuminate\Support\Fluent
     */
    public function creator(): BelongsTo|Fluent
    {
        if ($this->performerMode == 'users') {
            return $this->belongsTo(config('koffinate.core.model.users'), $this->getCreatedByColumn());
        } else {
            return $this->performerAsPlain($this->getCreatedByColumn());
        }
    }

    /**
     * Updater of the relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\Illuminate\Support\Fluent
     */
    public function updater(): BelongsTo|Fluent
    {
        if ($this->performerMode == 'users') {
            return $this->belongsTo(config('koffinate.core.model.users'), $this->getUpdatedByColumn());
        } else {
            return $this->performerAsPlain($this->getUpdatedByColumn());
        }
    }
}
