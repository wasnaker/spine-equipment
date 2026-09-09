<?php

declare(strict_types=1);

namespace Modules\Equipment\Listeners;

use Modules\Equipment\Models\Equipment;
use Spine\Events\EntityCreated;
use Spine\Events\EntityDeleted;
use Spine\Events\EntityUpdated;
use Spine\Services\ActivityLogService;

/**
 * HOOK — Equipment lifecycle (HasLifecycleHooks).
 * Item katalog: created/updated/deleted -> activity log.
 */
class LogEquipmentActivity
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function created(EntityCreated $event): void
    {
        if (! $event->entity instanceof Equipment) {
            return;
        }

        $this->activityLog->log(
            'Equipment created: ' . ($event->entity->name ?? $event->entity->getKey()),
            $event->entity,
            $this->user(),
            ['event' => 'created'],
        );
    }

    public function updated(EntityUpdated $event): void
    {
        if (! $event->entity instanceof Equipment) {
            return;
        }

        $changes = $event->changes;

        $this->activityLog->log(
            'Equipment updated: ' . ($event->entity->name ?? $event->entity->getKey()) . ' (' . $this->describe($changes) . ')',
            $event->entity,
            $this->user(),
            ['event' => 'updated', 'changes' => $changes],
        );
    }

    public function deleted(EntityDeleted $event): void
    {
        if (! $event->entity instanceof Equipment) {
            return;
        }

        $this->activityLog->log(
            'Equipment deleted: ' . ($event->entity->name ?? $event->entity->getKey()),
            null,
            $this->user(),
            ['event' => 'deleted', 'id' => $event->entity->getKey()],
            null,
            $event->entityType,
        );
    }

    private function describe(array $changes): string
    {
        $parts = [];

        foreach ($changes as $field => $change) {
            if (in_array($field, ['updated_at', 'remember_token'], true)) {
                continue;
            }

            $label = Equipment::labels()[$field] ?? $field;
            $parts[] = $label . ': ' . $change['old'] . ' -> ' . $change['new'];
        }

        return implode(', ', $parts);
    }

    private function user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return auth('sanctum')->user() ?? auth()->user();
    }
}
