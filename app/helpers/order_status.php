<?php
/** app/helpers/order_status.php - order status labels and the allowed workflow. */

function orderStatuses(): array
{
    return [
        'pending'            => 'Pending',
        'confirmed'          => 'Confirmed',
        'picked_up'          => 'Picked Up',
        'processing'         => 'Processing',
        'ready_for_delivery' => 'Ready for Delivery',
        'out_for_delivery'   => 'Out for Delivery',
        'delivered'          => 'Delivered',
        'cancelled'          => 'Cancelled',
    ];
}

function statusLabel($status): string
{
    $all = orderStatuses();
    return $all[$status] ?? ucfirst(str_replace('_', ' ', (string) $status));
}

/** Coloured status indicator (already escaped). */
function statusBadge($status): string
{
    return '<span class="badge badge-' . e($status) . '">' . e(statusLabel($status)) . '</span>';
}

/** Workflow: Pending -> Confirmed -> Picked Up -> Processing -> Ready -> Out for Delivery -> Delivered. */
function statusTransitions(): array
{
    return [
        'pending'            => ['confirmed', 'cancelled'],
        'confirmed'          => ['picked_up', 'cancelled'],
        'picked_up'          => ['processing'],
        'processing'         => ['ready_for_delivery'],
        'ready_for_delivery' => ['out_for_delivery'],
        'out_for_delivery'   => ['delivered'],
        'delivered'          => [],
        'cancelled'          => [],
    ];
}

function allowedNextStatuses($status): array
{
    $t = statusTransitions();
    return $t[$status] ?? [];
}

function canTransition($from, $to): bool
{
    return in_array($to, allowedNextStatuses($from), true);
}

/** Steps shown in the customer tracking bar. */
function trackerSteps(): array
{
    return ['pending', 'confirmed', 'picked_up', 'processing', 'ready_for_delivery', 'out_for_delivery', 'delivered'];
}

/** Button text for a staff action. */
function statusActionLabel($status): string
{
    $labels = [
        'confirmed'          => 'Confirm Pickup Request',
        'picked_up'          => 'Mark as Picked Up',
        'processing'         => 'Start Processing',
        'ready_for_delivery' => 'Mark Ready for Delivery',
        'out_for_delivery'   => 'Mark Out for Delivery',
        'delivered'          => 'Confirm Delivery',
        'cancelled'          => 'Cancel Order',
    ];
    return $labels[$status] ?? statusLabel($status);
}

/** Dashboard groups: every status belongs to exactly one group. */
function statusGroups(): array
{
    return [
        'pending'    => ['pending'],
        'processing' => ['confirmed', 'picked_up', 'processing'],
        'completed'  => ['ready_for_delivery', 'out_for_delivery'],
        'delivered'  => ['delivered'],
        'cancelled'  => ['cancelled'],
    ];
}

function activeStatuses(): array
{
    return ['pending', 'confirmed', 'picked_up', 'processing', 'ready_for_delivery', 'out_for_delivery'];
}
