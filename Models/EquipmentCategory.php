<?php

declare(strict_types=1);

namespace Modules\Equipment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spine\Traits\HasLifecycleHooks;

class EquipmentCategory extends Model
{
    use HasLifecycleHooks;
    use SoftDeletes;

    protected $table = 'equipment_categories';

    protected $fillable = ['code', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class, 'category_id');
    }
}
