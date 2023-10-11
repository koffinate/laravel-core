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
        $this->timestamp('created_at', $precision)->nullable();
        $this->makePerformerColumn('created_by');

        $this->timestamp('updated_at', $precision)->nullable();
        $this->makePerformerColumn('updated_by');
    }

    /**
     * Add creation and update timestampTz columns to the table.
     *
     * @param  int|null  $precision
     *
     * @return void
     */
    public function timestampsTz($precision = 0): void
    {
        $this->timestampTz('created_at', $precision)->nullable();
        $this->makePerformerColumn('created_by');

        $this->timestampTz('updated_at', $precision)->nullable();
        $this->makePerformerColumn('updated_by');
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
        $deletedByColumn = str($column)->replaceMatches('/_at$/i', '')->append('_by')->toString();
        $deletedColum = $this->timestamp($column, $precision)->nullable();
        $this->makePerformerColumn($deletedByColumn);

        $this->timestamp('restore_at', $precision)->nullable();
        $this->makePerformerColumn('restore_by');

        return $deletedColum;
    }

    /**
     * Add a "deleted at" timestampTz for the table.
     *
     * @param  string  $column
     * @param  int|null  $precision
     *
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function softDeletesTz($column = 'deleted_at', $precision = 0): ColumnDefinition
    {
        $deletedByColumn = str($column)->replaceMatches('/_at$/i', '')->append('_by')->toString();
        $deletedColum = $this->timestampTz($column, $precision)->nullable();
        $this->makePerformerColumn($deletedByColumn);

        $this->timestampTz('restore_at', $precision)->nullable();
        $this->makePerformerColumn('restore_by');

        return $deletedColum;
    }

    private function makePerformerColumn(string $column): void
    {
        if (config('koffinate.core.model.use_perform_by')) {
            if ($this->performerMode == 'users') {
                $foreignType = in_array($this->userKeyType, ['int', 'integer']) ? 'foreignId' : 'foreignUuid';
                $this->{$foreignType}($column)->nullable()
                    ->constrained($this->tableUser)->onUpdate('cascade')->onDelete('restrict');
            } else {
                $this->string($column, 100)->nullable();
            }
        }
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

            return;
        }

        if (Builder::$defaultMorphKeyType === 'any') {
            $this->anyMorphs($name, $indexName);

            return;
        }

        parent::morphs($name, $indexName);
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

            return;
        }

        if (Builder::$defaultMorphKeyType === 'any') {
            $this->nullableAnyMorphs($name, $indexName);

            return;
        }

        parent::nullableMorphs($name, $indexName);
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
        $this->string("{$name}_string");

        $this->setMorphIndex($name, $indexName, ['string']);
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
        $this->string("{$name}_string")->nullable();

        $this->setMorphIndex($name, $indexName, ['string']);
    }

    /**
     * Add the proper columns for a polymorphic table using all type of IDs.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     *
     * @return void
     */
    public function anyMorphs(string $name, ?string $indexName = null): void
    {
        $this->string("{$name}_type");
        $this->unsignedBigInteger("{$name}_id")->nullable();
        $this->uuid("{$name}_uuid")->nullable();
        $this->ulid("{$name}_ulid")->nullable();
        $this->string("{$name}_string")->nullable();

        $this->setMorphIndex($name, $indexName);
    }

    /**
     * Add nullable columns for a polymorphic table using all type of IDs.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     *
     * @return void
     */
    public function nullableAnyMorphs(string $name, ?string $indexName = null): void
    {
        $this->string("{$name}_type")->nullable();
        $this->unsignedBigInteger("{$name}_id")->nullable();
        $this->uuid("{$name}_uuid")->nullable();
        $this->ulid("{$name}_ulid")->nullable();
        $this->string("{$name}_string")->nullable();

        $this->setMorphIndex($name, $indexName);
    }

    protected function setMorphIndex(string $name, string $indexName = null, array $types = []): void
    {
        if (empty($types)) {
            $types = ['numeric', 'uuid', 'ulid', 'string'];
        }

        foreach ($types as $type) {
            $columnName = "{$name}_".($type === 'numeric' ? 'id' : $type);
            $currentIndexName = null;
            if ($indexName) {
                $currentIndexName = $indexName.($type === 'numeric' ? '' : "_{$type}");
            }

            $this->index(["{$name}_type", $columnName], $currentIndexName);
        }
    }
}
