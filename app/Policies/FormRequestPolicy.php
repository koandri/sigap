<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FormRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class FormRequestPolicy
{
    /**
     * Determine whether the user can view the form request.
     */
    public function view(User $user, FormRequest $formRequest): bool
    {
        // Super Admin and Owner can view any form request
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        // Document Control can view any form request
        if ($user->hasRole('Document Control')) {
            return true;
        }

        // User can view their own form requests
        if ($formRequest->requested_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create form requests.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('dms.forms.request');
    }

    /**
     * Determine whether the user can process the form request.
     */
    public function process(User $user, FormRequest $formRequest): bool
    {
        // Super Admin and Owner can process any form request
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        // Document Control can process form requests
        if ($user->hasRole('Document Control')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can acknowledge the form request.
     */
    public function acknowledge(User $user, FormRequest $formRequest): bool
    {
        return $this->process($user, $formRequest);
    }

    /**
     * Determine whether the user can mark the form request as ready.
     */
    public function markReady(User $user, FormRequest $formRequest): bool
    {
        return $this->process($user, $formRequest);
    }

    /**
     * Determine whether the user can collect the form request.
     */
    public function collect(User $user, FormRequest $formRequest): bool
    {
        // User can collect their own form requests
        if ($formRequest->requested_by === $user->id) {
            return true;
        }

        // Document Control can collect any form request
        if ($user->hasRole('Document Control')) {
            return true;
        }

        return false;
    }
}
