<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientDocument extends Model
{
    use HasFactory;
     protected $fillable = [
        'client_id',
        'document_type',
        'document_number',
        'document_image1',
        'document_image2',
        'document_image3',
    ];
}
