<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'status', // 'open', 'pending', 'resolved', 'closed'
        'contactable_id',
        'contactable_type',
    ];

    /**
     * Get the parent contactable model (user or vendor).
     */
    public function contactable()
    {
        return $this->morphTo();
    }

    /**
     * Get all replies for this contact.
     */
    public function replies()
    {
        return $this->hasMany(ContactReply::class);
    }


}
