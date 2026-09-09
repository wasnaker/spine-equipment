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

    'detail_tabs' => [
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
