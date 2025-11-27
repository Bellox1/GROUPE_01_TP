<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    const ROLE_ADMIN = 'admin';

    const ROLE_TEACHER = 'teacher';

    const ROLE_STUDENT = 'student';

    /**
     * Les attributs qui peuvent être assignés en masse.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Les attributs qui doivent être masqués pour la sérialisation.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Obtenir les attributs qui doivent être castés.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Obtenir les initiales de l'utilisateur
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Obtenir les cours enseignés par cet utilisateur (s'il est enseignant).
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_teacher', 'teacher_id', 'course_id');
    }

    /**
     * Obtenir les groupes auxquels cet utilisateur appartient (s'il est étudiant).
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }
}
