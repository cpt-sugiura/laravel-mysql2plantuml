<?php


namespace Mysql2PlantUml\App\Models\Eloquents;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Class InformationSchemaTable
 *
 * @package App\Models
 * @mixin Builder
 * @property string $TABLE_CATALOG
 * @property string $TABLE_SCHEMA
 * @property string $TABLE_NAME
 * @property string $COLUMN_NAME
 * @property int $ORDINAL_POSITION
 * @property string|null $COLUMN_DEFAULT
 * @property string $IS_NULLABLE
 * @property string $DATA_TYPE
 * @property int|null $CHARACTER_MAXIMUM_LENGTH
 * @property int|null $CHARACTER_OCTET_LENGTH
 * @property int|null $NUMERIC_PRECISION
 * @property int|null $NUMERIC_SCALE
 * @property int|null $DATETIME_PRECISION
 * @property string|null $CHARACTER_SET_NAME
 * @property string|null $COLLATION_NAME
 * @property string $COLUMN_TYPE
 * @property string $COLUMN_KEY
 * @property string $EXTRA
 * @property string $PRIVILEGES
 * @property string $COLUMN_COMMENT
 * @property string $GENERATION_EXPRESSION
 * @method static Builder|InformationSchemaColumn newModelQuery()
 * @method static Builder|InformationSchemaColumn newQuery()
 * @method static Builder|InformationSchemaColumn query()
 */
class InformationSchemaColumn extends Model
{
    protected $connection = 'mysql_information_schema';
    protected $table = 'COLUMNS';

    public function keyColumnUsages(): Collection
    {
        $keyColumnUsages = Cache::get($this->TABLE_NAME.'-keyColumnUsages')
            ?? InformationSchemaKeyColumnUsage::where('TABLE_NAME', $this->TABLE_NAME)
                ->where('TABLE_SCHEMA', $this->TABLE_SCHEMA)
                ->where('COLUMN_NAME', $this->COLUMN_NAME)
                ->get();
        Cache::driver('array')->put($this->TABLE_NAME.'-'.$this->COLUMN_NAME.'-keyColumnUsages', $keyColumnUsages);

        return $keyColumnUsages;
    }

    public function hasForeignKeyConstraint(): bool
    {
        return $this->keyColumnUsages()
            ->filter(
                static function (InformationSchemaKeyColumnUsage $keyColumnUsage) {
                    return $keyColumnUsage->REFERENCED_TABLE_NAME !== null;
                }
            )->isNotEmpty();
    }

    public function isNullable():bool
    {
        return $this->IS_NULLABLE === 'YES';
    }

    public function isNullableToString(): string
    {
        if ($this->IS_NULLABLE === 'YES') {
            return '    null';
        }

        return 'not null';
    }

    public function columnCommentToString(): string
    {
        if ($this->COLUMN_COMMENT !== '') {
            return "comment '".$this->COLUMN_COMMENT."'";
        }
        return '';
    }

    public function columnCommentWithLaravelDefault(): string
    {
        if($this->COLUMN_COMMENT){
            return $this->COLUMN_COMMENT;
        }
        switch ($this->COLUMN_NAME){
            case 'id':
                $name = 'ID';
                break;
            case 'remember_token':
                $name = 'ログイン記憶用トークン';
                break;
            case 'created_at':
                $name = '作成日時';
                break;
            case 'updated_at':
                $name = '最終更新日時';
                break;
            case 'deleted_at':
                $name = '削除日時';
                break;
            default:
                $name = '';
        }
        return  $name;
    }

    public function isPrimaryKey():bool
    {
        return $this->COLUMN_KEY === 'PRI';
    }

    public function columnKeyToPrefixString(): string
    {
        if ($this->COLUMN_KEY === 'PRI') {
            return '+';
        }
        if ($this->hasForeignKeyConstraint()) {
            return '#';
        }

        return ' ';
    }

    public function columnKeyToPostString(): string
    {
        $str = '';
        $str .= $this->COLUMN_KEY === 'PRI' ? '[PK]' : str_repeat(' ',strlen('[PK]'));
        $str .= $this->hasForeignKeyConstraint() ? '[FK]' : str_repeat(' ',strlen('[FK]'));
        $str .= $this->COLUMN_KEY === 'UNI' ? '[UK]' : str_repeat(' ',strlen('[UK]'));
        $str .= $this->COLUMN_KEY === 'MUL' ? '[MUL]' : str_repeat(' ',strlen('[MUL]'));

        return str_pad($str, 8);
    }

    public function columnDefaultToString(): string
    {
        if ($this->COLUMN_DEFAULT === null) {
            return '';
        }
        if (Str::contains($this->DATA_TYPE, 'varchar')) {
            return 'default \''.$this->COLUMN_DEFAULT.'\'';
        }

        return 'default '.$this->COLUMN_DEFAULT;
    }
}
