<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewGuideRequirement extends Model
{
    public $timestamps = false;

    protected $fillable = ['position', 'name'];
}
