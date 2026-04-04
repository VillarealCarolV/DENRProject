<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $application_id
 * @property string $status
 * @property string|null $remarks
 * @property string $updated_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class StatusHistory extends Model
{
    protected $fillable = ['application_id', 'status', 'remarks', 'updated_by'];
}
