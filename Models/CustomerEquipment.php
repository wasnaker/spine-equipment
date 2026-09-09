<?php

declare(strict_types=1);

namespace Modules\Equipment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Customer\Models\Customer;
use Spine\Traits\HasLifecycleHooks;

/**
 * CustomerEquipment — equipment milik customer (My Equipment).
 * unit_name = nama alat versi customer; equipment_id = item katalog (Equipment Type).
 */
class CustomerEquipment extends Model
{
    use HasLifecycleHooks;
    use HasUlids;
    use SoftDeletes;

    protected $table = 'customer_equipments';

    protected $fillable = [
        'code', 'unit_code', 'unit_name', 'equipment_id', 'customer_id',
        'serial_no', 'location', 'procurement_year', 'manufacture_year',
        'cert_expired', 'status',
    ];

    protected $casts = [
        'procurement_year' => 'integer',
        'manufacture_year' => 'integer',
        'cert_expired' => 'date',
    ];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    protected static function booted(): void
    {
        // code = EntityCode::encode(id, 4); id belum ada saat pre-insert, jadi
        // temp dulu (kolom unique), final di created via saveQuietly (tanpa event).
        // PITFALL: pakai `saving`, bukan `creating` — listener creating milik
        // trait HasLifecycleHooks return `true` yang meng-halt listener lain.
        static::saving(function (self $model) {
            if (empty($model->code)) {
                $model->code = 'TMP-' . strtoupper(substr((string) str()->ulid(), 0, 10));
            }
        });

        static::created(function (self $model) {
            $model->code = \Spine\Support\EntityCode::encode($model->id, 4);
            $model->saveQuietly();
        });
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public static function labels(): array
    {
        return [
            'code'             => 'Kode',
            'unit_code'        => 'Unit Code',
            'unit_name'        => 'Unit Name',
            'equipment_id'     => 'Equipment Type',
            'customer_id'      => 'Customer',
            'serial_no'        => 'Serial No',
            'location'         => 'Location',
            'procurement_year' => 'Tahun Pengadaan',
            'manufacture_year' => 'Tahun Pembuatan',
            'cert_expired'     => 'Sertifikat Berakhir',
            'status'           => 'Status',
        ];
    }
}
