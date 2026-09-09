<?php

declare(strict_types=1);

namespace Modules\Equipment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spine\Traits\HasLifecycleHooks;

class EquipmentGroup extends Model
{
    use HasLifecycleHooks;
    use HasUlids;
    use SoftDeletes;

    protected $table = 'equipment_groups';

    protected $fillable = ['code', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function subgroups(): HasMany
    {
        return $this->hasMany(EquipmentSubgroup::class, 'group_id');
    }

    public static function labels(): array
    {
        return [
            'code'      => 'Kode',
            'name'      => 'Nama',
            'is_active' => 'Aktif',
        ];
    }
}
