<?php

declare(strict_types=1);

namespace Modules\Equipment\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Equipment\Models\Equipment;

/**
 * CRUD Equipment — item katalog (Group -> Subgroup -> Item).
 */
class EquipmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Equipment::with('subgroup:id,code,name,group_id', 'category:id,code,name', 'admin:id,name');

        if ($request->filled('subgroup_id')) {
            $query->where('subgroup_id', $request->integer('subgroup_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('q')) {
            $term = $request->string('q');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%");
            });
        }

        return response()->json(['data' => $query->orderByDesc('id')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'         => ['required', 'string', 'max:50'],
            'name'         => ['required', 'string', 'max:150'],
            'description'  => ['nullable', 'string'],
            'rate'         => ['sometimes', 'numeric', 'min:0'],
            'unit'         => ['nullable', 'string', 'max:30'],
            'subgroup_id'  => ['required', 'integer', 'exists:equipment_subgroups,id'],
            'category_id'  => ['nullable', 'integer', 'exists:equipment_categories,id'],
            'status'       => ['sometimes', 'string', 'in:draft,active,inactive,expired'],
            'admin_id'     => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $entity = Equipment::create($validated);

        Log::info('[Equipment] created', ['id' => $entity->id, 'code' => $entity->code]);

        return response()->json($entity, 201);
    }

    public function show(int $id): JsonResponse
    {
        $entity = Equipment::with('subgroup:id,code,name,group_id', 'category:id,code,name', 'admin:id,name')->find($id);

        if (! $entity) {
            return response()->json(['message' => 'Equipment not found'], 404);
        }

        return response()->json($entity);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $entity = Equipment::find($id);

        if (! $entity) {
            return response()->json(['message' => 'Equipment not found'], 404);
        }

        $validated = $request->validate([
            'code'         => ['sometimes', 'string', 'max:50'],
            'name'         => ['sometimes', 'string', 'max:150'],
            'description'  => ['nullable', 'string'],
            'rate'         => ['sometimes', 'numeric', 'min:0'],
            'unit'         => ['nullable', 'string', 'max:30'],
            'subgroup_id'  => ['sometimes', 'integer', 'exists:equipment_subgroups,id'],
            'category_id'  => ['nullable', 'integer', 'exists:equipment_categories,id'],
            'status'       => ['sometimes', 'string', 'in:draft,active,inactive,expired'],
            'admin_id'     => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $entity->update($validated);

        Log::info('[Equipment] updated', ['id' => $entity->id, 'code' => $entity->code]);

        return response()->json($entity);
    }

    public function destroy(int $id): JsonResponse
    {
        $entity = Equipment::find($id);

        if (! $entity) {
            return response()->json(['message' => 'Equipment not found'], 404);
        }

        $entity->delete();

        return response()->json(['message' => 'Equipment deleted']);
    }
}
