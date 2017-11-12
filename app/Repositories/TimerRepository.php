<?php

namespace App\Repositories;

use App\User;
use App\Timer;

class TimerRepository
{
    /**
     * Get all of the timers for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUser(User $user)
    {
        return Timer::where('user_id', $user->id)
                    ->orderBy('id', 'DESC')
                    ->get();
    }
}