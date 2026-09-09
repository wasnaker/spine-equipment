<?php

declare(strict_types=1);

namespace Modules\Equipment\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Equipment\Models\Equipment;
use Modules\Equipment\Models\EquipmentCategory;
use Spine\Services\ActivityLogService;

/**
 * CRUD EquipmentCategory — atribut item (bukan hierarki).
 */
class EquipmentCategoryController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }
    public function index(Request $request): JsonResponse
    {
        $query = EquipmentCategory::query();

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
            'code'      => ['required', 'string', 'max:50', 'unique:equipment_categories,code'],
            'name'      => ['required', 'string', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $entity = EquipmentCategory::create($validated);

        Log::info('[EquipmentCategory] created', ['id' => $entity->id, 'code' => $entity->code]);

        return response()->json($entity, 201);
    }

    public function show(int $id): JsonResponse
    {
        $entity = EquipmentCategory::find($id);

        if (! $entity) {
            return response()->json(['message' => 'EquipmentCategory not found'], 404);
        }

        return response()->json($entity);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $entity = EquipmentCategory::find($id);

        if (! $entity) {
            return response()->json(['message' => 'EquipmentCategory not found'], 404);
        }

        $validated = $request->validate([
            'code'      => ['sometimes', 'string', 'max:50', 'unique:equipment_categories,code,' . $id],
            'name'      => ['sometimes', 'string', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $entity->update($validated);

        Log::info('[EquipmentCategory] updated', ['id' => $entity->id, 'code' => $entity->code]);

        return response()->json($entity);
    }

    public function destroy(int $id): JsonResponse
    {
        $entity = EquipmentCategory::find($id);

        if (! $entity) {
            return response()->json(['message' => 'EquipmentCategory not found'], 404);
        }

        $entity->delete();

        return response()->json(['message' => 'EquipmentCategory deleted']);
    }

    public function equipments(int $id): JsonResponse
    {
        $entity = EquipmentCategory::find($id);

        if (! $entity) {
            return response()->json(['message' => 'EquipmentCategory not found'], 404);
        }

        $rows = Equipment::where('category_id', $id)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'status']);

        return response()->json(['data' => $rows]);
    }

    public function activityLogs(int $id): JsonResponse
    {
        $entity = EquipmentCategory::find($id);

        if (! $entity) {
            return response()->json(['message' => 'EquipmentCategory not found'], 404);
        }

        $logs = $this->activityLog
            ->query()
            ->where('subject_type', EquipmentCategory::class)
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
