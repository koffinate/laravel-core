<?php

namespace Koffin\Core\Database\Migrations;

use Illuminate\Database\Migrations\Migration as BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Koffin\Core\Support\Str;

class Migration extends BaseMigration
{
    protected $table;

    /**
     * @param  Blueprint  $table
     * @param $fields
     * @return bool|Blueprint
     */
    protected function setFields(Blueprint $table, $fields)
    {
        if (is_array($fields) && count($fields) > 0) {
            foreach ($fields as $field => $attr) {
                if (! Schema::connection($this->connection)->hasColumn($this->table, $field)) {
                    $type = $attr['type'] ?: 'string';
                    $type = preg_replace('/:+/i', ',', $type);

                    $t = $table->{$type}($field)->nullable($attr['null'] ?: false);
                    if (isset($attr['default'])) {
                        $t->default($attr['default']);
                    }
                    if (isset($attr['comment'])) {
                        $t->comment($attr['comment']);
                    }
                }
            }

            return $table;
        }

        return false;
    }

    /**
     * @param  Blueprint  $table
     * @param  array  $foreigns
     * @return bool|Blueprint
     */
    protected function setForeigns(Blueprint $table, $foreigns = [])
    {
        if (is_array($foreigns) && count($foreigns) > 0) {
            $currentTable = config('database.current_schema').'.'.$this->table;

            $prefix = Str::forceSnake($currentTable);
            $sm = Schema::connection($this->connection)->getConnection()->getDoctrineSchemaManager();
            $fkeys = array_map(function ($a) {
                return $a->getName();
            }, $sm->listTableForeignKeys($this->table));

            foreach ($foreigns as $tbl => $tblAttr) {
                foreach ($tblAttr as $key => $ref) {
                    if (! is_array($ref)) {
                        $vals['reference'] = $ref;
                    } else {
                        $vals = $ref;
                    }

                    $pkey = "{$prefix}_{$key}_foreign";
                    if (! in_array($pkey, $fkeys)) {
                        $table->foreign($key)->references($vals['reference'])->on($tbl)
                            ->onUpdate($vals['onUpdate'] ?: 'cascade')->onDelete($vals['onDelete'] ?: 'restrict');
                    }
                }
            }

            return $table;
        }

        return false;
    }
}
