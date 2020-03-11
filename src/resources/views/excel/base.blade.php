@php
    /** @var \Mysql2PlantUml\App\Models\Eloquents\InformationSchemaTable $table  */
    /** @var \Mysql2PlantUml\App\Models\Eloquents\InformationSchemaColumn $column  */
    /** @var \Mysql2PlantUml\App\Models\Eloquents\InformationSchemaKeyColumnUsage $keyColumnUsage  */
    /** @var array $relationsByConfig  */
@endphp
@foreach( $packages as $packageName => $tables )
@foreach( $tables as $index => $table )
機能名称	{{ $table->TABLE_COMMENT ?: null }}

テーブル名	{{ $table->TABLE_NAME }}

@include('puml::excel.entity', compact('index','table') )
@endforeach
@endforeach
