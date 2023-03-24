<?php

namespace Koffin\Core\Database\Schema;

use Closure;
use Illuminate\Database\Schema\Blueprint as BaseBlueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;

class Blueprint extends BaseBlueprint
{
    /**
     * @var string users|plain
     */
    public string $performerMode = 'users';

    /**
     * @var string
     */
    private string $tableUser = '';
    private string $userKeyType;

    /**
     * Create a new schema blueprint.
     *
     * @param  string  $table
     * @param  \Closure|null  $callback
     * @param  string  $prefix
     * @return void
     */
    public function __construct($table, Closure $callback = null, $prefix = '')
    {
        parent::__construct($table, $callback, $prefix);

        $userModel = config('koffinate.core.model.users');
        $this->tableUser = (new $userModel)->getTable();
        $this->userKeyType = config('koffinate.core.model.user_key_type', 'int');
    }

    /**
     * Add nullable creation and update timestamps to the table.
     *
     * @param  int  $precision
     * @return void
     */
    public function timestamps($precision = 0)
    {
        $foreignType = in_array($this->userKeyType, ['int', 'integer']) ? 'foreignId' : 'foreignUuid';

        $this->timestamp('created_at', $precision)->nullable();
        if ($this->performerMode == 'users') {
            $this->{$foreignType}('created_by')->nullable()
                ->constrained($this->tableUser)->onUpdate('cascade')->onDelete('restrict');
        } else {
            $this->string('created_by', 100)->nullable();
        }

        $this->timestamp('updated_at', $precision)->nullable();
        if ($this->performerMode == 'users') {
            $this->{$foreignType}('updated_by')->nullable()
                ->constrained($this->tableUser)->onUpdate('cascade')->onDelete('restrict');
        } else {
            $this->string('updated_by', 100)->nullable();
        }
    }

    /**
     * Add a "deleted at" timestamp for the table.
     *
     * @param  string  $column
     * @param  int  $precision
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function softDeletes($column = 'deleted_at', $precision = 0)
    {
        $foreignType = in_array($this->userKeyType, ['int', 'integer']) ? 'foreignId' : 'foreignUuid';

        $this->timestamp($column, $precision)->nullable();
        if ($this->performerMode == 'users') {
            $this->{$foreignType}('deleted_by')->nullable()
                ->constrained($this->tableUser)->onUpdate('cascade')->onDelete('restrict');
        } else {
            $this->string('deleted_by', 100)->nullable();
        }

        $this->timestamp('restore_at', $precision)->nullable();
        if ($this->performerMode == 'users') {
            $this->{$foreignType}('restore_by')->nullable()
                ->constrained($this->tableUser)->onUpdate('cascade')->onDelete('restrict');
        } else {
            $this->string('restore_by', 100)->nullable();
        }
    }

    /**
     * Add the proper columns for a polymorphic table.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     */
    public function morphs($name, $indexName = null)
    {
        if (Builder::$defaultMorphKeyType === 'string') {
            $this->stringMorphs($name, $indexName);
        } else {
            parent::morphs($name, $indexName);
        }
    }

    /**
     * Add nullable columns for a polymorphic table.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     */
    public function nullableMorphs($name, $indexName = null)
    {
        if (Builder::$defaultMorphKeyType === 'string') {
            $this->nullableStringMorphs($name, $indexName);
        } else {
            parent::nullableMorphs($name, $indexName);
        }
    }

    /**
     * Add the proper columns for a polymorphic table using string IDs.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     */
    public function stringMorphs($name, $indexName = null)
    {
        $this->string("{$name}_type");

        $this->string("{$name}_id");

        $this->index(["{$name}_type", "{$name}_id"], $indexName);
    }

    /**
     * Add nullable columns for a polymorphic table using string IDs.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     */
    public function nullableStringMorphs($name, $indexName = null)
    {
        $this->string("{$name}_type")->nullable();

        $this->string("{$name}_id")->nullable();

        $this->index(["{$name}_type", "{$name}_id"], $indexName);
    }
}
