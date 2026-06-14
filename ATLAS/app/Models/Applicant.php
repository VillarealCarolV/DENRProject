<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $full_name
 * @property string|null $address
 * @property string|null $contact_no
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Applicant extends Model
{
    protected $fillable = ['full_name', 'address', 'contact_no'];

    /**
     * Get the applications associated with the applicant.
     */
    public function applications()
    {
        return $this->hasMany(Application::class, 'applicant_id');
    }
}
