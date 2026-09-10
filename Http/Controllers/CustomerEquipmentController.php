<?php

declare(strict_types=1);

namespace Modules\Equipment\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Connection\Services\ActorResolver;
use Modules\Equipment\Models\CustomerEquipment;
use Spine\Services\ActivityLogService;

/**
 * CRUD CustomerEquipment — equipment milik customer (My Equipment).
 * unit_name = nama alat versi customer; equipment_id = item katalog (Equipment Type).
 *
 * Scoping: user dengan entity customer (customers.admin_id via ActorResolver)
 * HANYA melihat equipment milik customer-nya sendiri. Non-customer (platform/
 * surveyor/agency) melihat semua.
 */
class CustomerEquipmentController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
        private readonly ActorResolver $actors,
    ) {
    }

    private function isFullAccess(Request $request): bool
    {
        return $this->actors->resolve($request->user())['type'] !== 'customer';
    }

    private function allowAccessTo(Request $request, CustomerEquipment $entity): bool
    {
        if ($this->isFullAccess($request)) {
            return true;
        }

        $actor = $this->actors->resolve($request->user());

        return $actor['entity']?->id === $entity->customer_id;
    }

    public function index(Request $request): JsonResponse
    {
        $query = CustomerEquipment::with('customer:id,code,name', 'equipment:id,code,name,subgroup_id,category_id', 'equipment.subgroup:id,code,name,group_id', 'equipment.subgroup.group:id,code,name', 'equipment.category:id,code,name');

        // Customer entity: hanya equipment miliknya sendiri.
        if (! $this->isFullAccess($request)) {
            $actor = $this->actors->resolve($request->user());
            $query->where('customer_id', $actor['entity']->id);
        }

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
            'equipment_id'     => ['required', 'integer', 'exists:equipments,id'],
            'customer_id'      => ['nullable', 'integer', 'exists:customers,id'],
            'serial_no'        => ['nullable', 'string', 'max:100'],
            'location'         => ['nullable', 'string', 'max:150'],
            'procurement_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'manufacture_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'cert_expired'     => ['nullable', 'date'],
            'status'           => ['sometimes', 'string', 'in:active,inactive'],
        ]);

        // Customer entity: equipment selalu milik customer-nya sendiri.
        if (! $this->isFullAccess($request)) {
            $actor = $this->actors->resolve($request->user());
            $validated['customer_id'] = $actor['entity']->id;
        } elseif (empty($validated['customer_id'])) {
            return response()->json([
                'message' => 'The customer id field is required.',
                'errors'  => ['customer_id' => ['The customer id field is required.']],
            ], 422);
        }

        $entity = CustomerEquipment::create($validated);

        Log::info('[CustomerEquipment] created', ['id' => $entity->id, 'unit_code' => $entity->unit_code]);

        return response()->json($entity, 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $entity = CustomerEquipment::with('customer:id,code,name', 'equipment:id,code,name,subgroup_id,category_id', 'equipment.subgroup:id,code,name,group_id', 'equipment.subgroup.group:id,code,name', 'equipment.category:id,code,name')->find($id);

        if (! $entity || ! $this->allowAccessTo($request, $entity)) {
            return response()->json(['message' => 'CustomerEquipment not found'], 404);
        }

        return response()->json($entity);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $entity = CustomerEquipment::find($id);

        if (! $entity || ! $this->allowAccessTo($request, $entity)) {
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

        if (array_key_exists('customer_id', $validated) && ! $this->isFullAccess($request)) {
            // Customer entity tidak boleh memindahkan equipment ke customer lain.
            $actor = $this->actors->resolve($request->user());
            $validated['customer_id'] = $actor['entity']->id;
        }

        $entity->update($validated);

        Log::info('[CustomerEquipment] updated', ['id' => $entity->id, 'unit_code' => $entity->unit_code]);

        return response()->json($entity);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $entity = CustomerEquipment::find($id);

        if (! $entity || ! $this->allowAccessTo($request, $entity)) {
            return response()->json(['message' => 'CustomerEquipment not found'], 404);
        }

        $entity->delete();

        return response()->json(['message' => 'CustomerEquipment deleted']);
    }

    public function activityLogs(int $id, Request $request): JsonResponse
    {
        $entity = CustomerEquipment::find($id);

        if (! $entity || ! $this->allowAccessTo($request, $entity)) {
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
