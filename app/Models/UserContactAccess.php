<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserContactAccess extends Model
{
    protected $table = 'user_contact_access';

    protected $fillable = [
        'user_id',
        'contact_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
