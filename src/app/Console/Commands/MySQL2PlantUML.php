<?php

namespace Mysql2PlantUml\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Mysql2PlantUml\app\Exceptions\NotCreateDirException;
use Mysql2PlantUml\App\Models\Eloquents\InformationSchemaTable;
use Mysql2PlantUml\app\Models\ValueObjects\Relation;
use Mysql2PlantUml\app\Services\InformationSchemaService;
use Symfony\Component\Console\Input\InputOption;

class MySQL2PlantUML extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'dump:mysql2puml';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'MySQL中に既存のデータベースを元にER図を出力します。';

    /**
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['excel', 'e', InputOption::VALUE_NONE, 'Excelに使いやすいTSV形式で出力する'],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $packageDefines = config('mysql2plantuml.packages');
        $packages = InformationSchemaService::getPackagedSchema($packageDefines);
        $relationsByConfig = collect(config('mysql2plantuml.relations'))
            ->map(
                static function (array $item) {
                    return Relation::createRelationByConfig($item);
                }
            ) ?: collect();

        $bladePath = $this->getBaseBladePath();
        $freeComment = config('mysql2plantuml.free_comment');
        $view = View::file($bladePath, compact('packages', 'relationsByConfig', 'freeComment'));

        $baseDirPath = $this->prepareDistDir();
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

        $this->dumpSubFiles($packages, $bladePath, $relationsByConfig, $freeComment);
    }

    /**
     * @return string
     */
    protected function prepareDistDir(): string
    {
        $baseDirPath = base_path().DIRECTORY_SEPARATOR.config('mysql2plantuml.dist_dir').DIRECTORY_SEPARATOR;
        if (!file_exists($baseDirPath) && !mkdir($baseDirPath) && !is_dir($baseDirPath)) {
            throw new NotCreateDirException(sprintf('Directory "%s" was not created', $baseDirPath));
        }
        return $baseDirPath;
    }

    /**
     * @return string
     */
    protected function getBaseBladePath(): string
    {
        return $this->option('excel')
            ? __DIR__.'/../../../resources/views/excel/base.blade.php'
            : __DIR__.'/../../../resources/views/base.blade.php';
    }

    /**
     * @param  Collection<Collection<InformationSchemaTable>>  $packages
     * @param  string                                          $bladePath
     * @param  Collection                                      $relationsByConfig
     * @param  string|null                                     $freeComment
     */
    protected function dumpSubFiles($packages, string $bladePath, Collection $relationsByConfig, $freeComment): void
    {
        $subFilesDefines = config('mysql2plantuml.sub_files');
        if (!is_array($subFilesDefines)) {
            return;
        }
        foreach ($subFilesDefines as $filename => $tableNames) {
            $subTables = $packages->map(
                static function ($package) use ($tableNames) {
                    /** @var Collection<InformationSchemaTable> $package */
                    return $package->filter(
                        static function ($table) use ($tableNames) {
                            /** @var InformationSchemaTable $table */
                            return isset($table->TABLE_NAME) && in_array($table->TABLE_NAME, $tableNames, true);
                        }
                    );
                }
            )->filter(
                static function ($package) {
                    /** @var Collection $package */
                    return $package->isNotEmpty();
                }
            );
            $view = View::file(
                $bladePath,
                [
                    'packages'          => $subTables,
                    'relationsByConfig' => $relationsByConfig,
                    'freeComment'       => $freeComment
                ]
            );

            $baseDirPath = $this->prepareDistDir();
            file_put_contents($baseDirPath.$filename, $view->render());
        }
    }
}
