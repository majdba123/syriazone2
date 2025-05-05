<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id', // فقط معرف الاتصال
        'message',    // محتوى الرد
    ];

    /**
     * Get the contact that owns the reply.
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
