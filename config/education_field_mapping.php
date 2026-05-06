<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Education Field Mapping
    |--------------------------------------------------------------------------
    |
    | HR can maintain this file to control:
    | 1) canonical field groups and their aliases
    | 2) which groups are accepted as "related field"
    |
    | Unmapped fields remain strict by design:
    | they only pass on exact phrase match, not fuzzy similarity.
    |
    */

    'field_groups' => [
        // Statistics / Analytics track
        'statistics' => [
            'statistics',
            'applied statistics',
            'biostatistics',
            'bs statistics',
            'b.s. statistics',
            'ms statistics',
            'master of science in statistics',
        ],
        'mathematics' => [
            'mathematics',
            'bs mathematics',
            'b.s. mathematics',
        ],
        'applied_mathematics' => [
            'applied mathematics',
        ],
        'data_science' => [
            'data science',
            'data analytics',
            'analytics',
            'business analytics',
        ],
        'economics' => [
            'economics',
            'applied economics',
        ],

        // Governance / administration track
        'public_administration' => [
            'public administration',
            'public admin',
            'master in public administration',
            'master of public administration',
            'mpa',
        ],
        'political_science' => [
            'political science',
        ],
        'governance' => [
            'governance',
            'local governance',
            'governance operations',
            'local government administration',
        ],
        'public_policy' => [
            'public policy',
            'policy studies',
            'policy and governance',
        ],

        // Budget / finance / accounting track
        'accountancy' => [
            'accountancy',
            'accounting',
            'bs accountancy',
            'bsa',
        ],
        'financial_management' => [
            'financial management',
            'finance',
            'public financial management',
        ],
        'auditing' => [
            'auditing',
            'internal auditing',
        ],
        'business_administration' => [
            'business administration',
            'business management',
            'management',
            'mba',
        ],

        // ICT track
        'information_technology' => [
            'information technology',
            'it',
            'bsit',
            'b.s. information technology',
        ],
        'computer_science' => [
            'computer science',
            'bs computer science',
            'bscs',
        ],
        'information_systems' => [
            'information systems',
            'management information systems',
            'mis',
        ],
        'software_engineering' => [
            'software engineering',
        ],

        // Social services / HR track
        'psychology' => [
            'psychology',
        ],
        'social_work' => [
            'social work',
            'social welfare',
        ],
        'human_resource_management' => [
            'human resource management',
            'human resources management',
            'hrm',
            'personnel management',
        ],
    ],

    'related_groups' => [
        // Statistics family
        'statistics' => ['mathematics', 'applied_mathematics', 'data_science', 'economics'],
        'mathematics' => ['statistics', 'applied_mathematics', 'data_science', 'economics'],
        'applied_mathematics' => ['statistics', 'mathematics', 'data_science', 'economics'],
        'data_science' => ['statistics', 'mathematics', 'applied_mathematics', 'economics', 'information_systems'],
        'economics' => ['statistics', 'mathematics', 'data_science', 'financial_management', 'public_policy'],

        // Governance family
        'public_administration' => ['political_science', 'governance', 'public_policy', 'business_administration'],
        'political_science' => ['public_administration', 'governance', 'public_policy'],
        'governance' => ['public_administration', 'political_science', 'public_policy'],
        'public_policy' => ['public_administration', 'political_science', 'governance', 'economics'],

        // Finance family
        'accountancy' => ['financial_management', 'auditing', 'business_administration'],
        'financial_management' => ['accountancy', 'auditing', 'business_administration', 'economics'],
        'auditing' => ['accountancy', 'financial_management', 'business_administration'],
        'business_administration' => ['accountancy', 'financial_management', 'auditing', 'public_administration'],

        // ICT family
        'information_technology' => ['computer_science', 'information_systems', 'software_engineering', 'data_science'],
        'computer_science' => ['information_technology', 'information_systems', 'software_engineering', 'data_science'],
        'information_systems' => ['information_technology', 'computer_science', 'software_engineering', 'data_science'],
        'software_engineering' => ['information_technology', 'computer_science', 'information_systems'],

        // Social services / HR family
        'psychology' => ['human_resource_management', 'social_work'],
        'social_work' => ['psychology', 'public_administration', 'human_resource_management'],
        'human_resource_management' => ['psychology', 'business_administration', 'public_administration', 'social_work'],
    ],
];

