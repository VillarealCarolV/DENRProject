<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $parent_lot_id
 * @property int $child_lot_id
 * @property \Illuminate\Support\Carbon $split_date
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Subdivision extends Model
{
    protected $fillable = ['parent_lot_id', 'child_lot_id', 'split_date'];
}
