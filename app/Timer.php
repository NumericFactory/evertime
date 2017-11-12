<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Timer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'timer_type', 'offset_days', 'offset_hours', 'offset_minutes', 'offset_seconds', 'deadline', 'timezone', 'active_link', 'expired_link', 'label_days', 'label_hours', 'label_minutes', 'label_seconds', 'frenzy', 'styles', 'expired_image', 'upload_custom_image'];

    /**
     * Get the user that owns the timer.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
