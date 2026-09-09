<?php

declare(strict_types=1);

namespace Modules\Equipment\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Equipment\Models\EquipmentSubgroup;

/**
 * CRUD EquipmentSubgroup — anak group (Group -> Subgroup -> Item).
 */
class EquipmentSubgroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EquipmentSubgroup::with('group:id,code,name')->withCount('equipments');

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->integer('group_id'));
        }
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
            'code'      => ['required', 'string', 'max:50'],
            'name'      => ['required', 'string', 'max:150'],
            'group_id'  => ['required', 'integer', 'exists:equipment_groups,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $dup = EquipmentSubgroup::where('group_id', $validated['group_id'])
            ->where('code', $validated['code'])
            ->where('deleted_at', null)
            ->first();
        if ($dup) {
            return response()->json(['message' => "Code {$validated['code']} sudah ada untuk group ini."], 422);
        }

        $entity = EquipmentSubgroup::create($validated);

        Log::info('[EquipmentSubgroup] created', ['id' => $entity->id, 'code' => $entity->code]);

        return response()->json($entity, 201);
    }

    public function show(int $id): JsonResponse
    {
        $entity = EquipmentSubgroup::with(['group:id,code,name', 'equipments:id,code,name,subgroup_id,status'])->find($id);

        if (! $entity) {
            return response()->json(['message' => 'EquipmentSubgroup not found'], 404);
        }

        return response()->json($entity);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $entity = EquipmentSubgroup::find($id);

        if (! $entity) {
            return response()->json(['message' => 'EquipmentSubgroup not found'], 404);
        }

        $validated = $request->validate([
            'code'      => ['sometimes', 'string', 'max:50'],
            'name'      => ['sometimes', 'string', 'max:150'],
            'group_id'  => ['sometimes', 'integer', 'exists:equipment_groups,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('code', $validated)) {
            $dup = EquipmentSubgroup::where('id', '!=', $entity->id)
                ->where('group_id', $validated['group_id'] ?? $entity->group_id)
                ->where('code', $validated['code'])
                ->where('deleted_at', null)
                ->first();
            if ($dup) {
                return response()->json(['message' => "Code {$validated['code']} sudah ada untuk group ini."], 422);
            }
        }

        $entity->update($validated);

        Log::info('[EquipmentSubgroup] updated', ['id' => $entity->id, 'code' => $entity->code]);

        return response()->json($entity);
    }

    public function destroy(int $id): JsonResponse
    {
        $entity = EquipmentSubgroup::find($id);

        if (! $entity) {
            return response()->json(['message' => 'EquipmentSubgroup not found'], 404);
        }

        $entity->delete();

        return response()->json(['message' => 'EquipmentSubgroup deleted']);
    }
}
