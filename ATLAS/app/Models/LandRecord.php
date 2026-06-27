<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    protected $fillable = ['survey_no', 'remarks', 'total_area', 'location', 'is_subdivided'];

    /**
     * Get the child lots created from subdividing this parent lot.
     * Uses the subdivisions table as a bridge.
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(
            LandRecord::class,
            'subdivisions',
            'parent_lot_id',
            'child_lot_id'
        )->withTimestamps();
    }

    /**
     * Get the parent lot if this is a child of a subdivision.
     * Uses the subdivisions table as a bridge.
     */
    public function parent(): BelongsToMany
    {
        return $this->belongsToMany(
            LandRecord::class,
            'subdivisions',
            'child_lot_id',
            'parent_lot_id'
        )->withTimestamps();
    }
}
