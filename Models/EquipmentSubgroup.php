<?php

declare(strict_types=1);

namespace Modules\Equipment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spine\Traits\HasLifecycleHooks;

class EquipmentSubgroup extends Model
{
    use HasLifecycleHooks;
    use SoftDeletes;

    protected $table = 'equipment_subgroups';

    protected $fillable = ['code', 'name', 'group_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(EquipmentGroup::class, 'group_id');
    }

    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class, 'subgroup_id');
    }
}
