<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UploadLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',      
        'uploaded_by',   
        'status',         
        'message'        
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
