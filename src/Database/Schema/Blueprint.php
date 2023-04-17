<?php

namespace Koffin\Core\Database\Schema;

use Closure;
use Illuminate\Database\Schema\Blueprint as BaseBlueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\ColumnDefinition;
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
     *
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
     *
     * @return void
     */
    public function timestamps($precision = 0): void
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
     *
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function softDeletes($column = 'deleted_at', $precision = 0): ColumnDefinition
    {
        $foreignType = in_array($this->userKeyType, ['int', 'integer']) ? 'foreignId' : 'foreignUuid';

        $deletedColum = $this->timestamp($column, $precision)->nullable();
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

        return $deletedColum;
    }

    /**
     * Add the proper columns for a polymorphic table.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     *
     * @return void
     */
    public function morphs($name, $indexName = null): void
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
     *
     * @return void
     */
    public function nullableMorphs($name, $indexName = null): void
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
     *
     * @return void
     */
    public function stringMorphs(string $name, ?string $indexName = null): void
    {
        $this->string("{$name}_type");
        $this->unsignedBigInteger("{$name}_id");
        $this->uuid("{$name}_uuid");
        $this->ulid("{$name}_ulid");
        $this->string("{$name}_string");

        $this->setMorphIndex($name, $indexName);
    }

    /**
     * Add nullable columns for a polymorphic table using string IDs.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     *
     * @return void
     */
    public function nullableStringMorphs(string $name, ?string $indexName = null): void
    {
        $this->string("{$name}_type")->nullable();
        $this->unsignedBigInteger("{$name}_id")->nullable();
        $this->uuid("{$name}_uuid")->nullable();
        $this->ulid("{$name}_ulid")->nullable();
        $this->string("{$name}_string")->nullable();

        $this->setMorphIndex($name, $indexName);
    }

    protected function setMorphIndex(string $name, string $indexName = null): void
    {
        $this->index(["{$name}_type", "{$name}_id"], $indexName);
        $this->index(["{$name}_type", "{$name}_uuid"], $indexName ? "{$indexName}_uuid" : null);
        $this->index(["{$name}_type", "{$name}_ulid"], $indexName ? "{$indexName}_ulid" : null);
        $this->index(["{$name}_type", "{$name}_string"], $indexName ? "{$indexName}_string" : null);
    }
}
