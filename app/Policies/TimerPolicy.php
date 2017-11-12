<?php

namespace App\Policies;

use App\User;
use App\Timer;
use Illuminate\Auth\Access\HandlesAuthorization;

class TimerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given user can delete the given timer.
     *
     * @param  User  $user
     * @param  Timer  $timer
     * @return bool
     */
    public function destroy(User $user, Timer $timer)
    {
        // return $user->id === $timer->user_id;
        return true;
    }
}
