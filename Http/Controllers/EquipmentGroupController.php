<?php

declare(strict_types=1);

namespace Modules\Equipment\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Equipment\Models\EquipmentGroup;
use Modules\Equipment\Models\EquipmentSubgroup;
use Spine\Services\ActivityLogService;

/**
 * CRUD EquipmentGroup — level tertinggi katalog (Group -> Subgroup -> Item).
 */
class EquipmentGroupController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }
    public function index(Request $request): JsonResponse
    {
        $query = EquipmentGroup::query();

        if ($request->filled('q')) {
            $term = $request->string('q');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%");
            });
        }
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json(['data' => $query->orderByDesc('id')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'      => ['required', 'string', 'max:50', 'unique:equipment_groups,code'],
            'name'      => ['required', 'string', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $entity = EquipmentGroup::create($validated);

        Log::info('[EquipmentGroup] created', ['id' => $entity->id, 'code' => $entity->code]);

        return response()->json($entity, 201);
    }

    public function show(int $id): JsonResponse
    {
        $entity = EquipmentGroup::with('subgroups:id,code,name,group_id,is_active')->find($id);

        if (! $entity) {
            return response()->json(['message' => 'EquipmentGroup not found'], 404);
        }

        return response()->json($entity);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $entity = EquipmentGroup::find($id);

        if (! $entity) {
            return response()->json(['message' => 'EquipmentGroup not found'], 404);
        }

        $validated = $request->validate([
            'code'      => ['sometimes', 'string', 'max:50', 'unique:equipment_groups,code,' . $id],
            'name'      => ['sometimes', 'string', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $entity->update($validated);

        Log::info('[EquipmentGroup] updated', ['id' => $entity->id, 'code' => $entity->code]);

        return response()->json($entity);
    }

    public function destroy(int $id): JsonResponse
    {
        $entity = EquipmentGroup::find($id);

        if (! $entity) {
            return response()->json(['message' => 'EquipmentGroup not found'], 404);
        }

        $entity->delete();

        return response()->json(['message' => 'EquipmentGroup deleted']);
    }

    public function subgroups(int $id): JsonResponse
    {
        $entity = EquipmentGroup::find($id);

        if (! $entity) {
            return response()->json(['message' => 'EquipmentGroup not found'], 404);
        }

        $rows = EquipmentSubgroup::where('group_id', $id)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'is_active']);

        return response()->json(['data' => $rows]);
    }

    public function activityLogs(int $id): JsonResponse
    {
        $entity = EquipmentGroup::find($id);

        if (! $entity) {
            return response()->json(['message' => 'EquipmentGroup not found'], 404);
        }

        $logs = $this->activityLog
            ->query()
            ->where('subject_type', EquipmentGroup::class)
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
