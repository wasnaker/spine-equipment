<?php

declare(strict_types=1);

namespace Modules\Equipment\Listeners;

use Modules\Equipment\Models\Equipment;
use Modules\Equipment\Models\EquipmentCategory;
use Modules\Equipment\Models\EquipmentGroup;
use Modules\Equipment\Models\EquipmentSubgroup;
use Spine\Events\EntityCreated;
use Spine\Events\EntityDeleted;
use Spine\Events\EntityUpdated;
use Spine\Services\ActivityLogService;

/**
 * HOOK — lifecycle katalog Equipment (HasLifecycleHooks).
 * Group/Subgroup/Category/Item: created/updated/deleted -> activity log.
 */
class LogEquipmentActivity
{
    private const MODELS = [
        EquipmentGroup::class,
        EquipmentSubgroup::class,
        EquipmentCategory::class,
        Equipment::class,
    ];

    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function created(EntityCreated $event): void
    {
        if (! $this->supports($event->entity)) {
            return;
        }

        $this->activityLog->log(
            $this->label($event->entity) . ' created: ' . ($event->entity->name ?? $event->entity->getKey()),
            $event->entity,
            $this->user(),
            ['event' => 'created'],
        );
    }

    public function updated(EntityUpdated $event): void
    {
        if (! $this->supports($event->entity)) {
            return;
        }

        $changes = $event->changes;

        $this->activityLog->log(
            $this->label($event->entity) . ' updated: ' . ($event->entity->name ?? $event->entity->getKey()) . ' (' . $this->describe($event->entity, $changes) . ')',
            $event->entity,
            $this->user(),
            ['event' => 'updated', 'changes' => $changes],
        );
    }

    public function deleted(EntityDeleted $event): void
    {
        if (! $this->supports($event->entity)) {
            return;
        }

        $this->activityLog->log(
            $this->label($event->entity) . ' deleted: ' . ($event->entity->name ?? $event->entity->getKey()),
            null,
            $this->user(),
            ['event' => 'deleted', 'id' => $event->entity->getKey()],
            null,
            $event->entityType,
        );
    }

    private function supports(object $entity): bool
    {
        return in_array(get_class($entity), self::MODELS, true);
    }

    private function label(object $entity): string
    {
        return match (get_class($entity)) {
            EquipmentGroup::class => 'EquipmentGroup',
            EquipmentSubgroup::class => 'EquipmentSubgroup',
            EquipmentCategory::class => 'EquipmentCategory',
            default => 'Equipment',
        };
    }

    private function describe(object $entity, array $changes): string
    {
        $parts = [];
        $labels = method_exists($entity, 'labels') ? $entity::labels() : [];

        foreach ($changes as $field => $change) {
            if (in_array($field, ['updated_at', 'remember_token'], true)) {
                continue;
            }

            $label = $labels[$field] ?? $field;
            $parts[] = $label . ': ' . $change['old'] . ' -> ' . $change['new'];
        }

        return implode(', ', $parts);
    }

    private function user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return auth('sanctum')->user() ?? auth()->user();
    }
}
