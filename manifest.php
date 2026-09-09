<?php

declare(strict_types=1);

/**
 * MANIFEST modul Equipment.
 *
 * Porting dari prfx-equipments (Perfex) — identitas Perfex dihilangkan.
 * Katalog master equipment: Category -> Subgroup -> Item, dipakai lintas
 * dokumen transaksi. Fase 1: item katalog + dokumen kirim-customer (PDF via
 * barryvdh/laravel-dompdf). equipment_tasks / equipment_members: nanti.
 */
return [
    'menu' => [
        [
            'slug'       => 'equipments',
            'label'      => 'Equipments',
            'icon'       => '🛠️',
            'href'       => '/equipments',
            'position'   => 40,
            'permission' => 'equipment:view',
            'children'   => [
                ['title' => 'Items',      'url' => '/equipments'],
                ['title' => 'Group',      'url' => '/equipment-groups'],
                ['title' => 'Subgroup',   'url' => '/equipment-subgroups'],
                ['title' => 'Category',   'url' => '/equipment-categories'],
            ],
        ],
    ],

    'widgets' => [],

    'settings' => [
        [
            'slug'     => 'equipment',
            'label'    => 'Equipment',
            'icon'     => '🛠️',
            'position' => 50,
            'fields'   => [
                ['key' => 'equipment_start_number', 'label' => 'Start Number', 'type' => 'number', 'default' => '60100'],
                ['key' => 'equipment_code_length',  'label' => 'Code Length',  'type' => 'number', 'default' => '4'],
            ],
        ],
    ],

    'detail_tabs' => [
        // Equipments (item katalog)
        [
            'slug'       => 'overview',
            'label'      => 'Overview',
            'icon'       => '👁️',
            'api'        => '/api/v1/equipments/{id}',
            'position'   => 10,
            'permission' => 'equipment:view',
        ],
        [
            'slug'       => 'activity',
            'label'      => 'Activity',
            'icon'       => '🕐',
            'api'        => '/api/v1/equipments/{id}/activity-logs',
            'position'   => 20,
            'permission' => 'equipment:view',
        ],
        // Group
        [
            'slug'       => 'overview',
            'label'      => 'Overview',
            'icon'       => '👁️',
            'api'        => '/api/v1/equipment-groups/{id}',
            'position'   => 10,
            'permission' => 'equipment:view',
        ],
        [
            'slug'       => 'subgroups',
            'label'      => 'Subgroups',
            'icon'       => '🗂️',
            'api'        => '/api/v1/equipment-groups/{id}/subgroups',
            'position'   => 15,
            'permission' => 'equipment:view',
        ],
        [
            'slug'       => 'activity',
            'label'      => 'Activity',
            'icon'       => '🕐',
            'api'        => '/api/v1/equipment-groups/{id}/activity-logs',
            'position'   => 20,
            'permission' => 'equipment:view',
        ],
        // Subgroup
        [
            'slug'       => 'overview',
            'label'      => 'Overview',
            'icon'       => '👁️',
            'api'        => '/api/v1/equipment-subgroups/{id}',
            'position'   => 10,
            'permission' => 'equipment:view',
        ],
        [
            'slug'       => 'categories',
            'label'      => 'Categories',
            'icon'       => '🏷️',
            'api'        => '/api/v1/equipment-subgroups/{id}/categories',
            'position'   => 15,
            'permission' => 'equipment:view',
        ],
        [
            'slug'       => 'activity',
            'label'      => 'Activity',
            'icon'       => '🕐',
            'api'        => '/api/v1/equipment-subgroups/{id}/activity-logs',
            'position'   => 20,
            'permission' => 'equipment:view',
        ],
        // Category
        [
            'slug'       => 'overview',
            'label'      => 'Overview',
            'icon'       => '👁️',
            'api'        => '/api/v1/equipment-categories/{id}',
            'position'   => 10,
            'permission' => 'equipment:view',
        ],
        [
            'slug'       => 'equipments',
            'label'      => 'Equipments',
            'icon'       => '🛠️',
            'api'        => '/api/v1/equipment-categories/{id}/equipments',
            'position'   => 15,
            'permission' => 'equipment:view',
        ],
        [
            'slug'       => 'activity',
            'label'      => 'Activity',
            'icon'       => '🕐',
            'api'        => '/api/v1/equipment-categories/{id}/activity-logs',
            'position'   => 20,
            'permission' => 'equipment:view',
        ],
    ],

    'rbac' => [
        'permissions' => [
            'equipment:view', 'equipment:create', 'equipment:edit', 'equipment:delete',
        ],
        'roles' => [
            ['name' => 'equipment-admin', 'label' => 'Equipment Admin',
             'permissions' => ['equipment:*']],
        ],
        'grants' => [
            'customer' => ['equipment:view'],
            'surveyor' => ['equipment:view'],
            'agency'   => ['equipment:view'],
        ],
    ],
];
