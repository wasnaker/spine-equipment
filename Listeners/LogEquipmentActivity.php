<?php

declare(strict_types=1);

namespace Modules\Equipment\Listeners;

use Modules\Equipment\Models\CustomerEquipment;
use Modules\Customer\Models\Customer;
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
        CustomerEquipment::class,
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
            $this->label($event->entity) . ' created: ' . $this->displayName($event->entity),
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
            $this->label($event->entity) . ' updated: ' . $this->displayName($event->entity) . ' (' . $this->describe($event->entity, $changes) . ')',
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
            $this->label($event->entity) . ' deleted: ' . $this->displayName($event->entity),
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

    private function displayName(object $entity): string
    {
        if ($entity instanceof CustomerEquipment) {
            return $entity->unit_name ?? $entity->getKey();
        }

        return $entity->name ?? $entity->getKey();
    }

    private function label(object $entity): string
    {
        return match (get_class($entity)) {
            EquipmentGroup::class => 'EquipmentGroup',
            EquipmentSubgroup::class => 'EquipmentSubgroup',
            EquipmentCategory::class => 'EquipmentCategory',
            CustomerEquipment::class => 'CustomerEquipment',
            default => 'Equipment',
        };
    }

    private function describe(object $entity, array $changes): string
    {
        $parts = [];
        $labels = method_exists($entity, 'labels') ? $entity::labels() : [];

        // FK ditampilkan sebagai nama, bukan id (equipment/customer).
        $fkNames = [
            'equipment_id' => Equipment::class,
            'customer_id'  => Customer::class,
        ];

        foreach ($changes as $field => $change) {
            if (in_array($field, ['updated_at', 'remember_token'], true)) {
                continue;
            }

            $old = $change['old'];
            $new = $change['new'];
            if (isset($fkNames[$field])) {
                $model = $fkNames[$field];
                $old = $model::find($old)?->name ?? $old;
                $new = $model::find($new)?->name ?? $new;
            }

            $label = $labels[$field] ?? $field;
            $parts[] = $label . ': ' . $old . ' -> ' . $new;
        }

        return implode(', ', $parts);
    }

    private function user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return auth('sanctum')->user() ?? auth()->user();
    }
}
