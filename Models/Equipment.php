<?php

declare(strict_types=1);

namespace Modules\Equipment\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spine\Traits\HasLifecycleHooks;

class Equipment extends Model
{
    use HasLifecycleHooks;
    use HasUlids;
    use SoftDeletes;

    protected $table = 'equipments';

    protected $fillable = [
        'code', 'name', 'description', 'rate', 'unit',
        'subgroup_id', 'category_id', 'status', 'admin_id',
    ];

    protected $casts = ['rate' => 'decimal:2'];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_EXPIRED = 'expired';

    public function subgroup(): BelongsTo
    {
        return $this->belongsTo(EquipmentSubgroup::class, 'subgroup_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public static function labels(): array
    {
        return [
            'code'        => 'Kode',
            'name'        => 'Nama',
            'description' => 'Deskripsi',
            'rate'        => 'Tarif',
            'unit'        => 'Satuan',
            'subgroup_id' => 'Subgroup',
            'category_id' => 'Kategori',
            'status'      => 'Status',
            'admin_id'    => 'Admin',
        ];
    }
}
