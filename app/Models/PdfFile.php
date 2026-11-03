<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfFile extends Model
{
    /** @use HasFactory<\Database\Factories\PdfFileFactory> */
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'title',
        'path',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
