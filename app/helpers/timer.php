<?php

use \App\Timer;
use Carbon\Carbon;

function getTimerStatus(Timer $timer){
    $now = Carbon::now($timer->timezone);
    if($timer->timer_type == 'evergreen'){
        return "Active";
    } else {
        $deadline = new Carbon($timer->deadline, $timer->timezone);
        $diff_seconds = $now->diffInSeconds($deadline, false);
        if($diff_seconds > 0){
            return "Active";
        } else {
            return "Exired";
        }
    }
}