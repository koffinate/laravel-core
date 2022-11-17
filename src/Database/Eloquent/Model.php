<?php

namespace Koffin\Core\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Facades\DB;
use Koffin\Core\Database\Eloquent\Concerns\HasTimestamps;
use Koffin\Core\Database\Eloquent\Scopes\GeneralScope;
use Koffin\Core\Support\Str;

class Model extends BaseModel
{
    use HasTimestamps, GeneralScope;

    /*
     * The list of table wich include with schema
     */
    protected string|array $fullnameTable = [];

    /**
     * @var string users|plain
     */
    protected string $performerMode = 'users';

    /**
     * Who is (user) as executor.
     */
    protected $performBy = null;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at', 'restore_at',
        'created_by', 'updated_by', 'deleted_by', 'restore_by',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $schema = DB::getDatabaseName();

        $this->fullnameTable['self'] = "{$schema}.{$this->table}";
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        if (in_array(strtolower($this->getKeyType()), ['string', 'uuid'])) {
            return false;
        }

        return $this->incrementing;
    }

    /**
     * Perform a model insert operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return bool
     */
    protected function performInsert(Builder $query)
    {
        if (in_array($keyType = strtolower($this->getKeyType()), ['string', 'uuid'])) {
            $this->setIncrementing(false);
            $this->setAttribute($this->getKeyName(), ($keyType == 'string' ? Str::uuid()->getHex() : Str::uuid())->toString());
        }

        return parent::performInsert($query);
    }

    /**
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

    /**
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
     * @param  string|null  $performer
     */
    protected function performerAsPlain(?string $performer = 'By System')
    {
        if (empty($performer)) {
            throw new RelationNotFoundException();
        }

        return DB::select(DB::raw("SELECT null AS id, '{$performer}' AS name, '{$performer}' AS username, '{$performer}' AS email"));
    }
}
