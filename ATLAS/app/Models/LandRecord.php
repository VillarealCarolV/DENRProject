<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $survey_no
 * @property float $total_area
 * @property string $location
 * @property bool $is_subdivided
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class LandRecord extends Model
{
    protected $fillable = ['survey_no', 'total_area', 'location', 'is_subdivided'];
}
