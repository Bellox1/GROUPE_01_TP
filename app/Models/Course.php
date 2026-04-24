<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory;

    protected $fillable = ['code', 'name', 'description', 'credits', 'quota_cm', 'quota_td', 'quota_tp'];

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_teacher', 'course_id', 'teacher_id');
    }
}
