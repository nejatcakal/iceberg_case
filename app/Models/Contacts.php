<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacts extends Model
{
    use HasFactory;
    public $timestamps = false;
    /**
     * return with foreign key.
     *
     * @param  int $column
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function appointments ($column)
    {
        return $this->hasMany(Appointments::class, $column, 'id');

    }//end appointments()

}
