<?php
namespace App\Api\Constants;

class OptionElementTypeConstants extends EnumType
{
    const TEXTBOX = 'TEXTBOX';
    const CHECKBOX = 'CHECKBOX';
    const CHECKBOX_LIST = 'CHECKBOX_LIST';
    const RADIO_LIST = 'RADIO_LIST';
    const DROPDOWN = 'DROPDOWN';
    const MULTILINE_TEXT = 'MULTILINE_TEXT';
    const RICHTEXT_EDITOR = 'RICHTEXT_EDITOR';
    const FILE_UPLOAD = 'FILE_UPLOAD';
    const DATETIME = 'DATETIME';
    const CONTROL_LIST = 'CONTROL_LIST';
    const MAP_COORDINATE = 'MAP_COORDINATE';

    const PAGINATION_ENABLED = 'paginationEnabled';
    const PAGINATION_PER_PAGE = 'paginationPerPage';
    const PAGINATION_PAGE_NAME = 'page';
    const PAGINATION_TEMPLATE_NAME = 'selectedPaginationTemplate';
    const PAGINATION_RENDER_DATA = 'renderData';

    const FILTER_ENABLED = 'filterEnabled';
    const FILTER_NAME = 'filter';

    const ELEMENT_TYPES = [
        self::TEXTBOX,
        self::CHECKBOX,
        self::CHECKBOX_LIST,
        self::RADIO_LIST,
        self::DROPDOWN,
        self::MULTILINE_TEXT,
        self::RICHTEXT_EDITOR,
        self::FILE_UPLOAD,
        self::DATETIME,
        self::CONTROL_LIST,
        self::MAP_COORDINATE
    ];

    public  function getFields()
    {
        return [
            'TEXTBOX',
            'CHECKBOX',
            'CHECKBOX_LIST',
            'RADIO_LIST',
            'DROPDOWN',
            'MULTILINE_TEXT',
            'RICHTEXT_EDITOR',
            'FILE_UPLOAD',
            'DATETIME',
            'CONTROL_LIST',
            'MAP_COORDINATE',

            'PAGINATION_ENABLED',
            'PAGINATION_PER_PAGE',
            'PAGINATION_PAGE_NAME',
            'PAGINATION_TEMPLATE_NAME',
            'PAGINATION_RENDER_DATA',

            'FILTER_ENABLED',
            'FILTER_NAME',

            'ELEMENT_TYPES'
        ];
    }
}