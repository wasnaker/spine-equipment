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
        ],
    ],

    'widgets' => [],

    'detail_tabs' => [
        [
            'slug'       => 'overview',
            'label'      => 'Overview',
            'icon'       => '👁️',
            'api'        => '',
            'position'   => 10,
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
