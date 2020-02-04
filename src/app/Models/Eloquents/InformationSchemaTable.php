<?php


namespace Mysql2PlantUml\App\Models\Eloquents;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mysql2PlantUml\app\Models\ValueObjects\Relation;

/**
 * Class InformationSchemaTable
 *
 * @package App\Models
 * @mixin Builder
 * @property string $TABLE_CATALOG
 * @property string $TABLE_SCHEMA
 * @property string $TABLE_NAME
 * @property string $TABLE_TYPE
 * @property string|null $ENGINE
 * @property int|null $VERSION
 * @property string|null $ROW_FORMAT
 * @property int|null $TABLE_ROWS
 * @property int|null $AVG_ROW_LENGTH
 * @property int|null $DATA_LENGTH
 * @property int|null $MAX_DATA_LENGTH
 * @property int|null $INDEX_LENGTH
 * @property int|null $DATA_FREE
 * @property int|null $AUTO_INCREMENT
 * @property string|null $CREATE_TIME
 * @property string|null $UPDATE_TIME
 * @property string|null $CHECK_TIME
 * @property string|null $TABLE_COLLATION
 * @property int|null $CHECKSUM
 * @property string|null $CREATE_OPTIONS
 * @property string $TABLE_COMMENT
 * @method static Builder|InformationSchemaTable newModelQuery()
 * @method static Builder|InformationSchemaTable newQuery()
 * @method static Builder|InformationSchemaTable query()
 */
class InformationSchemaTable extends Model
{
    protected $connection = 'mysql_information_schema';
    protected $table = 'TABLES';

    /**
     * @return Collection
     */
    public function columns(): Collection
    {
        $columns = Cache::get($this->TABLE_NAME.'-columns')
            ?? InformationSchemaColumn::where('TABLE_NAME', $this->TABLE_NAME)
                ->where('TABLE_SCHEMA', $this->TABLE_SCHEMA)
                ->orderBy('ORDINAL_POSITION')
                ->get();
        Cache::driver('array')->put($this->TABLE_NAME.'-columns', $columns);

        return $columns;
    }

    /**
     * @return Collection
     */
    public function keyColumnUsages()
    {
        $keyColumnUsages = Cache::get($this->TABLE_NAME.'-keyColumnUsages')
            ?? InformationSchemaKeyColumnUsage::where('TABLE_NAME', $this->TABLE_NAME)
                ->where('TABLE_SCHEMA', $this->TABLE_SCHEMA)
                ->get();
        Cache::driver('array')->put($this->TABLE_NAME.'-keyColumnUsages', $keyColumnUsages);

        return $keyColumnUsages;
    }

    public function relationValueObjects(): Collection
    {
        $thisTABLE_NAME = $this->TABLE_NAME;
        $relationsByConfig = collect(config('mysql2plantuml.relations'))
            ->filter(
                static function (array $item) use ($thisTABLE_NAME) {
                    return $item['from'] === $thisTABLE_NAME;
                }
            )
            ->map(
                static function (array $item) {
                    return Relation::createRelationByConfig($item);
                }
            );

        $relationsBySchema = $this->keyColumnUsages()
            ->map(
                static function (InformationSchemaKeyColumnUsage $keyColumnUsage) {
                    return $keyColumnUsage->getRelationValueObject();
                }
            )->filter(
                static function ($item) {
                    return $item !== null;
                }
            );

        return $relationsByConfig->push($relationsBySchema)->flatten()
            ->filter(
                static function (Relation $relation) use ($thisTABLE_NAME) {
                    return $relation->fromTable === $thisTABLE_NAME;
                }
            )
            ->unique(
                static function (Relation $relation) {
                    return $relation->getRelationTableSetId();
                }
            );
    }

    public function getColumnsColumnMaxLength(string $key): int
    {
        if (method_exists(InformationSchemaColumn::class, $key)) {
            return $this->columns()
                ->map(
                    static function (InformationSchemaColumn $column) use ($key) {
                        return Str::length($column->$key());
                    }
                )
                ->max();
        }
        return $this->columns()
            ->pluck($key)
            ->map(
                static function (string $name) {
                    return Str::length($name);
                }
            )
            ->max();
    }
}
