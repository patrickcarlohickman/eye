<?php

namespace Eyewitness\Eye\Repo\Notifications;

use Eyewitness\Eye\Repo\Model;

class History extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eyewitness_io_notification_history';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime'
    ];
}
