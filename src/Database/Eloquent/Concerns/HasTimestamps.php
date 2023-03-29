<?php

namespace Koffin\Core\Database\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps as BaseHasTimestamps;

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
    public function setCreatedAt($value)
    {
        if (auth()->user() && empty($this->performBy)) {
            if ($this->performerMode == 'users') {
                $this->performBy = auth()->user()->id;
            } else {
                $this->performBy = auth()->user()->name ?? auth()->user()->username ?? auth()->user()->email ?? auth()->user()->id;
            }
        }

        $this->{$this->getCreatedAtColumn()} = $value;
        $this->{$this->getCreatedByColumn()} = $this->performBy;

        return $this;
    }

    /**
     * Set the value of the "updated at" attribute.
     *
     * @param  mixed  $value
     *
     * @return $this
     */
    public function setUpdatedAt($value)
    {
        if (auth()->user() && empty($this->performBy)) {
            if ($this->performerMode == 'users') {
                $this->performBy = auth()->user()->id;
            } else {
                $this->performBy = auth()->user()->name ?? auth()->user()->username ?? auth()->user()->email ?? auth()->user()->id;
            }
        }

        $this->{$this->getUpdatedAtColumn()} = $value;
        $this->{$this->getUpdatedByColumn()} = $this->performBy;

        return $this;
    }

    /**
     * Get the name of the "created by" column.
     *
     * @return string
     */
    public function getCreatedByColumn()
    {
        return defined('static::CREATED_BY') ? static::CREATED_BY : 'created_by';
    }

    /**
     * Get the name of the "updated by" column.
     *
     * @return string
     */
    public function getUpdatedByColumn()
    {
        return defined('static::UPDATED_BY') ? static::UPDATED_BY : 'updated_by';
    }

    public function creater()
    {
        if ($this->performerMode == 'users') {
            return $this->belongsTo(config('koffinate.core.model.users'), $this->getCreatedByColumn());
        } else {
            return $this->performerAsPlain($this->getCreatedByColumn());
        }
    }

    public function updater()
    {
        if ($this->performerMode == 'users') {
            return $this->belongsTo(config('koffinate.core.model.users'), $this->getUpdatedByColumn());
        } else {
            return $this->performerAsPlain($this->getUpdatedByColumn());
        }
    }
}
