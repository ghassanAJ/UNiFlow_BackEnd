<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Chapter extends Model
{
    /** @use HasFactory<\Database\Factories\ChapterFactory> */
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'sort_order',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class)->orderBy('sort_order');
    }

    public function pdfFiles(): HasMany
    {
        return $this->hasMany(PdfFile::class);
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }
}
