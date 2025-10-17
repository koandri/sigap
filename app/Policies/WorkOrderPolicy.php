<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

final class WorkOrderPolicy
{
    /**
     * Determine if user can view any work orders.
     */
    public function viewAny(User $user): bool
    {
        // Super Admin and Owner can view all
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        // Users with view permission can view their own or their staff's WOs
        return $user->can('maintenance.work-orders.view');
    }

    /**
     * Determine if user can view the work order.
     */
    public function view(User $user, WorkOrder $workOrder): bool
    {
        // Super Admin and Owner can view all
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        // Engineering Staff can view all work orders
        if ($user->hasRole('Engineering')) {
            return true;
        }

        // Engineering Operator can only view work orders assigned to them
        if ($user->hasRole('Engineering Operator')) {
            return $workOrder->assigned_to === $user->id;
        }

        // Creator can view
        if ($workOrder->requested_by === $user->id) {
            return true;
        }

        // Manager of creator can view
        if ($workOrder->requestedBy->manager_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can create work orders.
     */
    public function create(User $user): bool
    {
        return $user->can('maintenance.work-orders.create');
    }

    /**
     * Determine if user can update the work order.
     */
    public function update(User $user, WorkOrder $workOrder): bool
    {
        // Super Admin and Owner can update all
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        // Creator can update if no work has been done (submitted or assigned status)
        if ($workOrder->requested_by === $user->id) {
            // Can fully edit if no work started
            if (in_array($workOrder->status, ['submitted', 'assigned'])) {
                return true;
            }
            
            // Can only edit certain fields if work has started
            if (in_array($workOrder->status, ['in-progress', 'pending-verification', 'verified', 'rework'])) {
                return true; // Limited update capability
            }
        }

        // Manager of creator has same rights as creator
        if ($workOrder->requestedBy->manager_id === $user->id) {
            if (in_array($workOrder->status, ['submitted', 'assigned', 'in-progress', 'pending-verification', 'verified', 'rework'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if user can delete the work order.
     */
    public function delete(User $user, WorkOrder $workOrder): bool
    {
        // Super Admin and Owner can delete any non-completed WO
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return $workOrder->status !== 'completed';
        }

        // Creator can delete only if no work has been done
        if ($workOrder->requested_by === $user->id && in_array($workOrder->status, ['submitted', 'assigned'])) {
            return true;
        }

        // Manager of creator can delete if no work has been done
        if ($workOrder->requestedBy->manager_id === $user->id && in_array($workOrder->status, ['submitted', 'assigned'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can assign the work order.
     */
    public function assign(User $user, WorkOrder $workOrder): bool
    {
        // Must have assign permission
        if (!$user->can('maintenance.work-orders.assign')) {
            return false;
        }

        // Only Super Admin, Owner, or Engineering can assign
        return $user->hasRole(['Super Admin', 'Owner', 'Engineering']);
    }

    /**
     * Determine if user can perform work on the work order.
     */
    public function work(User $user, WorkOrder $workOrder): bool
    {
        // Must be assigned to this work order
        if ($workOrder->assigned_to !== $user->id) {
            return false;
        }

        // Must have work permission
        return $user->can('maintenance.work-orders.work');
    }

    /**
     * Determine if user can verify the work order.
     */
    public function verify(User $user, WorkOrder $workOrder): bool
    {
        // Must have verify permission
        if (!$user->can('maintenance.work-orders.verify')) {
            return false;
        }

        // Only Engineering, Super Admin, and Owner can verify
        return $user->hasRole(['Super Admin', 'Owner', 'Engineering']);
    }

    /**
     * Determine if user can close the work order.
     */
    public function close(User $user, WorkOrder $workOrder): bool
    {
        // Super Admin and Owner can close
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        // Work order must be verified first
        if ($workOrder->status !== 'verified') {
            return false;
        }

        // Must have close permission
        if (!$user->can('maintenance.work-orders.close')) {
            return false;
        }

        // Creator can close
        if ($workOrder->requested_by === $user->id) {
            return true;
        }

        // Manager of creator can close
        if ($workOrder->requestedBy->manager_id === $user->id) {
            return true;
        }

        return false;
    }
}

