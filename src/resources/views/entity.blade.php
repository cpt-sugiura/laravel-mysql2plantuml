@section('entity')
@php
    /** @var \Mysql2PlantUml\App\Models\InformationSchemaTable $table  */
    /** @var \Mysql2PlantUml\App\Models\InformationSchemaColumn $column  */
    /** @var \Mysql2PlantUml\App\Models\InformationSchemaKeyColumnUsage $keyColumnUsage  */
@endphp
entity "{{ $table->TABLE_COMMENT ? $table->TABLE_COMMENT.'\n' : null }}{{ $table->TABLE_NAME }}" as {{ $table->TABLE_NAME }} {
@php
    $columns = $table->columns();
    $maxColumnNameLen = $table->getColumnsColumnMaxLength('COLUMN_NAME');
    $maxColumnTypeLen = $table->getColumnsColumnMaxLength('COLUMN_TYPE');
    $maxColumnDefaultLen = $table->getColumnsColumnMaxLength('columnDefaultToString');
@endphp
@foreach( $columns as $column )
    {{ $column->columnKeyToPrefixString() }} {{ str_pad($column->COLUMN_NAME, $maxColumnNameLen) }}{{ $column->columnKeyToPostString() }} {{ str_pad($column->COLUMN_TYPE, $maxColumnTypeLen) }} {{ str_pad($column->columnDefaultToString(), $maxColumnDefaultLen) }} {{ $column->isNullableToString() }} {!! $column->columnCommentToString() !!}
@endforeach
}
@overwrite
@yield('entity')
