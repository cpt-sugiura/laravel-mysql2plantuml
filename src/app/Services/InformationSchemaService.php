<?php


namespace Mysql2PlantUml\app\Services;


use Illuminate\Database\Eloquent\Collection;
use Mysql2PlantUml\App\Models\Eloquents\InformationSchemaTable;

class InformationSchemaService
{

    /**
     * パッケージに分割して格納されたTABLEモデル達のコレクションが返される.
     * パッケージ定義が一切存在しない場合はスキーマでひとくくり.
     * パッケージに定義されていないテーブルはコメントないし名前でパッケージ化.
     * @param  array<array<string>>|null  $packageDefines
     * @return Collection|InformationSchemaTable[]
     */
    public static function getPackagedSchema($packageDefines = null)
    {
        return InformationSchemaTable::where('TABLE_SCHEMA', config('mysql2plantuml.target_database'))
            ->get()
            ->filter(
                static function (InformationSchemaTable $table) {
                    $withoutTableNames = config('mysql2plantuml.without_tables') ?: [];
                    return !in_array($table->TABLE_NAME, $withoutTableNames, true);
                }
            )
            ->mapToGroups(
                static function (InformationSchemaTable $table) use ($packageDefines) {
                    if ($packageDefines === null || $packageDefines === []) {
                        return [$table->TABLE_SCHEMA => $table];
                    }
                    $packageName = collect($packageDefines)
                        ->filter(
                            static function (array $package) use ($table) {
                                return in_array($table->TABLE_NAME, $package, true);
                            }
                        )->keys()->first();
                    return [$packageName ?? ($table->TABLE_COMMENT ?: $table->TABLE_NAME) => $table];
                }
            );
    }
}
