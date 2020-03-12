@section('entity')
@php
    /** @var \Mysql2PlantUml\App\Models\Eloquents\InformationSchemaTable $table  */
    /** @var \Mysql2PlantUml\App\Models\Eloquents\InformationSchemaColumn $column  */
    /** @var \Mysql2PlantUml\App\Models\Eloquents\InformationSchemaKeyColumnUsage $keyColumnUsage  */
@endphp
@php
    $columns = $table->columns();
@endphp
	項目名	DBカラム	属性	必須	PK	備考
@foreach( $columns as $index => $column )
@php
    $commentOnDb = $column->columnCommentWithLaravelDefault();
    $parts = explode('.', $commentOnDb);
    $name = trim($parts[0]);
    unset($parts[0]);
    $comment = count($parts) > 0 ? trim(implode('.', $parts)) : '';
@endphp
{{ $index + 1 }}	{{ $name }}								{{ $column->COLUMN_NAME }}										{{ $column->COLUMN_TYPE }}						{{ $column->isNullable() ? '○' : null }}			{{ !$column->isPrimaryKey() ? '○'  : null }}			{{ $comment }}
@endforeach
@overwrite
@yield('entity')
