<?php

namespace Koffin\Core\Database\Eloquent;

trait ModelCustom
{
    /**
     * @var string
     * @value int|string
     */
    protected $primaryValue = 'int';

    /**
     * if incrementing method is manually.
     *
     * @var bool
     */
    protected $autoIncrementing = false;

    protected function getPrimaryValue()
    {
        if ($this->primaryValue === 'int') {
            if ($this->autoIncrementing == true) {
                return $this->select(DB::Raw("nextval('{$this->table}_{$this->primaryKey}_seq'::regclass)"))->first()->nextval;
            } else {
                return $this->max($this->primaryKey) + 1;
            }
        }

        if ($this->primaryValue === 'string') {
            return md5(auth()->user()->id.'::'.config('perbanas.app.codename').'::'.microtime());
        }
    }

    protected function setPrimaryValue()
    {
        if (! $this->exists) {
            $this->attributes[$this->primaryKey] = $this->getPrimaryValue();
        }
    }
}
