<?php

declare(strict_types=1);

namespace Modules\Equipment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spine\Traits\HasLifecycleHooks;

class EquipmentCategory extends Model
{
    use HasLifecycleHooks;
    use HasUlids;
    use SoftDeletes;

    protected $table = 'equipment_categories';

    protected $fillable = ['code', 'name', 'subgroup_id', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class, 'category_id');
    }

    public function subgroup(): BelongsTo
    {
        return $this->belongsTo(EquipmentSubgroup::class, 'subgroup_id');
    }

    public static function labels(): array
    {
        return [
            'code'        => 'Kode',
            'name'        => 'Nama',
            'subgroup_id' => 'Subgroup',
            'sort_order'  => 'Urutan',
            'is_active'   => 'Aktif',
        ];
    }
}
