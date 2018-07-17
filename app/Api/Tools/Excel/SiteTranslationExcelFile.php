<?php
namespace App\Api\Tools\Excel;

use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\ValidationRuleConstants;
use App\Api\Models\GlobalItem;
use App\Api\Models\GlobalItemOption;
use App\Api\Models\Language;
use App\Api\Models\Page;
use App\Api\Models\PageItem;
use App\Api\Models\PageItemOption;
use App\Api\Models\Site;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SiteTranslationExcelFile
{
    const ROWS = 'rows';
    const STYLES = 'styles';
    const LINKS = 'links';
    const FORMULAS = 'formulas';
    const COLUMN_WIDTH_SIZES = 'columnWidthSizes';

    const SHEET_NAME = 'sheetName';
    const DATA = 'data';
    const FREEZE = 'freeze';

    const BACKGROUND = 'background';
    const FONT_COLOR = 'fontColor';
    const FONT_WEIGHT = 'fontWeight';
    const FONT_FAMILY = 'fontFamily';
    const FONT_SIZE = 'fontSize';
    const HORIZONTAL_ALIGNMENT = 'horizontalAlignment';
    const VERTICAL_ALIGNMENT = 'verticalAlignment';

    const PROTECTED_CELLS = 'protectedCells';
    const UNPROTECTED_CELLS = 'unprotectedCells';

    const NAMED_RANGE = 'namedRange';
    const COORDINATION = 'xy';
    const NAME = 'name';
    const LABEL = 'label';
    const BACK_LINK = 'back_link';
    const RESERVED_ID = '_reserved_id';
    const HAS_RESERVED_ID = '_has_reserved_id';

    const SITE = 'SITE';
    const SITE_DOMAIN_NAME = 'DOMAIN NAME';
    const SITE_ID = 'SITE ID';

    const PAGE = 'PAGE';
    const PAGES = 'PAGES';
    const PAGE_ID = 'PAGE ID';
    const PAGE_NAME = 'PAGE NAME';

    const PAGE_ITEM_OPTION = 'PAGE_ITEM_OPTION';

    const GLOBAL_ITEM = 'GLOBAL';
    const GLOBAL_ITEMS = 'GLOBALS';
    const GLOBAL_ITEM_ID = 'GLOBAL ITEM ID';
    const GLOBAL_ITEM_NAME = 'GLOBAL ITEM NAME';

    const GLOBAL_ITEM_OPTION = 'GLOBAL_ITEM_OPTION';

    const CONTROL_LIST = 'CONTROL_LIST';
    const SUB_CONTROL_LIST = 'SUB_CONTROL_LIST';
    const CONTROL_LIST_ITEM_ID = 'ITEM ID';
    const CONTROL_LIST_FROM_ITEM = 'FROM ITEM';
    const CONTROL_LIST_NAME = 'NAME';
    const CONTROL_LIST_IN_PAGE = 'IN PAGE';

    const DOWN_SYMBOL = '↓';

    const NO_TRANSLATIONS = 'No Translations Available';
    const USING_DATA_FROM_GLOBAL_ITEM = 'Using data from global item';
    const NO_DATA = 'No Data';

    const BACK_TO = 'BACK TO';

    const INVALID_FORMAT = 'Invalid format';
    const INVALID_SITE = 'Invalid Site';

    /**
     * Site headers
     *
     * @var array
     */
    private $siteHeaders = [
        [
            self::ROWS => [
                self::GLOBAL_ITEMS,
                '',
                '',
                self::PAGES,
            ],
            self::STYLES => [
                'A*:D*' => [
                    self::FONT_WEIGHT => 'bold'
                ]
            ]
        ],
        [
            self::ROWS => [
                'NO.',
                'NAME',
                '',
                'NO.',
                'NAME'
            ],
            self::STYLES => [
                'A*' => [
                    self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                ],
                'A*:B*' => [
                    self::BACKGROUND => '#305496',
                    self::FONT_COLOR => '#ffffff',
                    self::FONT_WEIGHT => 'bold'
                ],
                'D*' => [
                    self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                ],
                'D*:E*' => [
                    self::BACKGROUND => '#305496',
                    self::FONT_COLOR => '#ffffff',
                    self::FONT_WEIGHT => 'bold'
                ],
            ]
        ]
    ];

    /**
     * Item headers
     *
     * @var array
     */
    private $itemHeaders = [
        self::ROWS => [
            'NO.',
            'ITEM ID',
            'ITEM NAME',
            '',
            ''
        ],
        self::STYLES => [
            'A*' => [
                self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ],
            'A*:E*' => [
                self::BACKGROUND => '#305496',
                self::FONT_COLOR => '#ffffff',
                self::FONT_WEIGHT => 'bold'
            ]
        ]
    ];

    /**
     * Item option headers
     *
     * @var array
     */
    private $itemOptionHeaders = [
        self::ROWS => [
            '',
            '',
            'ITEM OPTION ID',
            'ITEM OPTION NAME',
            'TYPE'
        ],
        self::STYLES => [
            'C*:E*' => [
                self::BACKGROUND => '#305496',
                self::FONT_COLOR => '#ffffff',
                self::FONT_WEIGHT => 'bold'
            ]
        ]
    ];

    /**
     * Control list item option headers
     *
     * @var array
     */
    private $controlListItemOptionHeaders = [
        self::ROWS => [
            'SUB ITEM NO.',
            'SUB ITEM ID',
            'SUB ITEM NAME',
            'TYPE',
            '',
            'TRANSLATION'
        ],
        self::STYLES => [
            'A*' => [
                self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ],
            'A*:F*' => [
                self::BACKGROUND => '#305496',
                self::FONT_COLOR => '#ffffff',
                self::FONT_WEIGHT => 'bold'
            ]
        ]
    ];

    /**
     * Site instructions
     *
     * @var array
     */
    private $siteInstructions = [
        [
            self::ROWS => [
                'INSTRUCTIONS:',
                'Click on the link and it will bring you to the editing sheet of the item you selected.'
            ],
            self::STYLES => [
                'A*:B*' => [
                    self::FONT_WEIGHT => 'bold',
                    self::FONT_SIZE => 12
                ]
            ]
        ],
        [
            'rows' => ['']
        ]
    ];

    /**
     * Instructions
     *
     * @var array
     */
    private $instructions = [
        [
            self::ROWS => [
                'INSTRUCTIONS:',
                'Please edit cells with this green header only.'
            ],
            self::STYLES => [
                'A*:B*' => [
                    self::FONT_WEIGHT => 'bold',
                    self::FONT_SIZE => 12
                ],
                'B*' => [
                    self::FONT_COLOR => '#548235'
                ]
            ]
        ],
        [
            self::ROWS => [
                '',
                'For the control list items, in order to edit its contents, please click its name. This will bring you to its sub items\' contents. Once you are done editing, please click \'BACK\' to go back to its parent item.'
            ],
            self::STYLES => [
                'B*' => [
                    self::FONT_WEIGHT => 'bold',
                    self::FONT_SIZE => 12,
                    self::FONT_COLOR => '#c65911'
                ]
            ]
        ],
        [
            'rows' => ['']
        ]
    ];

    /**
     * Column width sizes
     *
     * @var array
     */
    private $columnWidthSizes = [
        'A' => 18,
        'B' => 18,
        'C' => 30,
        'D' => 30,
        'E' => 18
    ];

    /**
     * Language columns
     *
     * @var array
     */
    private $languageColumns = [];

    /**
     * Language column width size
     *
     * @var int
     */
    private $languageColumnWidthSize = 40;

    /**
     * Language header styles
     *
     * @var array
     */
    private $languageHeaderStyle = [
        self::BACKGROUND => '#548235',
        self::FONT_COLOR => '#ffffff',
        self::FONT_WEIGHT => 'bold'
    ];

    /**
     * Hyperlink style
     *
     * @var array
     */
    private $linkStyle = [
        self::FONT_COLOR => '#0000FF',
        self::FONT_WEIGHT => 'bold'
    ];

    /**
     * Invalid sheet name
     *
     * @var array
     */
    private $invalidSheetName = array('*', ':', '/', '\\', '?', '[', ']', '\'');

    /**
     * Site's languages
     *
     * @var Language
     */
    private $siteLanguages;

    /**
     * Return translation export excel file
     *
     * @param Site $site
     * @param null $filename
     * @param null $password
     * @throws \Exception
     */
    public function export(Site $site, $filename = null, $password = null)
    {
        if (is_null($filename)) {
            $filename = ucwords($site->domain_name) . ' Translations';
        }

        if (is_null($password)) {
            $password = str_random(30);
        }

        $this->siteLanguages = $site->languages;

        if ( ! empty($this->siteLanguages)) {
            foreach ($this->siteLanguages as $index => $language) {
                $position = count($this->columnWidthSizes);
                $column = $this->getExcelColumnFromNumber($position);

                $this->columnWidthSizes[$column] = $this->languageColumnWidthSize;
                $this->languageColumns[] = $column;

                /** Insert languages into item headers */
                $this->itemHeaders[self::ROWS][] = $language->name . ' (' . $language->getKey() . ')';
                $this->itemHeaders[self::STYLES][$column . '*'] = $this->languageHeaderStyle;
                $this->itemHeaders[self::PROTECTED_CELLS][] = $column . '*';
            }
        }

        /** Excel sheet data */
        $sheets = [];

        /** Control list data */
        $controlListData = [];
        $subControlListData = [];

        /**
         * Global Items
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $globalItems = $site
            ->pristineGlobalItems()
            ->where('is_visible', true)
            ->with(
                [
                    'globalItemOptions' => function ($query) {
                        /** @var $query \Illuminate\Database\Eloquent\Builder */
                        $query->where('is_visible', true)
                            ->with(
                                'siteTranslations',
                                'elementType'
                            );
                    }
                ]
            )
            ->orderBy('name')
            ->get();

        $globalItemNamedRanges = [];

        if ( ! empty($globalItems)) {
            /** Initialize excel rows */
            $globalItemData = [];

            /** Insert instruction */
            $this->pushFlatten($this->instructions, $globalItemData);

            /** Insert item header */
            $globalItemData[] = $this->itemHeaders;

            /** @var GlobalItem $globalItem */
            foreach ($globalItems as $index => $globalItem) {
                $globalItemNamedRanges[] = [
                    self::LABEL => $globalItem->name,
                    self::NAME => self::GLOBAL_ITEM . preg_replace('/-/', '', $globalItem->getKey()),
                    self::BACK_LINK => self::SITE . self::GLOBAL_ITEM . preg_replace('/-/', '', $globalItem->getKey())
                ];

                /** Insert global item data */
                $globalItemData[] = [
                    self::ROWS => [
                        ++$index,
                        $globalItem->getKey(),
                        $globalItem->name
                    ],
                    self::STYLES => [
                        'A*' => [
                            self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                        ]
                    ],
                    self::NAMED_RANGE => [
                        [
                            self::COORDINATION => 'C*',
                            self::NAME => self::GLOBAL_ITEM . preg_replace('/-/', '', $globalItem->getKey()),
                        ]
                    ]
                ];

                $globalItemOptions = $this->filterNecessaryItemOptions($globalItem->globalItemOptions);

                $globalItemOptions = $this->rejectItemOptionsByVariableNames($globalItemOptions, ValidationRuleConstants::FORM_PRIVATE_PROPERTIES);

                /** Insert down symbol  */
                $globalItemData[] = [
                    self::ROWS => [
                        '',
                        '',
                        self::DOWN_SYMBOL
                    ],
                    self::STYLES => [
                        'C*' => [
                            self::FONT_WEIGHT => 'bold',
                            self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                        ]
                    ]
                ];

                /** Insert item option header */
                $globalItemData[] = $this->itemOptionHeaders;

                if (empty($globalItemOptions)) {
                    /** Insert empty translation row */
                    $globalItemData[] = [
                        self::ROWS => [
                            '',
                            '',
                            self::NO_TRANSLATIONS
                        ],
                        self::STYLES => [
                            'C*' => [
                                self::FONT_WEIGHT => 'bold',
                                self::FONT_COLOR => '#FF0000'
                            ]
                        ]
                    ];
                } else {
                    /** @var GlobalItemOption $globalItemOption */
                    foreach ($globalItemOptions as $globalItemOption) {
                        /** Insert global item option data */
                        $rowData = [
                            self::ROWS => [
                                '',
                                '',
                                $globalItemOption->getKey(),
                                $globalItemOption->name,
                                $globalItemOption->elementType->element_type
                            ]
                        ];

                        if ($globalItemOption->elementType->element_type === OptionElementTypeConstants::CONTROL_LIST) {
                            /** Control list specific */
                            $rowData[self::STYLES] = [
                                'E*' => [
                                    self::FONT_COLOR => '#c65911',
                                    self::FONT_WEIGHT => 'bold'
                                ],
                                'D*' => $this->linkStyle
                            ];

                            $namedRange = self::GLOBAL_ITEM_OPTION . preg_replace('/-/', '', $globalItemOption->getKey());
                            $controlListNamedRange = self::CONTROL_LIST . $namedRange;

                            $rowData[self::NAMED_RANGE] = [
                                [
                                    self::COORDINATION => 'D*',
                                    self::NAME => $namedRange
                                ]
                            ];

                            $rowData[self::FORMULAS] = [
                                'D*' => $this->generateHyperlinkFormula('#' . $controlListNamedRange, $globalItemOption->name)
                            ];

                            /** Insert data into control list sheet */
                            $controlListRowData = $this->generateControlListRowData($globalItem, $globalItemOption, $namedRange, $controlListNamedRange, $subControlListData);
                            $controlListData = array_merge($controlListData, $controlListRowData);
                        } else {
                            /** Normal data */
                            if ( ! empty($this->languageColumns)) {
                                if (count($this->languageColumns) === 1) {
                                    $rowData[self::UNPROTECTED_CELLS] = [$this->languageColumns[0] . '*'];
                                } else {
                                    $firstLanguageColumn = array_first($this->languageColumns);
                                    $lastLanguageColumn = array_last($this->languageColumns);
                                    $rowData[self::UNPROTECTED_CELLS] = [$firstLanguageColumn . '*:' . $lastLanguageColumn . '*'];
                                }

                                foreach ($this->siteLanguages as $siteLanguage) {
                                    $translation = $globalItemOption->withOptionSiteTranslation($siteLanguage->getKey());
                                    $rowData[self::ROWS][] = array_key_exists('translated_text', $translation) ? $translation['translated_text'] : $translation['option_value'];
                                }
                            }
                        }

                        $globalItemData[] = $rowData;
                    }
                }

                $globalItemData[] = [
                    self::ROWS => [
                        '',
                        '',
                        self::BACK_TO . ' ' . self::SITE
                    ],
                    self::STYLES => [
                        'C*' => $this->linkStyle
                    ],
                    self::FORMULAS => [
                        'C*' => $this->generateHyperlinkFormula(
                            '#' . self::SITE . self::GLOBAL_ITEM . preg_replace('/-/', '', $globalItem->getKey()),
                            self::BACK_TO . ' ' . self::SITE
                        )
                    ]
                ];

                $globalItemData[] = [
                    self::ROWS => ['']
                ];
            }

            $globalItemData[] = [
                self::ROWS => [
                    '',
                    '',
                    '',
                    '',
                    ''
                ],
                self::STYLES => [
                    'A*:E*' => [
                        self::BACKGROUND => '#305496'
                    ]
                ]
            ];

            $this->pushFlatten([
                [
                    self::SHEET_NAME => self::GLOBAL_ITEM,
                    self::DATA => $globalItemData,
                    self::FREEZE => 'F4'
                ]
            ], $sheets);
        }

        /**
         * Pages
         */
        $pages = $site
            ->pages()
            ->with(
                [
                    'pageItems' => function ($query) {
                        /** @var $query \Illuminate\Database\Eloquent\Builder */
                        $query->where('is_visible', true)
                            ->with(
                                [
                                    'pageItemOptions' => function ($query) {
                                        /** @var $query \Illuminate\Database\Eloquent\Builder */
                                        $query->where('is_visible', true)
                                            ->with(
                                                'siteTranslations',
                                                'elementType'
                                            );
                                    }
                                ],
                                'globalItem'
                            );
                    }
                ]
            )
            ->orderBy('name')
            ->get();

        $pageNamedRanges = [];

        if ( ! empty($pages)) {
            /** Initialize excel rows */
            $pageData = [];

            /** @var \App\Api\Models\Page $page */
            foreach ($pages as $page) {
                $sheetName = $page->is_active
                    ? self::PAGE . ' - ' . $page->name
                    : self::PAGE . ' - ' . $page->name . ' (Inactive)';

                $pageItems = $page->pageItems;

                $pageNamedRanges[] = [
                    self::LABEL => $page->name,
                    self::NAME => self::PAGE . preg_replace('/-/', '', $page->getKey()),
                    self::BACK_LINK => self::SITE . self::PAGE . preg_replace('/-/', '', $page->getKey())
                ];

                /** Insert page data */
                $pageItemData = [
                    [
                        self::ROWS => [
                            self::PAGE_ID,
                            $page->getKey()
                        ],
                        self::NAMED_RANGE => [
                            [
                                self::COORDINATION => 'A1',
                                self::NAME => self::PAGE . preg_replace('/-/', '', $page->getKey()),
                            ]
                        ]
                    ],
                    [
                        self::ROWS => [
                            self::PAGE_NAME,
                            $page->name
                        ]
                    ]
                ];

                /** Insert instruction */
                $this->pushFlatten($this->instructions, $pageItemData);

                $pageItemData[] = [
                    self::ROWS => [
                        self::BACK_TO . ' ' . self::SITE
                    ],
                    self::FORMULAS => [
                        'A*' => $this->generateHyperlinkFormula(
                            '#' . self::SITE . self::PAGE . preg_replace('/-/', '', $page->getKey()),
                            self::BACK_TO . ' ' . self::SITE
                        )
                    ],
                    self::STYLES => [
                        'A*' => $this->linkStyle
                    ]
                ];

                /** Insert item header */
                $pageItemData[] = $this->itemHeaders;

                if ( ! empty($pageItems)) {
                    /** @var PageItem $item */
                    foreach ($pageItems as $index => $pageItem) {
                        /** Insert page item data */
                        $pageItemData[] = [
                            self::ROWS => [
                                ++$index,
                                $pageItem->getKey(),
                                $pageItem->name
                            ],
                            self::STYLES => [
                                'A*' => [
                                    self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                ]
                            ]
                        ];

                        /** Insert down symbol  */
                        $pageItemData[] = [
                            self::ROWS => [
                                '',
                                '',
                                self::DOWN_SYMBOL
                            ],
                            self::STYLES => [
                                'C*' => [
                                    self::FONT_WEIGHT => 'bold',
                                    self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                ]
                            ]
                        ];

                        if ($pageItem->globalItem) {
                            /** Insert item option header */
                            $pageItemData[] = [
                                self::ROWS => [
                                    '',
                                    '',
                                    'GLOBAL ITEM ID',
                                    'GLOBAL ITEM NAME',
                                    'TYPE'
                                ],
                                self::STYLES => [
                                    'C*:E*' => [
                                        self::BACKGROUND => '#305496',
                                        self::FONT_COLOR => '#ffffff',
                                        self::FONT_WEIGHT => 'bold'
                                    ]
                                ]
                            ];

                            $globalItem = $pageItem->globalItem;

                            if ($globalItem->is_visible) {
                                $pageItemData[] = [
                                    self::ROWS => [
                                        '',
                                        '',
                                        $globalItem->getKey(),
                                        $globalItem->name,
                                        self::USING_DATA_FROM_GLOBAL_ITEM
                                    ],
                                    self::STYLES => [
                                        'D*' => $this->linkStyle,
                                        'E*' => [
                                            self::FONT_WEIGHT => 'bold',
                                            self::FONT_COLOR => '#FF0000'
                                        ]
                                    ],
                                    self::FORMULAS => [
                                        'D*' => $this->generateHyperlinkFormula(
                                            '#' . self::GLOBAL_ITEM . preg_replace('/-/', '', $globalItem->getKey()),
                                            $globalItem->name
                                        )
                                    ],
                                ];
                            } else {
                                /** Insert item option header */
                                $pageItemData[] = $this->itemOptionHeaders;

                                /** Insert empty translation row */
                                $pageItemData[] = [
                                    self::ROWS => [
                                        '',
                                        '',
                                        self::NO_TRANSLATIONS
                                    ],
                                    self::STYLES => [
                                        'C*' => [
                                            self::FONT_WEIGHT => 'bold',
                                            self::FONT_COLOR => '#FF0000'
                                        ]
                                    ]
                                ];
                            }
                        } else {
                            /** Insert item option header */
                            $pageItemData[] = $this->itemOptionHeaders;

                            $pageItemOptions = $this->filterNecessaryItemOptions($pageItem->pageItemOptions);

                            if (empty($pageItemOptions)) {
                                /** Insert empty translation row */
                                $pageItemData[] = [
                                    self::ROWS => [
                                        '',
                                        '',
                                        self::NO_TRANSLATIONS
                                    ],
                                    self::STYLES => [
                                        'C*' => [
                                            self::FONT_WEIGHT => 'bold',
                                            self::FONT_COLOR => '#FF0000'
                                        ]
                                    ]
                                ];
                            } else {
                                /** @var \App\Api\Models\PageItemOption $pageItemOption */
                                foreach ($pageItemOptions as $pageItemOption) {
                                    /** Insert page item option data */
                                    $rowData = [
                                        self::ROWS => [
                                            '',
                                            '',
                                            $pageItemOption->getKey(),
                                            $pageItemOption->name,
                                            $pageItemOption->elementType->element_type
                                        ],
                                        self::NAMED_RANGE => [],
                                        self::FORMULAS => []
                                    ];

                                    if ($pageItemOption->elementType->element_type === OptionElementTypeConstants::CONTROL_LIST) {
                                        /** Control list specific */
                                        $rowData[self::STYLES] = [
                                            'E*' => [
                                                self::FONT_COLOR => '#c65911',
                                                self::FONT_WEIGHT => 'bold'
                                            ],
                                            'D*' => $this->linkStyle
                                        ];

                                        $namedRange = self::PAGE_ITEM_OPTION . preg_replace('/-/', '', $pageItem->getKey());
                                        $controlListNamedRange = self::CONTROL_LIST . $namedRange;

                                        $rowData[self::NAMED_RANGE] = [
                                            [
                                                self::COORDINATION => 'D*',
                                                self::NAME => $namedRange
                                            ]
                                        ];

                                        $rowData[self::FORMULAS] = [
                                            'D*' => $this->generateHyperlinkFormula('#' . $controlListNamedRange, $pageItemOption->name)
                                        ];

                                        /** Insert data into control list sheet */
                                        $controlListRowData = $this->generateControlListRowData($pageItem, $pageItemOption, $namedRange, $controlListNamedRange, $subControlListData);
                                        $controlListData = array_merge($controlListData, $controlListRowData);
                                    } else {
                                        /** Normal data */
                                        if ( ! empty($this->languageColumns)) {
                                            if (count($this->languageColumns) === 1) {
                                                $rowData[self::UNPROTECTED_CELLS] = [$this->languageColumns[0] . '*'];
                                            } else {
                                                $firstLanguageColumn = array_first($this->languageColumns);
                                                $lastLanguageColumn = array_last($this->languageColumns);
                                                $rowData[self::UNPROTECTED_CELLS] = [$firstLanguageColumn . '*:' . $lastLanguageColumn . '*'];
                                            }

                                            foreach ($this->siteLanguages as $siteLanguage) {
                                                $translation = $pageItemOption->withOptionSiteTranslation($siteLanguage->getKey());
                                                $rowData[self::ROWS][] = array_key_exists('translated_text', $translation) ? $translation['translated_text'] : $translation['option_value'];
                                            }
                                        }
                                    }

                                    $pageItemData[] = $rowData;
                                }
                            }
                        }

                        $pageItemData[] = [
                            self::ROWS => ['']
                        ];
                    }
                }

                $pageItemData[] = [
                    self::ROWS => [
                        '',
                        '',
                        '',
                        '',
                        ''
                    ],
                    self::STYLES => [
                        'A*:E*' => [
                            self::BACKGROUND => '#305496'
                        ]
                    ]
                ];

                $pageItemData[] = [
                    self::ROWS => [
                        self::BACK_TO . ' ' . self::SITE
                    ],
                    self::FORMULAS => [
                        'A*' => $this->generateHyperlinkFormula(
                            '#' . self::SITE . self::PAGE . preg_replace('/-/', '', $page->getKey()),
                            self::BACK_TO . ' ' . self::SITE
                        )
                    ],
                    self::STYLES => [
                        'A*' => $this->linkStyle
                    ]
                ];

                $pageData[] = [
                    self::SHEET_NAME => $sheetName,
                    self::DATA => $pageItemData,
                    self::FREEZE => 'F6'
                ];
            }

            $sheets = array_merge($sheets, $pageData);
        }

        /**
         * Sub control list
         */
        array_unshift($sheets, [
            self::SHEET_NAME => self::SUB_CONTROL_LIST,
            self::DATA => $subControlListData,
            self::COLUMN_WIDTH_SIZES => [
                'A' => 15,
                'B' => 15,
                'C' => 30,
                'D' => 15,
                'E' => 30,
                'F' => 30
            ]
        ]);

        /**
         * Control list
         */
        array_unshift($sheets, [
            self::SHEET_NAME => self::CONTROL_LIST,
            self::DATA => $controlListData,
            self::COLUMN_WIDTH_SIZES => [
                'A' => 15,
                'B' => 15,
                'C' => 30,
                'D' => 15,
                'E' => 30,
                'F' => 30
            ]
        ]);

        /**
         * Site
         */
        $siteData = [
            [
                self::ROWS => [
                    self::SITE_ID,
                    $site->getKey()
                ]
            ],
            [
                self::ROWS => [
                    self::SITE_DOMAIN_NAME,
                    $site->domain_name
                ]
            ]
        ];

        /** Insert site instruction */
        $this->pushFlatten($this->siteInstructions, $siteData);

        $maxNamedRanges = max(count($globalItemNamedRanges), count($pageNamedRanges));

        /** Insert site header */
        $this->pushFlatten($this->siteHeaders, $siteData);

        $globalItemDone = false;
        $globalItemLast = false;
        $pageDone = false;
        $pageLast = false;

        for ($i = 0; $i < $maxNamedRanges; $i++) {
            $data = [
                self::ROWS => [],
                self::STYLES => [
                    'A*' => [
                        self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ],
                    'D*' => [
                        self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                    ]
                ],
                self::NAMED_RANGE => [],
                self::FORMULAS => []
            ];

            if ( ! $globalItemDone) {
                if ($globalItemLast) {
                    $data[self::ROWS][] = '';
                    $data[self::ROWS][] = '';

                    $globalItemDone = true;
                } else {
                    if (count($globalItemNamedRanges) === 0) {
                        $data[self::ROWS][] = self::NO_DATA;
                        $data[self::ROWS][] = '';

                        $globalItemLast = true;
                    } else {
                        if (array_key_exists($i, $globalItemNamedRanges)) {
                            $data[self::ROWS][] = $i + 1;
                            $data[self::ROWS][] = $globalItemNamedRanges[$i][self::LABEL];
                            $data[self::FORMULAS]['B*'] = $this->generateHyperlinkFormula('#' . $globalItemNamedRanges[$i][self::NAME], $globalItemNamedRanges[$i][self::LABEL]);
                            $data[self::STYLES]['B*'] = $this->linkStyle;
                            $data[self::NAMED_RANGE][] = [
                                self::COORDINATION => 'B*',
                                self::NAME => $globalItemNamedRanges[$i][self::BACK_LINK]
                            ];
                        } else {
                            $data[self::ROWS][] = '';
                            $data[self::ROWS][] = '';

                            $globalItemLast = true;
                        }
                    }
                }
            } else {
                $data[self::ROWS][] = '';
                $data[self::ROWS][] = '';
            }

            $data[self::ROWS][] = '';

            if ( ! $pageDone) {
                if ($pageLast) {
                    $data[self::ROWS][] = '';
                    $data[self::ROWS][] = '';

                    $pageDone = true;
                } else {
                    if (count($pageNamedRanges) === 0) {
                        $data[self::ROWS][] = self::NO_DATA;
                        $data[self::ROWS][] = '';

                        $pageLast = true;
                    } else {
                        if (array_key_exists($i, $pageNamedRanges)) {
                            $data[self::ROWS][] = $i + 1;
                            $data[self::ROWS][] = $pageNamedRanges[$i][self::LABEL];
                            $data[self::FORMULAS]['E*'] = $this->generateHyperlinkFormula('#' . $pageNamedRanges[$i][self::NAME], $pageNamedRanges[$i][self::LABEL]);
                            $data[self::STYLES]['E*'] = $this->linkStyle;
                            $data[self::NAMED_RANGE][] = [
                                self::COORDINATION => 'E*',
                                self::NAME => $pageNamedRanges[$i][self::BACK_LINK]
                            ];
                        } else {
                            $pageLast = true;
                        }
                    }
                }
            }

            $siteData[] = $data;
        }

        array_unshift($sheets, [
            self::SHEET_NAME => self::SITE,
            self::DATA => $siteData,
            self::COLUMN_WIDTH_SIZES => [
                'A' => 15,
                'B' => 30,
                'C' => 15,
                'D' => 15,
                'E' => 30
            ]
        ]);

        /**
         * Control List
         */

        if (empty($sheets)) throw new \Exception('No data');

        /** Export */
        /** @noinspection PhpUndefinedMethodInspection */
        return Excel::create($filename, function ($excel) use ($filename, $sheets, $password) {
            /** @noinspection PhpUndefinedMethodInspection */
            $excel->setTitle($filename)
                ->setCreator('QUIQ CMS')
                ->setCompany('QUO-Global');

            foreach ($sheets as $sheetData) {
                $sheetName = $this->getValidSheetName($sheetData[self::SHEET_NAME]);

                /** @noinspection PhpUndefinedMethodInspection */
                $excel->sheet($sheetName, function ($sheet) use ($excel, $sheetData, $password) {
                    $widthSizes = array_key_exists(self::COLUMN_WIDTH_SIZES, $sheetData) ? $sheetData[self::COLUMN_WIDTH_SIZES] : $this->columnWidthSizes;
                    $freeze = array_key_exists(self::FREEZE, $sheetData) && ! empty($sheetData[self::FREEZE]) ? $sheetData[self::FREEZE] : false;

                    /** @noinspection PhpUndefinedMethodInspection */
                    $sheet->setAutoSize(false);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $sheet->setWidth($widthSizes);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $sheet->protect($password);

                    if ($freeze) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $sheet->setFreeze($freeze);
                    }

                    if ( ! empty($sheetData[self::DATA])) {
                        foreach ($sheetData[self::DATA] as $index => $rowData) {
                            $row = ++$index;
                            $rows = $rowData[self::ROWS];
                            $styles = array_key_exists(self::STYLES, $rowData) ? $rowData[self::STYLES] : [];
                            $protectCells = array_key_exists(self::PROTECTED_CELLS, $rowData) ? $rowData[self::PROTECTED_CELLS] : [];
                            $unprotectCells = array_key_exists(self::UNPROTECTED_CELLS, $rowData) ? $rowData[self::UNPROTECTED_CELLS] : [];
                            $namedRanges = array_key_exists(self::NAMED_RANGE, $rowData) ? $rowData[self::NAMED_RANGE] : [];
                            $links = array_key_exists(self::LINKS, $rowData) ? $rowData[self::LINKS] : [];
                            $formulas = array_key_exists(self::FORMULAS, $rowData) ? $rowData[self::FORMULAS] : [];

                            /** @noinspection PhpUndefinedMethodInspection */
                            $sheet->row($row, $rows);

                            if ( ! empty($protectCells)) {
                                foreach ($protectCells as $coordination) {
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $sheet->protectCells($this->applyExcelCoordination($coordination, $row), $password);
                                }
                            }

                            if ( ! empty($unprotectCells)) {
                                foreach ($unprotectCells as $coordination) {
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $sheet
                                        ->getStyle($this->applyExcelCoordination($coordination, $row))
                                        ->getProtection()
                                        ->setLocked(\PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                                }
                            }

                            if ( ! empty($namedRanges)) {
                                foreach ($namedRanges as $namedRange) {
                                    $this->applyNamedRange($excel, $sheet, $namedRange, $row);
                                }
                            }

                            $this->applyHyperlinks($sheet, $links, $row);

                            $this->applyFormulas($sheet, $formulas, $row);

                            $this->applyCellStyles($sheet, $styles, $row);
                        }
                    }

                    /** @noinspection PhpUndefinedMethodInspection */
                    $sheet->getProtection()->setSheet(true);
                });
            }

            /** @noinspection PhpUndefinedMethodInspection */
            $excel->setActiveSheetIndex(0);
            /** @noinspection PhpUndefinedMethodInspection */
            $excel->getSecurity()->setLockWindows(true);
            /** @noinspection PhpUndefinedMethodInspection */
            $excel->getSecurity()->setLockStructure(true);
            /** @noinspection PhpUndefinedMethodInspection */
            $excel->getSecurity()->setWorkbookPassword($password);
        })->string('xlsx');
    }

    /**
     * Import excel file into database
     *
     * @param Site $site
     * @param $file
     * @throws \Exception
     */
    public function import(Site $site, $file)
    {
        /**
         * Sheet extraction
         */

        /** @noinspection PhpUndefinedMethodInspection */
        $allSheets = Excel::load($file)->all();

        /** @noinspection PhpUndefinedMethodInspection */
        $siteOnlyData = Excel::selectSheets(self::SITE)->load($file)->takeRows(1)->all();

        /** @noinspection PhpUndefinedMethodInspection */
        $subControlListOnlyData = Excel::selectSheets(self::SUB_CONTROL_LIST)->load($file)->all();

        /** @noinspection PhpUndefinedMethodInspection */
        $controlListOnlyData = Excel::selectSheets(self::CONTROL_LIST)->load($file)->all();

        /** @noinspection PhpUndefinedMethodInspection */
        $globalHeaderData = Excel::selectSheets(self::GLOBAL_ITEM)->load($file)->skipRows(3)->takeRows(1)->all();

        /** @noinspection PhpUndefinedMethodInspection */
        $globalOnlyData = Excel::selectSheets(self::GLOBAL_ITEM)->load($file)->skipRows(4)->all();

        /**
         * Site validation
         */

        foreach ($siteOnlyData as $siteSheetData) {
            foreach ($siteSheetData as $siteRowData) {
                if ( ! isset($siteRowData[1]) || $siteRowData[1] !== $site->getKey()) throw new \Exception(self::INVALID_SITE);
                break 2;
            }
        }

        /**
         * Sub control list data gathering
         */

        $subControlListImportedData = $this->extractSubControlListImportedData($subControlListOnlyData);


        /**
         * Control list data gathering
         */

        $controlListImportedData = $this->extractControlListImportedData($controlListOnlyData, $subControlListImportedData);

        /**
         * Global data gathering
         */

        $globalImportedData = [];

        $globalLanguageCodes = [];

        foreach ($globalHeaderData as $globalHeaderSheetData) {
            foreach ($globalHeaderSheetData as $globalHeaderRowData) {
                if (empty($globalHeaderRowData)) throw new \Exception(self::INVALID_FORMAT);

                /** @noinspection PhpUndefinedMethodInspection */
                $globalHeaderRowData->each(function ($value, $key) use (&$globalLanguageCodes) {
                    if (preg_match('/(?:[\S\s]+ \()([\S\s]+)(?:\))/', $value, $matches)) {
                        $globalLanguageCodes[$key] = $matches[1];
                    }
                });

                break 2;
            }
        }

        foreach ($globalOnlyData as $globalSheetData) {
            $temp = [];
            $tempId = null;
            $isItemStarted = false;
            $isTranslationStarted = false;
            $isItemSkipped = false;
            $itemSkipRowTarget = null;
            $itemSkipHeaderRows = 3;
            $itemSkipFooterRows = 2;
            $rowCount = 0;
            $itemNumber = 1;
            $last = count($globalSheetData);

            foreach ($globalSheetData as $key => $globalRowData) {
                $firstCell = isset($globalRowData[0]) ? $globalRowData[0] : null;
                $thirdCell = isset($globalRowData[2]) ? $globalRowData[2] : null;
                $rowCount++;

                if ($key + 1 === $last) {
                    if ( ! empty($temp)) {
                        $globalImportedData = array_merge($globalImportedData, $temp);
                    }
                }

                // Skip rows
                if ($isItemSkipped && ! is_null($itemSkipRowTarget)) {
                    if ($rowCount > $itemSkipRowTarget) {
                        $isItemSkipped = false;
                        $rowCount = 0;
                        $itemSkipRowTarget = null;
                    } else {
                        continue;
                    }
                }

                if ($firstCell == $itemNumber) {
                    $id = isset($globalRowData[1]) ? $globalRowData[1] : null;
                    $itemNumber++;

                    if (is_null($id)) throw new \Exception(self::INVALID_FORMAT);

                    if ($isItemStarted) {
                        if ($isTranslationStarted) {
                            $globalImportedData = array_merge($globalImportedData, $temp);
                            $temp = [];
                            $tempId = null;
                            $rowCount = 1;

                            // New item
                            $tempId = $id;
                            $temp[$tempId] = [];
                            $isItemStarted = true;
                            $isItemSkipped = true;
                            $isTranslationStarted = true;
                            $itemSkipRowTarget = $itemSkipFooterRows;
                            continue;
                        }
                    } else {
                        // New item
                        $tempId = $id;
                        $temp[$tempId] = [];
                        $isItemStarted = true;
                        $isItemSkipped = true;
                        $itemSkipRowTarget = $itemSkipHeaderRows;
                        $isTranslationStarted = true;
                        continue;
                    }
                } else if ($isTranslationStarted) {
                    if ($thirdCell === self::NO_TRANSLATIONS) {
                        $globalImportedData = array_merge($globalImportedData, $temp);
                        $temp = [];
                        $tempId = null;
                        $isItemStarted = false;
                        $isTranslationStarted = false;
                        $rowCount = 1;
                        $isItemSkipped = true;
                        $itemSkipRowTarget = $itemSkipFooterRows;
                        continue;
                    } else if ( !! preg_match('/-/', $thirdCell) && $thirdCell !== self::BACK_TO . ' ' . self::SITE) {
                        $itemOptionId = $thirdCell;
                        $itemOptionType = isset($globalRowData[4]) ? $globalRowData[4] : null;
                        $translatedTexts = [];

                        if ($itemOptionType === OptionElementTypeConstants::CONTROL_LIST) {
                            if ($targetData = array_key_exists($itemOptionId, $controlListImportedData)
                                ? $controlListImportedData[$itemOptionId]
                                : null
                            ) {

                                foreach ($globalLanguageCodes as $code) {
                                    $translatedTexts[$code] = array_key_exists($code, $targetData) ? $targetData[$code] : null;
                                }
                            }
                        } else {
                            foreach ($globalLanguageCodes as $index => $code) {
                                $translatedTexts[$code] = isset($globalRowData[$index]) ? $globalRowData[$index] : null;
                            }
                        }


                        $temp[$tempId][$itemOptionId] = [
                            'type' => $itemOptionType,
                            'translatedText' => empty($translatedTexts) ? null : $translatedTexts
                        ];

                        continue;
                    }
                }
            }
        }

        /**
         * Page data gathering
         */

        $pageLanguageCodes = $globalLanguageCodes;

        $sheetExceptions = [
            self::SITE,
            self::GLOBAL_ITEM,
            self::CONTROL_LIST,
            self::SUB_CONTROL_LIST
        ];

        $pageImportedData = [];

        foreach ($allSheets as $sheet) {
            /** @noinspection PhpUndefinedMethodInspection */
            $title = $sheet->getTitle();

            $temp = [];
            $tempPageId = null;
            $tempItemId = null;
            $isItemStarted = false;
            $isTranslationStarted = false;
            $isItemSkipped = false;
            $itemSkipRowTarget = null;
            $pageSkipHeaderRows = 7;
            $itemSkipHeaderRows = 2;
            $itemSkipFooterRows = 1;
            $rowCount = 0;
            $itemNumber = 1;
            $last = count($sheet);

            if ( ! in_array($title, $sheetExceptions)) {
                foreach ($sheet as $key => $row) {
                    $firstCell = isset($row[0]) ? $row[0] : null;
                    $thirdCell = isset($row[2]) ? $row[2] : null;
                    $rowCount++;

                    if ($key + 1 === $last) {
                        if ( ! empty($temp)) {
                            $pageImportedData = array_merge($pageImportedData, $temp);
                        }
                    }

                    // Skip rows
                    if ($isItemSkipped && ! is_null($itemSkipRowTarget)) {
                        if ($rowCount > $itemSkipRowTarget) {
                            $isItemSkipped = false;
                            $rowCount = 0;
                            $itemSkipRowTarget = null;
                        } else {
                            continue;
                        }
                    }

                    if ($firstCell === self::PAGE_ID) {
                        $pageId = isset($row[1]) ? $row[1] : null;

                        if (is_null($pageId)) throw new \Exception(self::INVALID_FORMAT);

                        // New page
                        $tempPageId = $pageId;
                        $temp[$tempPageId] = [];
                        $isItemSkipped = true;
                        $itemSkipRowTarget = $pageSkipHeaderRows;
                        continue;
                    } else if ($firstCell == $itemNumber) {
                        $id = isset($row[1]) ? $row[1] : null;
                        $itemNumber++;

                        if (is_null($id)) throw new \Exception(self::INVALID_FORMAT);

                        if ($isItemStarted) {
                            if ($isTranslationStarted) {
                                $tempItemId = null;
                                $rowCount = 1;

                                // New item
                                $tempItemId = $id;
                                $temp[$tempPageId][$tempItemId] = [];
                                $isItemStarted = true;
                                $isItemSkipped = true;
                                $itemSkipRowTarget = $itemSkipHeaderRows;
                                $isTranslationStarted = true;
                                continue;
                            }
                        } else {
                            // New item
                            $tempItemId = $id;
                            $temp[$tempPageId][$tempItemId] = [];
                            $isItemStarted = true;
                            $isItemSkipped = true;
                            $itemSkipRowTarget = $itemSkipHeaderRows;
                            $isTranslationStarted = true;
                            continue;
                        }
                    } else if ($isTranslationStarted) {
                        if ($thirdCell === self::NO_TRANSLATIONS) {
                            $tempId = null;
                            $isItemStarted = false;
                            $isTranslationStarted = false;
                            $rowCount = 1;
                            $isItemSkipped = true;
                            $itemSkipRowTarget = $itemSkipFooterRows;
                            continue;
                        } else if ( !! preg_match('/-/', $thirdCell)) {
                            $itemOptionId = $thirdCell;
                            $itemOptionType = isset($row[4]) ? $row[4] : null;
                            $translatedTexts = [];

                            if ($itemOptionType !== self::USING_DATA_FROM_GLOBAL_ITEM) {
                                if ($itemOptionType === OptionElementTypeConstants::CONTROL_LIST) {
                                    if ($targetData = array_key_exists($itemOptionId, $controlListImportedData)
                                        ? $controlListImportedData[$itemOptionId]
                                        : null
                                    ) {

                                        foreach ($pageLanguageCodes as $code) {
                                            $translatedTexts[$code] = array_key_exists($code, $targetData) ? $targetData[$code] : null;
                                        }
                                    }
                                } else {
                                    foreach ($pageLanguageCodes as $index => $code) {
                                        $translatedTexts[$code] = isset($row[$index]) ? $row[$index] : null;
                                    }
                                }

                                $temp[$tempPageId][$tempItemId][$itemOptionId] = [
                                    'type' => $itemOptionType,
                                    'translatedText' => empty($translatedTexts) ? null : $translatedTexts
                                ];

                                continue;
                            }
                        }
                    }
                }
            }
        }

        /**
         * Apply data
         */

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($globalImportedData, $pageImportedData, $site) {
            // Global Items
            if (count($globalImportedData) > 0) {
                $globalItemIds = array_keys($globalImportedData);

                /** @var GlobalItem|GlobalItem[]|\Illuminate\Support\Collection $globalItems */
                $globalItems = $site
                    ->globalItems()
                    ->whereIn('global_items.id', $globalItemIds)
                    ->get();

                if (count($globalItems) > 0) {
                    /** @var GlobalItem $globalItem */
                    foreach ($globalItems as $globalItem) {
                        if ($targetImportedGlobalItemData = isset($globalImportedData[$globalItem->getKey()])
                            ? $globalImportedData[$globalItem->getKey()]
                            : null
                        ) {

                            $globalItemOptionIds = array_keys($targetImportedGlobalItemData);

                            /** @var GlobalItemOption|GlobalItemOption[]|\Illuminate\Support\Collection $globalItems */
                            $globalItemOptions = $globalItem
                                ->globalItemOptions()
                                ->whereIn('global_item_options.id', $globalItemOptionIds)
                                ->get();

                            if (count($globalItemOptions) > 0) {
                                /** @var GlobalItemOption $globalItemOption */
                                foreach ($globalItemOptions as $globalItemOption) {
                                    if ($targetImportedGlobalItemOptionData = isset($targetImportedGlobalItemData[$globalItemOption->getKey()])
                                        ? $targetImportedGlobalItemData[$globalItemOption->getKey()]
                                        : null
                                    ) {

                                        $globalItemOptionArray = $globalItemOption->getOptionSiteTranslation();
                                        $translatedTexts = $targetImportedGlobalItemOptionData['translatedText'];
                                        $defaultValue = ($pageItemOptionOptionArray = $globalItemOption->getOptionValue())
                                            ? $pageItemOptionOptionArray->option_value
                                            : null;

                                        if ( ! empty($translatedTexts) && ! is_null($translatedTexts)) {
                                            foreach ($translatedTexts as $code => $targetImportedTranslatedText) {
                                                $translation = collect($globalItemOptionArray)
                                                    ->where('language_code', $code)
                                                    ->first();
                                                $translatedText = isset($translation->translated_text)
                                                    ? $translation->translated_text
                                                    : $defaultValue;

                                                if ($targetImportedGlobalItemOptionData['type'] === OptionElementTypeConstants::CONTROL_LIST) {
                                                    $toBeSaved = $this->applyControlListTranslatedText($translatedText, $targetImportedTranslatedText);
                                                } else {
                                                    $toBeSaved = $targetImportedTranslatedText;
                                                }

                                                if ($translatedText !== $toBeSaved) {
                                                    $globalItemOption->upsertOptionSiteTranslation($code, $toBeSaved, false);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Pages
            if (count($pageImportedData) > 0) {
                $pageIds = array_keys($pageImportedData);

                /** @var Page|Page[]|\Illuminate\Support\Collection $pages */
                $pages = $site
                    ->pages()
                    ->whereIn('pages.id', $pageIds)
                    ->get();

                if (count($pages) > 0) {
                    /** @var Page $page */
                    foreach ($pages as $page) {
                        if ($targetImportedPageData = isset($pageImportedData[$page->getKey()])
                            ? $pageImportedData[$page->getKey()]
                            : null
                        ) {

                            $pageItemIds = array_keys($targetImportedPageData);

                            /** @var PageItem|PageItem[]|\Illuminate\Support\Collection $pageItems */
                            $pageItems = $page
                                ->pageItems()
                                ->whereIn('page_items.id', $pageItemIds)
                                ->get();

                            if (count($pageItems) > 0) {
                                /** @var PageItem $pageItem */
                                foreach ($pageItems as $pageItem) {
                                    if ($targetImportedPageItemData = isset($targetImportedPageData[$pageItem->getKey()])
                                        ? $targetImportedPageData[$pageItem->getKey()]
                                        : null
                                    ) {

                                        $pageItemOptionIds = array_keys($targetImportedPageItemData);

                                        $pageItemOptions = $pageItem
                                            ->pageItemOptions()
                                            ->whereIn('page_item_options.id', $pageItemOptionIds)
                                            ->get();

                                        if (count($pageItemOptions) > 0) {
                                            /** @var PageItemOption $pageItemOption */
                                            foreach ($pageItemOptions as $pageItemOption) {
                                                if ($targetImportedPageItemOptionData = isset($targetImportedPageItemData[$pageItemOption->getKey()])
                                                    ? $targetImportedPageItemData[$pageItemOption->getKey()]
                                                    : null
                                                ) {

                                                    $pageItemOptionTranslationArray = $pageItemOption->getOptionSiteTranslation();
                                                    $translatedTexts = $targetImportedPageItemOptionData['translatedText'];
                                                    $defaultValue = ($pageItemOptionOptionArray = $pageItemOption->getOptionValue())
                                                        ? $pageItemOptionOptionArray->option_value
                                                        : null;

                                                    if ( ! empty($translatedTexts) && ! is_null($translatedTexts)) {
                                                        foreach ($translatedTexts as $code => $targetImportedTranslatedText) {
                                                            $translation = collect($pageItemOptionTranslationArray)
                                                                ->where('language_code', $code)
                                                                ->first();
                                                            $translatedText = isset($translation->translated_text)
                                                                ? $translation->translated_text
                                                                : $defaultValue;

                                                            if ($targetImportedPageItemOptionData['type'] === OptionElementTypeConstants::CONTROL_LIST) {
                                                                $toBeSaved = $this->applyControlListTranslatedText($translatedText, $targetImportedTranslatedText);
                                                            } else {
                                                                $toBeSaved = $targetImportedTranslatedText;
                                                            }

                                                            if ($translatedText !== $toBeSaved) {
                                                                $pageItemOption->upsertOptionSiteTranslation($code, $toBeSaved, false);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Return excel column from number
     *
     * @param $num
     * @return string
     */
    private function getExcelColumnFromNumber($num)
    {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return $this->getExcelColumnFromNumber($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }

    /**
     * Return push-flatten array
     *
     * @param array $array
     * @param array $appendTo
     * @return array
     */
    private function pushFlatten(array $array, &$appendTo = [])
    {
        array_map(function ($item) use (&$appendTo) {
            $appendTo[] = $item;
        }, $array);
        return $appendTo;
    }

    /**
     * Filter necessary control list item options
     *
     * @param array $controlListItemOptions
     * @return array
     */
    private function filterNecessaryControlListItemOption($controlListItemOptions = [])
    {
        return collect($controlListItemOptions)
            ->filter(function ($option) {
                if ( ! $option->element_type) return false;
                if ($option->element_type === OptionElementTypeConstants::TEXTBOX) {
                    $value = json_decode($option->element_value);
                    $helper = isset($value->helper) ? $value->helper : '';
                    return empty($helper);
                }
                return in_array($option->element_type, [
                    OptionElementTypeConstants::MULTILINE_TEXT,
                    OptionElementTypeConstants::RICHTEXT_EDITOR,
                    OptionElementTypeConstants::CONTROL_LIST
                ]);
            })
            ->sortBy('name')
            ->all();
    }

    /**
     * Filter necessary item options
     *
     * @param array $itemOptions
     * @return array
     */
    private function filterNecessaryItemOptions($itemOptions = [])
    {
        return collect($itemOptions)
            ->filter(function ($option) {
                if ( ! $option->elementType) return false;
                if ($option->elementType->element_type === OptionElementTypeConstants::TEXTBOX) {
                    $value = json_decode($option->elementType->element_value);
                    $helper = isset($value->helper) ? $value->helper : '';
                    return empty($helper);
                }
                return in_array($option->elementType->element_type, [
                    OptionElementTypeConstants::MULTILINE_TEXT,
                    OptionElementTypeConstants::RICHTEXT_EDITOR,
                    OptionElementTypeConstants::CONTROL_LIST
                ]);
            })
            ->sortBy('name')
            ->all();
    }

    /**
     * Return item options without particular variable names
     *
     * @param array $itemOptions
     * @param array $variableNames
     * @return array
     */
    private function rejectItemOptionsByVariableNames($itemOptions = [], $variableNames = [])
    {
        return collect($itemOptions)
            ->reject(function ($option) use ($variableNames) {
                return in_array($option->variable_name, $variableNames);
            })
            ->sortBy('name')
            ->all();
    }

    /**
     * Return a valid sheet name
     *
     * @param $sheetName
     * @return mixed
     */
    private function getValidSheetName($sheetName)
    {
        $sheetName = str_replace($this->invalidSheetName, '', $sheetName);
        if (strlen($sheetName) > 31) {
            $sheetName = substr($sheetName, 0, 28) . '...';
        }
        return $sheetName;
    }

    /**
     * Return an excel coordination
     *
     * @param $coordination
     * @param $row
     * @return mixed
     */
    private function applyExcelCoordination($coordination, $row)
    {
        return preg_replace('/\*/', $row, $coordination);
    }

    /**
     * Apply formulas
     *
     * @param $sheet
     * @param $formulas
     * @param $row
     */
    private function applyFormulas(&$sheet, $formulas, $row)
    {
        if ( ! empty($formulas)) {
            foreach ($formulas as $coordination => $formula) {
                $coordination = $this->applyExcelCoordination($coordination, $row);

                if (preg_match('/:/', $coordination)) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $sheet->cells($coordination, function ($cells) use ($formula, $sheet) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $cells->setValue($formula);
                    });
                } else {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $sheet->cell($coordination, function ($cell) use ($formula, $sheet) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $cell->setValue($formula);
                    });
                }
            }
        }
    }

    /**
     * Apply hyperlinks
     *
     * @param $sheet
     * @param $links
     * @param $row
     */
    private function applyHyperlinks(&$sheet, $links, $row)
    {
        if ( ! empty($links)) {
            foreach ($links as $coordination => $link) {
                $coordination = $this->applyExcelCoordination($coordination, $row);

                if (preg_match('/:/', $coordination)) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $sheet->cells($coordination, function ($cells) use ($link, $sheet) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $cells->setUrl($link);
                    });
                } else {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $sheet->cell($coordination, function ($cell) use ($link, $sheet) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $cell->setUrl($link);
                    });
                }
            }
        }
    }

    /**
     * Apply a named range
     *
     * @param $excel
     * @param $sheet
     * @param $namedRange
     * @param $row
     */
    private function applyNamedRange(&$excel, &$sheet, $namedRange, $row)
    {
        if (array_key_exists(self::NAME, $namedRange) && array_key_exists(self::COORDINATION, $namedRange)) {
            $name = $namedRange[self::NAME];
            $coordination = preg_match('/\*/', $namedRange[self::COORDINATION])
                ? $this->applyExcelCoordination($namedRange[self::COORDINATION], $row)
                : $namedRange[self::COORDINATION];
            /** @noinspection PhpUndefinedMethodInspection */
            $excel->addNamedRange(new \PHPExcel_NamedRange($name, $sheet, $coordination));
        }
    }

    /**
     * Apply cell styles
     *
     * @param $sheet
     * @param $styles
     * @param $row
     */
    private function applyCellStyles(&$sheet, $styles, $row)
    {
        if ( ! empty($styles)) {
            foreach ($styles as $coordination => $style) {
                $coordination = $this->applyExcelCoordination($coordination, $row);

                if (preg_match('/:/', $coordination)) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $sheet->cells($coordination, function ($cells) use ($style, $sheet) {
                        foreach ($style as $key => $value) {
                            switch ($key) {
                                case self::FONT_COLOR:
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $cells->setFontColor($value);
                                    break;
                                case self::FONT_FAMILY:
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $cells->setFontFamily($value);
                                    break;
                                case self::FONT_SIZE:
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $cells->setFontSize($value);
                                    break;
                                case self::FONT_WEIGHT:
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $cells->setFontWeight($value);
                                    break;
                                case self::BACKGROUND:
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $cells->setBackground($value);
                                    break;
                                case self::HORIZONTAL_ALIGNMENT:
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $cells->setAlignment($value);
                                    break;
                                case self::VERTICAL_ALIGNMENT:
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $cells->setValignment($value);
                                    break;
                                default:
                                    break;
                            }
                        }
                    });
                } else {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $sheet->cell($coordination, function ($cell) use ($style) {
                        foreach ($style as $key => $value) {
                            switch ($key) {
                                case self::FONT_COLOR:
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $cell->setFontColor($value);
                                    break;
                                case self::FONT_FAMILY:
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $cell->setFontFamily($value);
                                    break;
                                case self::FONT_SIZE:
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $cell->setFontSize($value);
                                    break;
                                case self::FONT_WEIGHT:
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $cell->setFontWeight($value);
                                    break;
                                case self::BACKGROUND:
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $cell->setBackground($value);
                                    break;
                                case self::HORIZONTAL_ALIGNMENT:
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $cell->setAlignment($value);
                                    break;
                                case self::VERTICAL_ALIGNMENT:
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $cell->setValignment($value);
                                    break;
                                default:
                                    break;
                            }
                        }
                    });
                }
            }
        }
    }

    /**
     * Return hyperlink formula
     *
     * @param $url
     * @param string $label
     * @return string
     */
    private function generateHyperlinkFormula($url, $label = '')
    {
        return '=HYPERLINK("' . $url . '","' . $label . '")';
    }

    /**
     * Return control list row data
     *
     * @param GlobalItem|PageItem $item
     * @param GlobalItemOption|PageItemOption $itemOption
     * @param $namedRange
     * @param $controlListNamedRange
     * @param $subControlListData
     * @return array
     */
    private function generateControlListRowData($item, $itemOption, $namedRange, $controlListNamedRange, &$subControlListData = [])
    {
        $controlListRowData = [
            [
                self::ROWS => [
                    self::CONTROL_LIST_ITEM_ID,
                    $itemOption->getKey()
                ],
                self::NAMED_RANGE => [
                    [
                        self::COORDINATION => 'A*',
                        self::NAME => $controlListNamedRange
                    ]
                ]
            ],
            [
                self::ROWS => [
                    self::CONTROL_LIST_NAME,
                    $itemOption->name
                ]
            ],
            [
                self::ROWS => [
                    self::CONTROL_LIST_FROM_ITEM,
                    $item->name
                ]
            ],
            [
                self::ROWS => [
                    self::BACK_TO . ' ITEM - ' . $itemOption->name
                ],
                self::STYLES => [
                    'A*' => $this->linkStyle
                ],
                self::FORMULAS => [
                    'A*' => $this->generateHyperlinkFormula('#' . $namedRange, self::BACK_TO . ' ITEM - ' . $itemOption->name)
                ]
            ]
        ];

        $controlListRowData[] = $this->controlListItemOptionHeaders;

        /** @var Language $siteLanguage */
        foreach ($this->siteLanguages as $siteLanguage) {
            $controlListRowData[] = [
                self::ROWS => [
                    $siteLanguage->name . ' (' . $siteLanguage->getKey() . ')'
                ],
                self::STYLES => [
                    'A*:F*' => $this->languageHeaderStyle
                ]
            ];

            $translation = $itemOption->withOptionSiteTranslation($siteLanguage->getKey());

            if ($translatedText = array_key_exists('translated_text', $translation) ? $translation['translated_text'] : $translation['option_value']) {
                if ($controlLists = json_decode($translatedText)) {
                    if (is_array($controlLists)) {
                        foreach ($controlLists as $index => $controlList) {
                            $id = isset($controlList->id) ? $controlList->id : null;
                            $props = isset($controlList->props) ? $controlList->props : null;
                            $globalItemId = isset($controlList->global_item_id) ? $controlList->global_item_id : null;
                            $props = $this->filterNecessaryControlListItemOption($props);
                            $first = true;

                            if ( ! empty($id)) {
                                if ( ! empty($globalItemId)) {
                                    /** @var GlobalItem|null $globalItem */
                                    if ($globalItem = GlobalItem::find($globalItemId)) {
                                        if ($globalItem->is_visible) {
                                            $controlListRowData[] = [
                                                self::ROWS => [
                                                    $index + 1,
                                                    '-',
                                                    $globalItem->name,
                                                    self::USING_DATA_FROM_GLOBAL_ITEM
                                                ],
                                                self::STYLES => [
                                                    'A*' => [
                                                        self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                                    ],
                                                    'C*' => $this->linkStyle,
                                                    'D*' => [
                                                        self::FONT_WEIGHT => 'bold',
                                                        self::FONT_COLOR => '#FF0000'
                                                    ]
                                                ],
                                                self::FORMULAS => [
                                                    'C*' => $this->generateHyperlinkFormula(
                                                        '#' . self::GLOBAL_ITEM . preg_replace('/-/', '', $globalItem->getKey()),
                                                        $globalItem->name
                                                    )
                                                ],
                                            ];
                                        } else {
                                            /** Insert empty translation row */
                                            $controlListRowData[] = [
                                                self::ROWS => [
                                                    $index + 1,
                                                    '-',
                                                    self::NO_TRANSLATIONS
                                                ],
                                                self::STYLES => [
                                                    'A*' => [
                                                        self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                                    ],
                                                    'C*' => [
                                                        self::FONT_WEIGHT => 'bold',
                                                        self::FONT_COLOR => '#FF0000'
                                                    ]
                                                ]
                                            ];
                                        }
                                    }
                                } else if ( ! empty($props)) {
                                    foreach ($props as $prop) {
                                        $propId = isset($prop->prop_id) ? $prop->prop_id : null;
                                        $variableName = isset($prop->variable_name) ? $prop->variable_name : null;
                                        $name = isset($prop->name) ? $prop->name : null;
                                        $type = isset($prop->element_type) ? $prop->element_type : null;
                                        $value = isset($prop->option_value) ? $prop->option_value : null;

                                        if ($type === OptionElementTypeConstants::CONTROL_LIST) {
                                            $rowData = [
                                                self::ROWS => [
                                                    $first ? $index + 1 : '',
                                                    $id . '.' . $propId . '.' . $variableName,
                                                    $name,
                                                    $type,
                                                    ''
                                                ],
                                                self::STYLES => [
                                                    'A*' => [
                                                        self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                                    ],
                                                    'C*' => $this->linkStyle,
                                                    'D*' => [
                                                        self::FONT_COLOR => '#c65911',
                                                        self::FONT_WEIGHT => 'bold'
                                                    ]
                                                ]
                                            ];

                                            $value = json_decode($value);

                                            if (empty($value)) {
                                                $rowData[self::ROWS][4] = self::NO_TRANSLATIONS;
                                                $rowData[self::STYLES]['E*'] = [self::FONT_WEIGHT => 'bold', self::FONT_COLOR => '#FF0000'];
                                                $controlListRowData[] = $rowData;
                                            } else {
                                                $tempNamedRange = self::CONTROL_LIST . preg_replace('/-/', '', $id . '.' . $propId) . '.' . $siteLanguage->getKey();
                                                $controlListNamedRange = self::CONTROL_LIST . $tempNamedRange;

                                                $rowData[self::NAMED_RANGE] = [
                                                    [
                                                        self::COORDINATION => 'C*',
                                                        self::NAME => $tempNamedRange
                                                    ]
                                                ];

                                                $rowData[self::FORMULAS] = [
                                                    'C*' => $this->generateHyperlinkFormula('#' . $controlListNamedRange, $name)
                                                ];

                                                $controlListRowData[] = $rowData;

                                                /** Insert data into control list sheet */
                                                $data = $this->generateSubControlListRowData(
                                                    $propId,
                                                    $itemOption->name,
                                                    $id,
                                                    $name,
                                                    $value,
                                                    $tempNamedRange,
                                                    $controlListNamedRange,
                                                    $siteLanguage
                                                );

                                                $subControlListData = array_merge($subControlListData, $data);
                                            }
                                        } else {
                                            $controlListRowData[] = [
                                                self::ROWS => [
                                                    $first ? $index + 1 : '',
                                                    $id . '.' . $propId . '.' . $variableName,
                                                    $name,
                                                    $type,
                                                    '',
                                                    $value
                                                ],
                                                self::STYLES => [
                                                    'A*' => [
                                                        self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                                    ]
                                                ],
                                                self::UNPROTECTED_CELLS => ['F*']
                                            ];
                                        }

                                        $first = false;
                                    }
                                } else {
                                    $controlListRowData[] = [
                                        self::ROWS => [
                                            self::NO_TRANSLATIONS
                                        ],
                                        self::STYLES => [
                                            'A*' => [
                                                self::FONT_WEIGHT => 'bold',
                                                self::FONT_COLOR => '#FF0000'
                                            ]
                                        ]
                                    ];

                                    break;
                                }
                            } else {
                                $controlListRowData[] = [
                                    self::ROWS => [
                                        self::NO_TRANSLATIONS
                                    ],
                                    self::STYLES => [
                                        'A*' => [
                                            self::FONT_WEIGHT => 'bold',
                                            self::FONT_COLOR => '#FF0000'
                                        ]
                                    ]
                                ];

                                break;
                            }
                        }
                    } else {
                        $controlListRowData[] = [
                            self::ROWS => [
                                self::NO_TRANSLATIONS
                            ],
                            self::STYLES => [
                                'A*' => [
                                    self::FONT_WEIGHT => 'bold',
                                    self::FONT_COLOR => '#FF0000'
                                ]
                            ]
                        ];
                    }
                } else {
                    $controlListRowData[] = [
                        self::ROWS => [
                            self::NO_TRANSLATIONS
                        ],
                        self::STYLES => [
                            'A*' => [
                                self::FONT_WEIGHT => 'bold',
                                self::FONT_COLOR => '#FF0000'
                            ]
                        ]
                    ];
                }
            } else {
                $controlListRowData[] = [
                    self::ROWS => [
                        self::NO_TRANSLATIONS
                    ],
                    self::STYLES => [
                        'A*' => [
                            self::FONT_WEIGHT => 'bold',
                            self::FONT_COLOR => '#FF0000'
                        ]
                    ]
                ];
            }
        }

        $controlListRowData[] = [
            self::ROWS => [
                '',
                '',
                '',
                '',
                '',
                ''
            ],
            self::STYLES => [
                'A*:F*' => [
                    self::BACKGROUND => '#305496'
                ]
            ]
        ];

        $controlListRowData[] = [
            self::ROWS => [
                self::BACK_TO . ' ITEM - ' . $itemOption->name
            ],
            self::STYLES => [
                'A*' => $this->linkStyle
            ],
            self::FORMULAS => [
                'A*' => $this->generateHyperlinkFormula('#' . $namedRange, self::BACK_TO . ' ITEM - ' . $itemOption->name)
            ]
        ];

        $controlListRowData[] = [
            self::ROWS => [
                ''
            ]
        ];

        $controlListRowData[] = [
            self::ROWS => [
                ''
            ]
        ];

        return $controlListRowData;
    }

    /**
     * Generate sub control list row data
     *
     * @param $itemId
     * @param $fromItemName
     * @param $fromItemId
     * @param $itemName
     * @param $controlLists
     * @param $namedRange
     * @param $controlListNamedRange
     * @param Language $siteLanguage
     * @return array
     */
    private function generateSubControlListRowData($itemId, $fromItemName, $fromItemId, $itemName, $controlLists, $namedRange, $controlListNamedRange, $siteLanguage)
    {
        $controlListRowData = [
            [
                self::ROWS => [
                    self::CONTROL_LIST_ITEM_ID,
                    $fromItemId . '.' . $itemId
                ],
                self::NAMED_RANGE => [
                    [
                        self::COORDINATION => 'A*',
                        self::NAME => $controlListNamedRange
                    ]
                ]
            ],
            [
                self::ROWS => [
                    self::CONTROL_LIST_NAME,
                    $itemName
                ]
            ],
            [
                self::ROWS => [
                    self::CONTROL_LIST_FROM_ITEM,
                    $fromItemName
                ]
            ],
            [
                self::ROWS => [
                    self::BACK_TO . ' ITEM - ' . $fromItemName
                ],
                self::STYLES => [
                    'A*' => $this->linkStyle
                ],
                self::FORMULAS => [
                    'A*' => $this->generateHyperlinkFormula('#' . $namedRange, self::BACK_TO . ' ITEM - ' . $fromItemName)
                ]
            ]
        ];

        $controlListRowData[] = $this->controlListItemOptionHeaders;

        $controlListRowData[] = [
            self::ROWS => [
                $siteLanguage->name . ' (' . $siteLanguage->getKey() . ')'
            ],
            self::STYLES => [
                'A*:F*' => $this->languageHeaderStyle
            ]
        ];

        foreach ($controlLists as $index => $controlList) {
            $id = isset($controlList->id) ? $controlList->id : null;
            $props = isset($controlList->props) ? $controlList->props : null;
            $globalItemId = isset($controlList->global_item_id) ? $controlList->global_item_id : null;
            $props = $this->filterNecessaryControlListItemOption($props);
            $first = true;

            if ( ! empty($id)) {
                if ( ! empty($globalItemId)) {
                    /** @var GlobalItem|null $globalItem */
                    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                    if ($globalItem = GlobalItem::where('id', $globalItemId)->first()) {
                        if ($globalItem->is_visible) {
                            $controlListRowData[] = [
                                self::ROWS => [
                                    $index + 1,
                                    '-',
                                    $globalItem->name,
                                    self::USING_DATA_FROM_GLOBAL_ITEM
                                ],
                                self::STYLES => [
                                    'A*' => [
                                        self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                    ],
                                    'C*' => $this->linkStyle,
                                    'D*' => [
                                        self::FONT_WEIGHT => 'bold',
                                        self::FONT_COLOR => '#FF0000'
                                    ]
                                ],
                                self::FORMULAS => [
                                    'C*' => $this->generateHyperlinkFormula(
                                        '#' . self::GLOBAL_ITEM . preg_replace('/-/', '', $globalItem->getKey()),
                                        $globalItem->name
                                    )
                                ],
                            ];
                        } else {
                            /** Insert empty translation row */
                            $controlListRowData[] = [
                                self::ROWS => [
                                    $index + 1,
                                    '-',
                                    self::NO_TRANSLATIONS
                                ],
                                self::STYLES => [
                                    'A*' => [
                                        self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                    ],
                                    'C*' => [
                                        self::FONT_WEIGHT => 'bold',
                                        self::FONT_COLOR => '#FF0000'
                                    ]
                                ]
                            ];
                        }
                    }
                } else if ( ! empty($props)) {
                    foreach ($props as $prop) {
                        $propId = isset($prop->prop_id) ? $prop->prop_id : null;
                        $variableName = isset($prop->variable_name) ? $prop->variable_name : null;
                        $name = isset($prop->name) ? $prop->name : null;
                        $type = isset($prop->element_type) ? $prop->element_type : null;
                        $value = isset($prop->option_value) ? $prop->option_value : null;

                        if ($type === OptionElementTypeConstants::CONTROL_LIST) {
                            $rowData = [
                                self::ROWS => [
                                    $first ? $index + 1 : '',
                                    $id . '.' . $propId . '.' . $variableName,
                                    $name,
                                    $type,
                                    ''
                                ],
                                self::STYLES => [
                                    'A*' => [
                                        self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                    ],
                                    'C*' => $this->linkStyle,
                                    'D*' => [
                                        self::FONT_COLOR => '#c65911',
                                        self::FONT_WEIGHT => 'bold'
                                    ]
                                ]
                            ];

                            $value = json_decode($value);

                            if (empty($value)) {
                                $rowData[self::ROWS][4] = self::NO_TRANSLATIONS;
                                $rowData[self::STYLES]['E*'] = [self::FONT_WEIGHT => 'bold', self::FONT_COLOR => '#FF0000'];
                                $controlListRowData[] = $rowData;
                            } else {
                                $tempNamedRange = self::CONTROL_LIST . preg_replace('/-/', '', $id . '.' . $propId) . '.' . $siteLanguage->getKey();
                                $controlListNamedRange = self::CONTROL_LIST . $tempNamedRange;

                                $rowData[self::NAMED_RANGE] = [
                                    [
                                        self::COORDINATION => 'C*',
                                        self::NAME => $tempNamedRange
                                    ]
                                ];

                                $rowData[self::FORMULAS] = [
                                    'C*' => $this->generateHyperlinkFormula('#' . $controlListNamedRange, $name)
                                ];

                                $controlListRowData[] = $rowData;

                                /** Insert data into control list sheet */
                                $data = $this->generateSubControlListRowData(
                                    $propId,
                                    $itemName,
                                    $id,
                                    $name,
                                    $value,
                                    $tempNamedRange,
                                    $controlListNamedRange,
                                    $siteLanguage
                                );
                            }
                        } else {
                            $controlListRowData[] = [
                                self::ROWS => [
                                    $first ? $index + 1 : '',
                                    $id . '.' . $propId . '.' . $variableName,
                                    $name,
                                    $type,
                                    '',
                                    $value
                                ],
                                self::STYLES => [
                                    'A*' => [
                                        self::HORIZONTAL_ALIGNMENT => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                                    ]
                                ],
                                self::UNPROTECTED_CELLS => ['F*']
                            ];
                        }

                        $first = false;
                    }
                } else {
                    $controlListRowData[] = [
                        self::ROWS => [
                            self::NO_TRANSLATIONS
                        ],
                        self::STYLES => [
                            'A*' => [
                                self::FONT_WEIGHT => 'bold',
                                self::FONT_COLOR => '#FF0000'
                            ]
                        ]
                    ];

                    break;
                }
            } else {
                $controlListRowData[] = [
                    self::ROWS => [
                        self::NO_TRANSLATIONS
                    ],
                    self::STYLES => [
                        'A*' => [
                            self::FONT_WEIGHT => 'bold',
                            self::FONT_COLOR => '#FF0000'
                        ]
                    ]
                ];

                break;
            }
        }

        $controlListRowData[] = [
            self::ROWS => [
                '',
                '',
                '',
                '',
                '',
                ''
            ],
            self::STYLES => [
                'A*:F*' => [
                    self::BACKGROUND => '#305496'
                ]
            ]
        ];

        $controlListRowData[] = [
            self::ROWS => [
                self::BACK_TO . ' ITEM - ' . $fromItemName
            ],
            self::STYLES => [
                'A*' => $this->linkStyle
            ],
            self::FORMULAS => [
                'A*' => $this->generateHyperlinkFormula('#' . $namedRange, self::BACK_TO . ' ITEM - ' . $fromItemName)
            ]
        ];

        $controlListRowData[] = [
            self::ROWS => [
                ''
            ]
        ];

        $controlListRowData[] = [
            self::ROWS => [
                ''
            ]
        ];

        if (isset($data)) {
            $controlListRowData = array_merge($controlListRowData, $data);
        }

        return $controlListRowData;
    }

    /**
     * Extract control-list imported data
     *
     * @param $controlListOnlyData
     * @param array $subControlListImportedData
     * @return array
     * @throws \Exception
     */
    private function extractControlListImportedData($controlListOnlyData, $subControlListImportedData = [])
    {
        if (empty($controlListOnlyData)) return [];

        $controlListImportedData = [];

        foreach ($controlListOnlyData as $controlListSheetData) {
            $temp = [];
            $tempId = null;
            $tempLanguageCode = null;
            $isItemStarted = false;
            $isItemOptionStarted = false;
            $isTranslationStarted = false;
            $isItemSkipped = false;
            $itemSkipRowTarget = null;
            $itemSkipHeaderRows = 5;
            $itemSkipFooterRows = 4;
            $rowCount = 0;
            $itemOptionNumber = 1;
            $last = count($controlListSheetData);

            foreach ($controlListSheetData as $key => $controlListRowData) {
                $firstCell = isset($controlListRowData[0]) ? $controlListRowData[0] : null;
                $rowCount++;

                if ($key + 1 === $last) {
                    if ( ! empty($temp)) {
                        $controlListImportedData = array_merge($controlListImportedData, $temp);
                    }
                }

                // Skip rows
                if ($isItemSkipped && ! is_null($itemSkipRowTarget)) {
                    if ($rowCount > $itemSkipRowTarget) {
                        $isItemSkipped = false;
                        $rowCount = 0;
                        $itemSkipRowTarget = null;
                    } else {
                        continue;
                    }
                }

                if ($firstCell === self::CONTROL_LIST_ITEM_ID) {
                    $id = isset($controlListRowData[1]) ? $controlListRowData[1] : null;

                    if (is_null($id)) throw new \Exception(self::INVALID_FORMAT);

                    if ($isItemStarted) {
                        if ($isTranslationStarted) {
                            $controlListImportedData = array_merge($controlListImportedData, $temp);
                            $temp = [];
                            $tempId = null;
                            $tempLanguageCode = null;
                            $isTranslationStarted = false;
                            $itemOptionNumber = 1;
                            $rowCount = 1;

                            // New item
                            $tempId = $id;
                            $temp[$tempId] = [];
                            $isItemStarted = true;
                            $isItemSkipped = true;
                            $itemSkipRowTarget = $itemSkipFooterRows;
                            continue;
                        }
                    } else {
                        // New item
                        $tempId = $id;
                        $temp[$tempId] = [];
                        $isItemStarted = true;
                        $isItemSkipped = true;
                        $itemSkipRowTarget = $itemSkipHeaderRows;
                        continue;
                    }
                } else if ($isTranslationStarted) {
                    if ($firstCell === self::NO_TRANSLATIONS) {
                        $controlListImportedData = array_merge($controlListImportedData, $temp);
                        $temp = [];
                        $tempId = null;
                        $tempLanguageCode = null;
                        $isItemStarted = false;
                        $isTranslationStarted = false;
                        $rowCount = 1;
                        $itemOptionNumber = 1;
                        $isItemSkipped = true;
                        $itemSkipRowTarget = $itemSkipFooterRows;
                        continue;
                    } else if ($isItemOptionStarted || $firstCell == $itemOptionNumber) {
                        $itemOptionId = isset($controlListRowData[1]) ? $controlListRowData[1] : null;
                        $itemOptionType = isset($controlListRowData[3]) ? $controlListRowData[3] : null;
                        $itemOptionNoTranslatedText = isset($controlListRowData[4]) ? $controlListRowData[4] : null;
                        $translatedText = isset($controlListRowData[5]) ? $controlListRowData[5] : null;

                        if (is_null($itemOptionId)) {
                            $isItemOptionStarted = false;
                            $itemOptionNumber = 1;

                            if (preg_match('/(?:[\S\s]+ \()([\S\s]+)(?:\))/', $firstCell, $matches)) {
                                // Found a language
                                $languageCode = $matches[1];
                                $tempLanguageCode = $languageCode;
                                $temp[$tempId][$tempLanguageCode] = [];
                                $isTranslationStarted = true;
                            }

                            continue;
                        }


                        $nestedIds = explode('.', $itemOptionId);
                        $variableName = array_pop($nestedIds);
                        $propId = array_pop($nestedIds);
                        $id = array_pop($nestedIds);

                        if ($itemOptionType !== self::USING_DATA_FROM_GLOBAL_ITEM) {
                            if ($itemOptionType === OptionElementTypeConstants::CONTROL_LIST) {
                                if ($itemOptionNoTranslatedText !== self::NO_TRANSLATIONS) {
                                    $subControlListData = null;

                                    if ( ! empty($subControlListImportedData)) {
                                        $queryId = $id . '.' . $propId;
                                        if ($targetData = array_key_exists($queryId, $subControlListImportedData)
                                            ? $subControlListImportedData[$queryId]
                                            : null
                                        ) {

                                            if ($targetLanguageData = array_key_exists($tempLanguageCode, $targetData)
                                                ? $targetData[$tempLanguageCode]
                                                : null
                                            ) {

                                                /** Map sub control list to this item */
                                                $temp[$tempId][$tempLanguageCode][$id][$propId] = [
                                                    'variableName' => $variableName,
                                                    'type' => $itemOptionType,
                                                    'translatedText' => $targetLanguageData
                                                ];
                                            }

                                        }
                                    }
                                }
                            } else {
                                $temp[$tempId][$tempLanguageCode][$id][$propId] = [
                                    'variableName' => $variableName,
                                    'type' => $itemOptionType,
                                    'translatedText' => $translatedText
                                ];
                            }
                        }

                        if ($firstCell == $itemOptionNumber) {
                            $itemOptionNumber++;
                        }

                        $isItemOptionStarted = true;
                        continue;
                    }
                } else if (preg_match('/(?:[\S\s]+ \()([\S\s]+)(?:\))/', $firstCell, $matches)) {
                    // Found a language
                    $languageCode = $matches[1];
                    $tempLanguageCode = $languageCode;
                    $temp[$tempId][$tempLanguageCode] = [];
                    $isTranslationStarted = true;
                    continue;
                }
            }
        }

        return $controlListImportedData;
    }

    /**
     * Extract sub control-list imported data
     *
     * @param $controlListOnlyData
     * @return array
     * @throws \Exception
     */
    private function extractSubControlListImportedData($controlListOnlyData)
    {
        if (empty($controlListOnlyData)) return [];

        $controlListImportedData = [];

        foreach ($controlListOnlyData as $controlListSheetData) {
            $temp = [];
            $tempId = null;
            $tempLanguageCode = null;
            $isItemStarted = false;
            $isItemOptionStarted = false;
            $isTranslationStarted = false;
            $isItemSkipped = false;
            $itemSkipRowTarget = null;
            $itemSkipHeaderRows = 5;
            $itemSkipFooterRows = 4;
            $rowCount = 0;
            $itemOptionNumber = 1;
            $last = count($controlListSheetData);

            foreach ($controlListSheetData as $key => $controlListRowData) {
                $firstCell = isset($controlListRowData[0]) ? $controlListRowData[0] : null;
                $rowCount++;

                if ($key + 1 === $last) {
                    if ( ! empty($temp)) {
                        if (array_key_exists($tempId, $controlListImportedData)) {
                            $tempReservedId = array_key_exists(self::RESERVED_ID, $controlListImportedData[$tempId])
                                ? $tempReservedId = $controlListImportedData[$tempId][self::RESERVED_ID]
                                : [];
                            $controlListImportedData[$tempId] = array_merge($controlListImportedData[$tempId], $temp[$tempId]);
                            $controlListImportedData[$tempId][self::RESERVED_ID] = array_key_exists(self::RESERVED_ID, $temp[$tempId])
                                ? array_merge($tempReservedId, $temp[$tempId][self::RESERVED_ID])
                                : $tempReservedId;
                        } else {
                            $controlListImportedData = array_merge($controlListImportedData, $temp);
                        }
                    }
                }

                // Skip rows
                if ($isItemSkipped && ! is_null($itemSkipRowTarget)) {
                    if ($rowCount > $itemSkipRowTarget) {
                        $isItemSkipped = false;
                        $rowCount = 0;
                        $itemSkipRowTarget = null;
                    } else {
                        continue;
                    }
                }

                if ($firstCell === self::CONTROL_LIST_ITEM_ID) {
                    $id = isset($controlListRowData[1]) ? $controlListRowData[1] : null;

                    if (is_null($id)) throw new \Exception(self::INVALID_FORMAT);

                    if ($isItemStarted) {
                        if ($isTranslationStarted) {
                            if (array_key_exists($tempId, $controlListImportedData)) {
                                $tempReservedId = array_key_exists(self::RESERVED_ID, $controlListImportedData[$tempId])
                                    ? $tempReservedId = $controlListImportedData[$tempId][self::RESERVED_ID]
                                    : [];
                                $controlListImportedData[$tempId] = array_merge($controlListImportedData[$tempId], $temp[$tempId]);
                                $controlListImportedData[$tempId][self::RESERVED_ID] = array_key_exists(self::RESERVED_ID, $temp[$tempId])
                                    ? array_merge($tempReservedId, $temp[$tempId][self::RESERVED_ID])
                                    : $tempReservedId;
                            } else {
                                $controlListImportedData = array_merge($controlListImportedData, $temp);
                            }

                            $temp = [];
                            $tempId = null;
                            $tempLanguageCode = null;
                            $isTranslationStarted = false;
                            $itemOptionNumber = 1;
                            $rowCount = 1;

                            // New item
                            $tempId = $id;
                            $temp[$tempId] = [];
                            $isItemStarted = true;
                            $isItemSkipped = true;
                            $itemSkipRowTarget = $itemSkipFooterRows;
                            continue;
                        }
                    } else {
                        // New item
                        $tempId = $id;
                        $temp[$tempId] = [];
                        $isItemStarted = true;
                        $isItemSkipped = true;
                        $itemSkipRowTarget = $itemSkipHeaderRows;
                        continue;
                    }
                } else if ($isTranslationStarted) {
                    if ($firstCell === self::NO_TRANSLATIONS) {
                        if (array_key_exists($tempId, $controlListImportedData)) {
                            $tempReservedId = array_key_exists(self::RESERVED_ID, $controlListImportedData[$tempId])
                                ? $tempReservedId = $controlListImportedData[$tempId][self::RESERVED_ID]
                                : [];
                            $controlListImportedData[$tempId] = array_merge($controlListImportedData[$tempId], $temp[$tempId]);
                            $controlListImportedData[$tempId][self::RESERVED_ID] = array_key_exists(self::RESERVED_ID, $temp[$tempId])
                                ? array_merge($tempReservedId, $temp[$tempId][self::RESERVED_ID])
                                : $tempReservedId;
                        } else {
                            $controlListImportedData = array_merge($controlListImportedData, $temp);
                        }

                        $temp = [];
                        $tempId = null;
                        $tempLanguageCode = null;
                        $isItemStarted = false;
                        $isTranslationStarted = false;
                        $rowCount = 1;
                        $itemOptionNumber = 1;
                        $isItemSkipped = true;
                        $itemSkipRowTarget = $itemSkipFooterRows;
                        continue;
                    } else if ($isItemOptionStarted || $firstCell == $itemOptionNumber) {
                        $itemOptionId = isset($controlListRowData[1]) ? $controlListRowData[1] : null;
                        $itemOptionType = isset($controlListRowData[3]) ? $controlListRowData[3] : null;
                        $itemOptionNoTranslatedText = isset($controlListRowData[4]) ? $controlListRowData[4] : null;
                        $translatedText = isset($controlListRowData[5]) ? $controlListRowData[5] : null;

                        if (is_null($itemOptionId)) {
                            $isItemOptionStarted = false;
                            $itemOptionNumber = 1;
                            continue;
                        }

                        $nestedIds = explode('.', $itemOptionId);
                        $variableName = array_pop($nestedIds);
                        $propId = array_pop($nestedIds);
                        $id = array_pop($nestedIds);

                        if ($itemOptionType !== self::USING_DATA_FROM_GLOBAL_ITEM) {
                            if ($itemOptionType === OptionElementTypeConstants::CONTROL_LIST) {
                                if ($itemOptionNoTranslatedText !== self::NO_TRANSLATIONS) {
                                    /** Map sub control list to this item */
                                    $temp[$tempId][$tempLanguageCode][$id][$propId] = [
                                        'variableName' => $variableName,
                                        'type' => $itemOptionType,
                                        'translatedText' => null
                                    ];
                                    $temp[$tempId][self::RESERVED_ID][] = $id . '.' . $propId . '.' . $tempLanguageCode;
                                }
                            } else {
                                $temp[$tempId][$tempLanguageCode][$id][$propId] = [
                                    'variableName' => $variableName,
                                    'type' => $itemOptionType,
                                    'translatedText' => $translatedText
                                ];
                            }
                        }

                        if ($firstCell == $itemOptionNumber) {
                            $itemOptionNumber++;
                        }

                        $isItemOptionStarted = true;
                        continue;
                    }
                } else if (preg_match('/(?:[\S\s]+ \()([\S\s]+)(?:\))/', $firstCell, $matches)) {
                    // Found a language
                    $languageCode = $matches[1];
                    $tempLanguageCode = $languageCode;
                    $temp[$tempId][$tempLanguageCode] = [];
                    $isTranslationStarted = true;
                    continue;
                }
            }
        }


        $controlListImportedData = collect($controlListImportedData)
            ->map(function ($item) use ($controlListImportedData) {
                return $this->resolveReservedControlListImportedData($controlListImportedData, $item);
            })
            ->filter()
            ->all();

        return $controlListImportedData;
    }

    /**
     * Resolve reserved control-list imported data
     *
     * @param $controlListImportedData
     * @param $item
     * @return mixed
     */
    private function resolveReservedControlListImportedData($controlListImportedData, $item)
    {
        if ($reservedIds = array_key_exists(self::RESERVED_ID, $item) ? $item[self::RESERVED_ID] : null) {
            foreach ($reservedIds as $reservedId) {
                $splitReservedId = explode('.', $reservedId);
                $id = $splitReservedId[0] . '.' . $splitReservedId[1];
                $languageCode = $splitReservedId[2];

                if ($targetData = array_key_exists($id, $controlListImportedData)
                    ? $controlListImportedData[$id]
                    : null
                ) {
                    $targetData = $this->resolveReservedControlListImportedData($controlListImportedData, $targetData);
                    $item[$languageCode][$splitReservedId[0]][$splitReservedId[1]]['translatedText'] = array_key_exists($languageCode, $targetData)
                        ? $targetData[$languageCode]
                        : null;
                }
            }
        }

        return $item;
    }

    /**
     * Apply imported control-list data to current control-list selected data
     *
     * @param $controlListData
     * @param $importedControlListData
     * @return string
     */
    private function applyControlListTranslatedText($controlListData, $importedControlListData)
    {
        $controlListData = json_decode($controlListData);

        if ( ! empty($importedControlListData) && count($importedControlListData) > 0 && is_array($importedControlListData)) {
            foreach ($importedControlListData as $id => $importedProps) {
                if ($item = collect($controlListData)
                    ->where('id', $id)
                    ->first()
                ) {

                    if ($props = isset($item->props) ? $item->props : null) {
                        foreach ($importedProps as $importedPropId => $importedProp) {
                            $importedPropType = isset($importedProp['type']) ? $importedProp['type'] : null;
                            $importedVariableName = isset($importedProp['variableName']) ? $importedProp['variableName'] : null;
                            $importedPropTranslatedText = isset($importedProp['translatedText']) ? $importedProp['translatedText'] : null;

                            if ($currentProp = collect($props)
                                ->where('prop_id', $importedPropId)
                                ->where('variable_name', $importedVariableName)
                                ->where('element_type', $importedPropType)
                                ->first()
                            ) {

                                if ($importedPropType === OptionElementTypeConstants::CONTROL_LIST) {
                                    $toBeSaved = $this->applyControlListTranslatedText($currentProp->option_value, $importedPropTranslatedText);
                                } else {
                                    $toBeSaved = $importedPropTranslatedText;
                                }

                                if ($currentProp->option_value !== $toBeSaved) {
                                    $currentProp->option_value = $toBeSaved;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (empty($controlListData)) return '';

        return json_encode($controlListData);
    }
}