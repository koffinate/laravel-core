<?php

namespace Koffin\Core\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Koffin\Core\Database\Eloquent\Concerns\HasTimestamps;
use Koffin\Core\Database\Eloquent\Scopes\GeneralScope;
use Koffin\Core\Foundation\Auth\User;

class Model extends BaseModel
{
    use HasTimestamps, GeneralScope;

    /**
     * Set table alias on query builder
     *
     * @var string|null
     */
    public static string|null $tableAlias = null;

    /**
     * The list of table wich include with schema.
     */
    protected string|array $fullnameTable = [];

    /**
     * @var string users|plain
     */
    protected string $performerMode = 'users';

    /**
     * Who is (user) as executor.
     */
    protected ?string $performBy = null;

    /**
     * Use tryFrom method on eloquent cast from enum.
     *
     * @var bool
     */
    protected bool $useTryOnEnumCast = true;

    /** @inheritdoc */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $schema = DB::getDatabaseName();

        $this->fullnameTable['self'] = "{$schema}.{$this->table}";
    }

    /** @inheritdoc */
    public function getIncrementing()
    {
        if (in_array(strtolower($this->getKeyType()), ['string', 'uuid'])) {
            return false;
        }

        return $this->incrementing;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public static function getFillableAttribute(array $data): array
    {
        $fillable = (new static)->getFillable();

        return Arr::only($data, Arr::flatten($fillable));
    }

    /** @inheritdoc */
    public function newQuery()
    {
        $query = parent::newQuery();

        if (static::$tableAlias) {
            return $query->from($this->getTable(), static::$tableAlias);
        }
        return $query;
    }

    /** @inheritdoc */
    public function qualifyColumn($column)
    {
        if (static::$tableAlias) {
            $column = static::$tableAlias . '.' . $column;
        }
        return parent::qualifyColumn($column);
    }

    /** @inheritdoc */
    protected function performInsert(Builder $query)
    {
        if (in_array($keyType = strtolower($this->getKeyType()), ['string', 'uuid', 'ulid'])) {
            $this->setIncrementing(false);
            $keyValue = match ($keyType) {
                'string' => Str::orderedUuid()->getHex(),
                'uuid' => Str::orderedUuid()->toString(),
                'ulid' => Str::ulid()->toBase32(),
            };
            $this->setAttribute($this->getKeyName(), $keyValue);
        }

        return parent::performInsert($query);
    }

    /** @inheritDoc */
    protected function getEnumCaseFromValue($enumClass, $value)
    {
        if (! $this->useTryOnEnumCast) {
            return parent::getEnumCaseFromValue($enumClass, $value);
        }

        return is_subclass_of($enumClass, \BackedEnum::class)
            ? $enumClass::tryFrom($value)
            : constant($enumClass.'::'.$value);
    }

    /*/**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    /*protected function setKeysForSaveQuery(Builder $query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }*/

    /*/**
     * Get the primary key value for a save query.
     *
     * @return mixed
     */
    /*protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }*/

    /**
     * generate performer from plain performer.
     *
     * @param string|null $performer
     *
     * @return \Illuminate\Support\Fluent
     */
    protected function performerAsPlain(?string $performer = null): Fluent
    {
        if (empty($performer)) {
            // throw new RelationNotFoundException();
            $performer = 'By System';
        }

        $id = 0;
        if (config('koffinate.core.model.users')->getKeyType() != 'int') {
            $id = "'00000000-0000-0000-0000-000000000000'";
        }
        $performer = Str::of($performer)->trim();
        $username = $performer->slug()->toString();
        $email = "$username@".config('koffinate.core.fake_mail_domain');

        $select = "SELECT $id AS id, '{$performer->toString()}' AS name, '$username' AS username, '$email' AS email";

        return new Fluent(DB::selectOne($select));
    }

    /**
     * set performer from performer.
     *
     * @return void
     */
    protected function setPerformedBy(): void
    {
        // reset performer
        $this->performBy = null;

        if (auth()->check() && config('koffinate.core.model.use_perform_by') && empty($this->performBy)) {
            $user = auth()->user();
            if ($user instanceof User) {
                if ($this->performerMode == 'users') {
                    $this->performBy = $user->id;
                } else {
                    $this->performBy = $user->name ?? $user->username ?? $user->email ?? $user->id;
                }
            }
        }
    }
}
