<?php

declare(strict_types=1);

namespace Modules\Equipment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentDocumentItem extends Model
{
    protected $table = 'equipment_document_items';

    protected $fillable = ['document_id', 'equipment_id', 'qty', 'rate'];

    protected $casts = [
        'qty' => 'decimal:2',
        'rate' => 'decimal:2',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(EquipmentDocument::class, 'document_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }
}
