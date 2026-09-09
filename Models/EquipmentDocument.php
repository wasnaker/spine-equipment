<?php

declare(strict_types=1);

namespace Modules\Equipment\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Customer\Models\Customer;
use Modules\Surveyor\Models\Surveyor;
use Spine\Traits\HasLifecycleHooks;

class EquipmentDocument extends Model
{
    use HasLifecycleHooks;
    use HasUlids;
    use SoftDeletes;

    protected $table = 'equipment_documents';

    protected $fillable = [
        'number', 'prefix', 'date', 'expirydate',
        'customer_id', 'surveyor_id',
        'subtotal', 'total_tax', 'total',
        'status', 'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'expirydate' => 'date',
        'subtotal' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(EquipmentDocumentItem::class, 'document_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function surveyor(): BelongsTo
    {
        return $this->belongsTo(Surveyor::class, 'surveyor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
