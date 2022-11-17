<?php

namespace Koffin\Core\Database\Eloquent;

use Illuminate\Database\Eloquent\SoftDeletes as BaseSoftDeletes;

trait SoftDeletes
{
    use BaseSoftDeletes;

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    protected function runSoftDelete()
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        if (auth()->user() && empty($this->performBy)) {
            if ($this->performerMode == 'users') {
                $this->performBy = auth()->user()->id;
            } else {
                $this->performBy = auth()->user()->name ?? auth()->user()->username ?? auth()->user()->email ?? auth()->user()->id;
            }
        }

        $time = $this->freshTimestamp();

        $columns = [
            $this->getDeletedAtColumn() => $this->fromDateTime($time),
            $this->getDeletedByColumn() => $this->performBy,
        ];

        $this->{$this->getDeletedAtColumn()} = $time;
        $this->{$this->getDeletedByColumn()} = $this->performBy;

        if ($this->timestamps && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;
            $this->{$this->getUpdatedByColumn()} = $this->performBy;

            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
            $columns[$this->getUpdatedByColumn()] = $this->performBy;
        }

        $query->update($columns);
    }

    /**
     * Restore a soft-deleted model instance.
     *
     * @return bool|null
     */
    public function restore()
    {
        // If the restoring event does not return false, we will proceed with this
        // restore operation. Otherwise, we bail out so the developer will stop
        // the restore totally. We will clear the deleted timestamp and save.
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        if (auth()->user() && empty($this->performBy)) {
            if ($this->performerMode == 'users') {
                $this->performBy = auth()->user()->id;
            } else {
                $this->performBy = auth()->user()->name ?? auth()->user()->username ?? auth()->user()->email ?? auth()->user()->id;
            }
        }

        $this->{$this->getDeletedAtColumn()} = null;
        $this->{$this->getDeletedByColumn()} = null;
        $this->{$this->getRestoreAtColumn()} = $this->freshTimestamp();
        $this->{$this->getRestoreByColumn()} = $this->performBy;

        // Once we have saved the model, we will fire the "restored" event so this
        // developer will do anything they need to after a restore operation is
        // totally finished. Then we will return the result of the save call.
        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('restored', false);

        return $result;
    }

    /**
     * Get the name of the "deleted by" column.
     *
     * @return string
     */
    public function getDeletedByColumn()
    {
        return defined('static::DELETED_BY') ? static::DELETED_BY : 'deleted_by';
    }

    /**
     * Get the name of the "deleted by" column.
     *
     * @return string
     */
    public function getRestoreAtColumn()
    {
        return defined('static::RESTORE_AT') ? static::RESTORE_AT : 'restore_at';
    }

    /**
     * Get the name of the "deleted by" column.
     *
     * @return string
     */
    public function getRestoreByColumn()
    {
        return defined('static::RESTORE_BY') ? static::RESTORE_BY : 'restore_by';
    }

    /**
     * Get the fully qualified "deleted by" column.
     *
     * @return string
     */
    public function getQualifiedDeletedByColumn()
    {
        return $this->qualifyColumn($this->getDeletedByColumn());
    }

    /**
     * Get the fully qualified "restore at" column.
     *
     * @return string
     */
    public function getQualifiedRestoreAtColumn()
    {
        return $this->qualifyColumn($this->getRestoreAtColumn());
    }

    /**
     * Get the fully qualified "restore by" column.
     *
     * @return string
     */
    public function getQualifiedRestoreByColumn()
    {
        return $this->qualifyColumn($this->getRestoreByColumn());
    }

    public function deleter()
    {
        if ($this->performerMode == 'users') {
            return $this->belongsTo(config('yusronarifCore.model.users'), $this->getDeletedByColumn());
        } else {
            return $this->performerAsPlain($this->getDeletedByColumn());
        }
    }

    public function restorer()
    {
        if ($this->performerMode == 'users') {
            return $this->belongsTo(config('yusronarifCore.model.users'), $this->getRestoreByColumn());
        } else {
            return $this->performerAsPlain($this->getRestoreByColumn());
        }
    }
}
