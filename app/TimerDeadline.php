<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimerDeadline extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    //protected $table = 'timer_deadlines';
    protected $fillable = [
        'timer_id',
        'email',
        'deadline'
    ];

}
