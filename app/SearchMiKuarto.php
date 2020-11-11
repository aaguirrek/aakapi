<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    protected $table = "tabdatasearchtable";
    protected $casts = [
        'data' => 'json',
    ];
}
