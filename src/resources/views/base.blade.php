@php
    /** @var \Mysql2PlantUml\App\Models\Eloquents\InformationSchemaTable $table  */
    /** @var \Mysql2PlantUml\App\Models\Eloquents\InformationSchemaColumn $column  */
    /** @var \Mysql2PlantUml\App\Models\Eloquents\InformationSchemaKeyColumnUsage $keyColumnUsage  */
    /** @var array $relationsByConfig  */
@endphp
@startuml
skinparam {
defaultFontName Monospaced
}
left to right direction
@foreach( $packages as $packageName => $tables )
package {{ $packageName }} {
@foreach( $tables as $table )
@include('puml::entity', ['table' => $table] )
@endforeach
}
@endforeach

@foreach( $packages as $packageName => $tables )
@foreach( $tables as $table )
@foreach( $table->relationValueObjects() as $relation )
{{ $relation->getRelationArrowStr() }}
@endforeach
@endforeach
@endforeach
@enduml
