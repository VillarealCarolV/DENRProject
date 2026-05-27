<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $tracking_no
 * @property int $applicant_id
 * @property int $land_record_id
 * @property \Illuminate\Support\Carbon $date_received
 * @property string|null $lot_type
 * @property string|null $new_lot_number
 * @property float|null $subdivided_area
 * @property float|null $remaining_area
 * @property string|null $land_officer_remarks
 * @property int|null $land_officer_id
 * @property \Illuminate\Support\Carbon|null $assessed_at
 * @property string|null $patent_details
 * @property string|null $patent_type
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Application extends Model
{
    protected $fillable = [
        'tracking_no', 
        'applicant_id', 
        'land_record_id', 
        'date_received', 
        'patent_details', 
        'patent_type',
        'lot_type',
        'new_lot_number',
        'subdivided_area',
        'remaining_area',
        'land_officer_remarks',
        'land_officer_id',
        'assessed_at'
    ];

    protected $casts = [
        'date_received' => 'datetime',
        'assessed_at' => 'datetime',
        'subdivided_area' => 'decimal:2',
        'remaining_area' => 'decimal:2',
    ];

    /**
     * Get the applicant that owns this application.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    /**
     * Get the land record associated with this application.
     */
    public function landRecord(): BelongsTo
    {
        return $this->belongsTo(LandRecord::class);
    }

    /**
     * Get the land officer that assessed this application.
     */
    public function landOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'land_officer_id');
    }

    /**
     * Get the status history for this application.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(StatusHistory::class);
    }
}
