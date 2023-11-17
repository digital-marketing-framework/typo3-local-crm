<?php

defined('TYPO3') || exit;

$ll = 'LLL:EXT:dmf_local_crm/Resources/Private/Language/locallang_db.xlf:';
$readOnly = true;

$GLOBALS['TCA']['tx_dmflocalcrm_domain_model_data_userdata'] = [
    'ctrl' => [
        'label' => 'user_id',
        'tstamp' => 'changed',
        'crdate' => 'created',
        'title' => $ll . 'tx_dmflocalcrm_domain_model_data_userdata',
        'origUid' => 't3_origuid',
        'searchFields' => 'user_id',
        'iconfile' => 'EXT:dmf_local_crm/Resources/Public/Icons/UserData.svg',
        // 'default_sortby' => 'changed DESC',
    ],
    'interface' => [
        'showRecordFieldList' => 'user_id,created,changed,serialized_data',
    ],
    'types' => [
        '0' => [
            'showitem' => 'user_id,created,changed,serialized_data',
        ],
    ],
    'palettes' => [
        '0' => ['showitem' => 'user_id,created,changed,serialized_data'],
    ],
    'columns' => [
        'user_id' => [
            'exclude' => 1,
            'label' => $ll . 'tx_dmflocalcrm_domain_model_data_userdata.user_id',
            'config' => [
                'type' => 'input',
                'readOnly' => $readOnly,
            ],
        ],
        'created' => [
            'exclude' => 1,
            'label' => $ll . 'tx_dmflocalcrm_domain_model_data_userdata.created',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'readOnly' => $readOnly,
            ],
        ],
        'changed' => [
            'exclude' => 1,
            'label' => $ll . 'tx_dmflocalcrm_domain_model_data_userdata.changed',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'readOnly' => $readOnly,
            ],
        ],
        'serialized_data' => [
            'exclude' => 1,
            'label' => $ll . 'tx_dmflocalcrm_domain_model_data_userdata.serialized_data',
            'config' => [
                'type' => 'user',
                'renderType' => 'digitalMarketingFrameworkJsonFieldElement',
                'cols' => 40,
                'rows' => 15,
                'readOnly' => $readOnly,
            ],
        ],
    ],
];
