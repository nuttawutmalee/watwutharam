<?php
namespace App\Api\Constants;

class OptionValueConstants extends EnumType
{
    const STRING = 'TEXT';
    const INTEGER = 'NUMBER';
    const DECIMAL = 'DECIMAL';
    const DATE = 'DATE';

    const OPTION_TYPES = [
        self::STRING,
        self::INTEGER,
        self::DECIMAL,
        self::DATE
    ];

    const STRING_RELATIONSHIP = 'string';
    const INTEGER_RELATIONSHIP = 'integer';
    const DECIMAL_RELATIONSHIP = 'decimal';
    const DATE_RELATIONSHIP = 'date';

    const OPTION_RELATIONSHIPS = [
        self::STRING_RELATIONSHIP,
        self::INTEGER_RELATIONSHIP,
        self::DECIMAL_RELATIONSHIP,
        self::DATE_RELATIONSHIP
    ];

    public  function getFields()
    {
        return [
            'STRING',
            'INTEGER',
            'DECIMAL',
            'DATE',
            'OPTION_TYPES',
            'STRING_RELATIONSHIP',
            'INTEGER_RELATIONSHIP',
            'DECIMAL_RELATIONSHIP',
            'DATE_RELATIONSHIP',
            'OPTION_RELATIONSHIPS',
        ];
    }

    /**
     * @param $type
     * @return mixed
     */
    public static function getRelationshipByType($type)
    {
        $types = collect(self::OPTION_TYPES);
        $relationships = collect(self::OPTION_RELATIONSHIPS);
        $index = $types->search(strtoupper($type), true);

        return strtolower($relationships->get($index));
    }

    /**
     * @param $relationship
     * @return mixed
     */
    public static function getTypeByRelationship($relationship)
    {
        $relationships = collect(self::OPTION_RELATIONSHIPS);
        $types = collect(self::OPTION_TYPES);
        $index = $relationships->search($relationship, true);

        return strtoupper($types->get($index));
    }
}