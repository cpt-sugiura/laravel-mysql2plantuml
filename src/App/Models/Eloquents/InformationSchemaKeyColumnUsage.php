<?php


namespace Mysql2PlantUml\App\Models\Eloquents;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mysql2PlantUml\App\Models\ValueObjects\Relation;

/**
 * App\Models\InformationSchemaKeyColumnUsage
 *
 * @property string $CONSTRAINT_CATALOG
 * @property string $CONSTRAINT_SCHEMA
 * @property string $CONSTRAINT_NAME
 * @property string $TABLE_CATALOG
 * @property string $TABLE_SCHEMA
 * @property string $TABLE_NAME
 * @property string $COLUMN_NAME
 * @property int $ORDINAL_POSITION
 * @property int|null $POSITION_IN_UNIQUE_CONSTRAINT
 * @property string|null $REFERENCED_TABLE_SCHEMA
 * @property string|null $REFERENCED_TABLE_NAME
 * @property string|null $REFERENCED_COLUMN_NAME
 * @method static Builder|InformationSchemaKeyColumnUsage newModelQuery()
 * @method static Builder|InformationSchemaKeyColumnUsage newQuery()
 * @method static Builder|InformationSchemaKeyColumnUsage query()
 */
class InformationSchemaKeyColumnUsage extends Model
{
    protected $connection = 'mysql_information_schema';
    protected $table = 'KEY_COLUMN_USAGE';


    /**
     * @return Relation
     * @throws \Mysql2PlantUml\App\Exceptions\InvalidArgsException
     */
    public function getRelationValueObject(): ?Relation
    {
        if ($this->TABLE_NAME === null || $this->REFERENCED_TABLE_NAME === null) {
            return null;
        }
        return new Relation(
            $this->TABLE_NAME,
            $this->REFERENCED_TABLE_NAME
        );
    }

}
