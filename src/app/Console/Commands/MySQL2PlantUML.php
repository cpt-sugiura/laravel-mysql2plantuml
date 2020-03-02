<?php

namespace Mysql2PlantUml\App\Console\Commands;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\View;
use Mysql2PlantUml\app\Exceptions\NotCreateDirException;
use Mysql2PlantUml\App\Models\Eloquents\InformationSchemaTable;
use Illuminate\Console\Command;
use Mysql2PlantUml\app\Models\ValueObjects\Relation;

class MySQL2PlantUML extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dump:mysql2puml';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'MySQL中に既存のデータベースを元にER図を出力します。';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $packageDefines = config('mysql2plantuml.packages');
        $packages = $this->getPackagedSchema($packageDefines);
        $relationsByConfig = collect(config('mysql2plantuml.relations'))
            ->map(
                static function (array $item) {
                    return Relation::createRelationByConfig($item);
                }
            ) ?: collect();

        $bladePath = __DIR__.'/../../../resources/views/base.blade.php';
        $freeComment = config('mysql2plantuml.free_comment');
        $view = View::file($bladePath, compact('packages', 'relationsByConfig', 'freeComment'));

        $baseDirPath = $this->PrepareDistDir();
        file_put_contents($baseDirPath.config('mysql2plantuml.target_database').'.puml', $view->render());
        $packages->each(
            static function ($package, $index) use ($baseDirPath, $relationsByConfig, $bladePath) {
                $view = View::file(
                    $bladePath,
                    ['packages' => [$index => $package], 'relationsByConfig' => $relationsByConfig]
                );
                config('mysql2plantuml.without_package_files') ?: file_put_contents($baseDirPath.$index.'.puml', $view->render());
            }
        );
    }

    /**
     * パッケージに分割して格納されたTABLEモデル達のコレクションが返される.
     * パッケージ定義が一切存在しない場合はスキーマでひとくくり.
     * パッケージに定義されていないテーブルはコメントないし名前でパッケージ化.
     * @param  array|null  $packageDefines
     * @return Collection|InformationSchemaTable[]
     */
    private function getPackagedSchema($packageDefines = null)
    {
        return InformationSchemaTable::where('TABLE_SCHEMA', config('mysql2plantuml.target_database'))
            ->get()
            ->filter(static function(InformationSchemaTable $table){
                $withoutTableNames = config('mysql2plantuml.without_tables') ?: [];
                return ! in_array($table->TABLE_NAME, $withoutTableNames, true);
            })
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

    /**
     * @return string
     */
    protected function PrepareDistDir(): string
    {
        $baseDirPath = base_path().DIRECTORY_SEPARATOR.config('mysql2plantuml.dist_dir').DIRECTORY_SEPARATOR;
        if (!file_exists($baseDirPath) && !mkdir($baseDirPath) && !is_dir($baseDirPath)) {
            throw new NotCreateDirException(sprintf('Directory "%s" was not created', $baseDirPath));
        }
        return $baseDirPath;
    }
}
