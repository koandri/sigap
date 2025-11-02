<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PrintedForm;
use App\Models\User;

final class PrintedFormPolicy
{
    /**
     * Determine whether the user can return the printed form.
     */
    public function returnForm(User $user, PrintedForm $printedForm): bool
    {
        // User can return forms issued to them
        if ($printedForm->issued_to === $user->id) {
            return true;
        }

        // User's manager can return forms issued to their staff
        $issuedToUser = $printedForm->issuedTo;
        if ($issuedToUser && $issuedToUser->manager_id === $user->id) {
            return true;
        }

        return false;
    }
}
