<?php

declare(strict_types=1);

namespace Modules\Equipment\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Equipment\Models\CustomerEquipment;
use Spine\Services\ActivityLogService;

/**
 * CRUD CustomerEquipment — equipment milik customer (My Equipment).
 * unit_name = nama alat versi customer; equipment_id = item katalog (Equipment Type).
 */
class CustomerEquipmentController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = CustomerEquipment::with('customer:id,code,name', 'equipment:id,code,name');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->integer('customer_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('q')) {
            $term = $request->string('q');
            $query->where(function ($q) use ($term) {
                $q->where('unit_name', 'like', "%{$term}%")
                  ->orWhere('unit_code', 'like', "%{$term}%")
                  ->orWhere('serial_no', 'like', "%{$term}%");
            });
        }

        return response()->json(['data' => $query->orderByDesc('id')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unit_code'        => ['nullable', 'string', 'max:50'],
            'unit_name'        => ['required', 'string', 'max:190'],
            'equipment_id'     => ['nullable', 'integer', 'exists:equipments,id'],
            'customer_id'      => ['required', 'integer', 'exists:customers,id'],
            'serial_no'        => ['nullable', 'string', 'max:100'],
            'location'         => ['nullable', 'string', 'max:150'],
            'procurement_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'manufacture_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'cert_expired'     => ['nullable', 'date'],
            'status'           => ['sometimes', 'string', 'in:active,inactive'],
        ]);

        $entity = CustomerEquipment::create($validated);

        Log::info('[CustomerEquipment] created', ['id' => $entity->id, 'unit_code' => $entity->unit_code]);

        return response()->json($entity, 201);
    }

    public function show(int $id): JsonResponse
    {
        $entity = CustomerEquipment::with('customer:id,code,name', 'equipment:id,code,name')->find($id);

        if (! $entity) {
            return response()->json(['message' => 'CustomerEquipment not found'], 404);
        }

        return response()->json($entity);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $entity = CustomerEquipment::find($id);

        if (! $entity) {
            return response()->json(['message' => 'CustomerEquipment not found'], 404);
        }

        $validated = $request->validate([
            'unit_code'        => ['sometimes', 'nullable', 'string', 'max:50'],
            'unit_name'        => ['sometimes', 'string', 'max:190'],
            'equipment_id'     => ['nullable', 'integer', 'exists:equipments,id'],
            'customer_id'      => ['sometimes', 'integer', 'exists:customers,id'],
            'serial_no'        => ['nullable', 'string', 'max:100'],
            'location'         => ['nullable', 'string', 'max:150'],
            'procurement_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'manufacture_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'cert_expired'     => ['nullable', 'date'],
            'status'           => ['sometimes', 'string', 'in:active,inactive'],
        ]);

        $entity->update($validated);

        Log::info('[CustomerEquipment] updated', ['id' => $entity->id, 'unit_code' => $entity->unit_code]);

        return response()->json($entity);
    }

    public function destroy(int $id): JsonResponse
    {
        $entity = CustomerEquipment::find($id);

        if (! $entity) {
            return response()->json(['message' => 'CustomerEquipment not found'], 404);
        }

        $entity->delete();

        return response()->json(['message' => 'CustomerEquipment deleted']);
    }

    public function activityLogs(int $id): JsonResponse
    {
        $entity = CustomerEquipment::find($id);

        if (! $entity) {
            return response()->json(['message' => 'CustomerEquipment not found'], 404);
        }

        $logs = $this->activityLog
            ->query()
            ->where('subject_type', CustomerEquipment::class)
            ->where('subject_id', $id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($log) => [
                'id'          => $log->id,
                'description' => $log->description,
                'causer'      => $log->causer?->name ?? 'System',
                'properties'  => $log->properties,
                'at'          => $log->created_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $logs]);
    }
}
