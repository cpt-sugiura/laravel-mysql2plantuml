<?php


namespace Mysql2PlantUml\app\Models\ValueObjects;

use Mysql2PlantUml\app\Exceptions\InvalidArgsException;

class Relation
{
    public const FORMAT_CROW = 0x0;
    public const FORMAT_NUM = 0x1;
    public const FORMAT_TYPES = [
        self::FORMAT_CROW,
        self::FORMAT_NUM,
    ];

    public const MANY_FOO_TO_BAR_BAZ = 0x1000;
    public const FOO_OPTIONAL_TO_BAR_BAZ = 0x0100;
    public const FOO_BAR_TO_MANY_BAZ = 0x0010;
    public const FOO_BAR_TO_BAZ_OPTIONAL = 0x0001;

    public const ONE_MANDATORY_TO_ONE_MANDATORY = 0x0000;
    public const ONE_MANDATORY_TO_ONE_OPTIONAL = 0x0001;
    public const ONE_MANDATORY_TO_MANY_MANDATORY = 0x0010;
    public const ONE_MANDATORY_TO_MANY_OPTIONAL = 0x0011;
    public const ONE_OPTIONAL_TO_ONE_MANDATORY = 0x0100;
    public const ONE_OPTIONAL_TO_ONE_OPTIONAL = 0x0101;
    public const ONE_OPTIONAL_TO_MANY_MANDATORY = 0x0110;
    public const ONE_OPTIONAL_TO_MANY_OPTIONAL = 0x0111;
    public const MANY_MANDATORY_TO_ONE_MANDATORY = 0x1000;
    public const MANY_MANDATORY_TO_ONE_OPTIONAL = 0x1001;
    public const MANY_MANDATORY_TO_MANY_MANDATORY = 0x1010;
    public const MANY_MANDATORY_TO_MANY_OPTIONAL = 0x1011;
    public const MANY_OPTIONAL_TO_ONE_MANDATORY = 0x1100;
    public const MANY_OPTIONAL_TO_ONE_OPTIONAL = 0x1101;
    public const MANY_OPTIONAL_TO_MANY_MANDATORY = 0x1110;
    public const MANY_OPTIONAL_TO_MANY_OPTIONAL = 0x1111;
    public const RELATION_TYPES = [
        self::ONE_MANDATORY_TO_ONE_MANDATORY,
        self::ONE_MANDATORY_TO_ONE_OPTIONAL,
        self::ONE_MANDATORY_TO_MANY_MANDATORY,
        self::ONE_MANDATORY_TO_MANY_OPTIONAL,
        self::ONE_OPTIONAL_TO_ONE_MANDATORY,
        self::ONE_OPTIONAL_TO_ONE_OPTIONAL,
        self::ONE_OPTIONAL_TO_MANY_MANDATORY,
        self::ONE_OPTIONAL_TO_MANY_OPTIONAL,
        self::MANY_MANDATORY_TO_ONE_MANDATORY,
        self::MANY_MANDATORY_TO_ONE_OPTIONAL,
        self::MANY_MANDATORY_TO_MANY_MANDATORY,
        self::MANY_MANDATORY_TO_MANY_OPTIONAL,
        self::MANY_OPTIONAL_TO_ONE_MANDATORY,
        self::MANY_OPTIONAL_TO_ONE_OPTIONAL,
        self::MANY_OPTIONAL_TO_MANY_MANDATORY,
        self::MANY_OPTIONAL_TO_MANY_OPTIONAL,
    ];

    public const ARROW_CHAR_LINE = '-';
    public const ARROW_CHAR_DOT = '.';
    public const ARROW_CHAR_SET = [
        self::ARROW_CHAR_LINE,
        self::ARROW_CHAR_DOT,
    ];

    public const DIRECTION_AUTO = '';
    public const DIRECTION_UP = 'up';
    public const DIRECTION_DOWN = 'down';
    public const DIRECTION_LEFT = 'left';
    public const DIRECTION_RIGHT = 'right';
    public const DIRECTION_TYPES = [
        self::DIRECTION_AUTO,
        self::DIRECTION_UP,
        self::DIRECTION_DOWN,
        self::DIRECTION_LEFT,
        self::DIRECTION_RIGHT,
    ];

    public const DEFAULT_ARROW_LENGTH = 2;
    public const DEFAULT_ARROW_CHAR = self::ARROW_CHAR_LINE;
    public const DEFAULT_DIRECTION = self::DIRECTION_AUTO;
    public const DEFAULT_RELATION_TYPE = self::MANY_OPTIONAL_TO_ONE_MANDATORY;

    /** @var string $fromTable */
    public $fromTable;
    /** @var string $toTable */
    public $toTable;
    public $relationType;
    public $diagramDirection;
    public $diagramArrowLength;
    public $diagramArrowChar;

    /**
     * Relation constructor.
     * @param  string  $fromTable
     * @param  string  $toTable
     * @param          $relationType
     * @param          $diagramDirection
     * @param          $diagramArrowLength
     * @param  string  $arrowChar
     * @throws InvalidArgsException
     */
    public function __construct(
        string $fromTable,
        string $toTable,
        $relationType = null,
        $diagramDirection = null,
        $diagramArrowLength = null,
        $arrowChar = null
    ) {
        $this->setFromTable($fromTable);
        $this->setToTable($toTable);
        $this->setRelationType($relationType ?? self::DEFAULT_RELATION_TYPE);
        $this->setDiagramDirection($diagramDirection ?? self::DEFAULT_DIRECTION);
        $this->setDiagramArrowLength($diagramArrowLength ?? self::DEFAULT_ARROW_LENGTH);
        $this->setArrowChar($arrowChar ?? self::DEFAULT_ARROW_CHAR);
    }

    public function getRelationTableSetId(): string
    {
        $set = [
            $this->fromTable,
            $this->toTable,
        ];
        sort($set);

        return implode('##', $set);
    }

    /**
     * @param  array  $attributes
     * @return Relation
     * @throws InvalidArgsException
     */
    public static function createRelationByConfig(array $attributes): Relation
    {
        return new self(
            $attributes['from'],
            $attributes['to'],
            $attributes['relation'] ?? null,
            $attributes['direction'] ?? null,
            $attributes['arrowLength'] ?? null,
            $attributes['arrowChar'] ?? null
        );
    }

    public function getRelationArrowStr(): string
    {
        switch (config('mysql2plantuml.relation_type')) {
            case self::FORMAT_CROW:
                return $this->getRelationArrowCrowFormatStr();
            case self::FORMAT_NUM:
            default:
                return $this->getRelationArrowNumFormatStr();
        }
    }

    /**
     * @param $relationType
     * @throws InvalidArgsException
     */
    private function setRelationType($relationType): void
    {
        if (!in_array($relationType, self::RELATION_TYPES, true)) {
            throw new InvalidArgsException(
                'Invalid RELATION_TYPE = '.$relationType.'. Please, $relationType in '.self::class.'::RELATION_TYPES.'
            );
        }
        $this->relationType = $relationType;
    }

    /**
     * @param  string  $toTable
     * @throws InvalidArgsException
     */
    private function setToTable(string $toTable): void
    {
        if ($toTable === '') {
            throw new InvalidArgsException(
                'Invalid $toTable = '.$toTable.'. Please, $toTable is not empty str.'
            );
        }
        $this->toTable = $toTable;
    }

    /**
     * @param  string  $fromTable
     * @throws InvalidArgsException
     */
    private function setFromTable(string $fromTable): void
    {
        if ($fromTable === '') {
            throw new InvalidArgsException(
                'Invalid $fromTable = '.$fromTable.'. Please, $fromTable is not empty str.'
            );
        }
        $this->fromTable = $fromTable;
    }

    /**
     * @param $diagramDirection
     * @throws InvalidArgsException
     */
    private function setDiagramDirection($diagramDirection): void
    {
        if (!in_array($diagramDirection, self::DIRECTION_TYPES, true)) {
            throw new InvalidArgsException(
                'Invalid DIRECTION_TYPE= '.$diagramDirection.'.  Please, $diagramDirection in '.self::class.'::DIRECTION_TYPES.'
            );
        }
        $this->diagramDirection = $diagramDirection;
    }

    /**
     * @param $diagramArrowLength
     * @throws \Mysql2PlantUml\app\Exceptions\InvalidArgsException
     */
    private function setDiagramArrowLength($diagramArrowLength): void
    {
        if ($diagramArrowLength <= 0) {
            throw new InvalidArgsException(
                'Invalid $diagramArrowLength. Please, $diagramArrowLength above 0 .'
            );
        }
        $this->diagramArrowLength = $diagramArrowLength;
    }

    /**
     * @return string
     */
    protected function getRelationArrowCrowFormatStr(): string
    {
        $arrowStr = $this->fromTable.' ';
        if (($this->relationType & self::MANY_FOO_TO_BAR_BAZ) === self::MANY_FOO_TO_BAR_BAZ) {
            $arrowStr .= '}';
        }
        if (($this->relationType & self::FOO_OPTIONAL_TO_BAR_BAZ) === self::FOO_OPTIONAL_TO_BAR_BAZ) {
            $arrowStr .= 'o';
        }
        $arrowStr .= $this->diagramArrowChar.$this->diagramDirection.str_repeat(
                $this->diagramArrowChar,
                $this->diagramArrowLength - 1
            );
        if (($this->relationType & self::FOO_BAR_TO_BAZ_OPTIONAL) === self::FOO_BAR_TO_BAZ_OPTIONAL) {
            $arrowStr .= 'o';
        }
        if (($this->relationType & self::FOO_BAR_TO_MANY_BAZ) === self::FOO_BAR_TO_MANY_BAZ) {
            $arrowStr .= '{';
        }
        return $arrowStr.' '.$this->toTable;
    }

    /**
     * @return string
     */
    protected function getRelationArrowNumFormatStr(): string
    {
        $leftRange = '';
        if (($this->relationType & self::FOO_OPTIONAL_TO_BAR_BAZ) === self::FOO_OPTIONAL_TO_BAR_BAZ) {
            $leftRange .= '0..';
        } else {
            $leftRange .= '1..';
        }
        if (($this->relationType & self::MANY_FOO_TO_BAR_BAZ) === self::MANY_FOO_TO_BAR_BAZ) {
            $leftRange .= 'n';
        } else {
            $leftRange .= '1';
        }
        $leftRange = str_replace('1..1', '1', $leftRange);

        $rightRange = '';
        if (($this->relationType & self::FOO_BAR_TO_BAZ_OPTIONAL) === self::FOO_BAR_TO_BAZ_OPTIONAL) {
            $rightRange .= '0..';
        } else {
            $rightRange .= '1..';
        }
        if (($this->relationType & self::FOO_BAR_TO_MANY_BAZ) === self::FOO_BAR_TO_MANY_BAZ) {
            $rightRange .= 'n';
        } else {
            $rightRange .= '1';
        }
        $rightRange = str_replace('1..1', '1', $rightRange);

        return $this->fromTable.' '
            .$this->diagramArrowChar.$this->diagramDirection.str_repeat(
                $this->diagramArrowChar,
                $this->diagramArrowLength - 1
            )
            .' '.$this->toTable
            .':'.$this->fromTable.' : '.$this->toTable.'\n = '.$leftRange.' : '.$rightRange;
    }

    /**
     * @param  string  $diagramArrowChar
     * @throws InvalidArgsException
     */
    private function setArrowChar(string $diagramArrowChar): void
    {
        if (!in_array($diagramArrowChar, self::ARROW_CHAR_SET, true)) {
            throw new InvalidArgsException(
                'Invalid ARROW_CHAR= "'.$diagramArrowChar.'".  Please, $diagramArrowChar in '.self::class.'::ARROW_CHAR_SET.'
            );
        }
        $this->diagramArrowChar = $diagramArrowChar;
    }
}
