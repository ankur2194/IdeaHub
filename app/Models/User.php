<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'department',
        'job_title',
        'points',
        'level',
        'experience',
        'title',
        'total_badges',
        'ideas_submitted',
        'ideas_approved',
        'comments_posted',
        'likes_given',
        'likes_received',
        'is_active',
        'tenant_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the ideas for the user.
     */
    public function ideas()
    {
        return $this->hasMany(Idea::class);
    }

    /**
     * Get the comments for the user.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the approvals assigned to the user.
     */
    public function approvals()
    {
        return $this->hasMany(Approval::class, 'approver_id');
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the badges earned by the user.
     */
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at', 'progress')
            ->withTimestamps();
    }

    /**
     * Get the dashboards for the user.
     */
    public function dashboards()
    {
        return $this->hasMany(UserDashboard::class);
    }

    /**
     * Get the user's default dashboard.
     */
    public function defaultDashboard()
    {
        return $this->hasOne(UserDashboard::class)->where('is_default', true);
    }

    /**
     * Get the user's current rank/title based on level.
     */
    public function getRankAttribute(): string
    {
        return match(true) {
            $this->level >= 50 => 'Innovation Master',
            $this->level >= 40 => 'Visionary Leader',
            $this->level >= 30 => 'Expert Innovator',
            $this->level >= 20 => 'Senior Contributor',
            $this->level >= 10 => 'Active Contributor',
            $this->level >= 5 => 'Rising Star',
            default => 'Newcomer',
        };
    }

    /**
     * Calculate XP required for next level.
     */
    public function getXpForNextLevel(): int
    {
        // Formula: 100 * level^1.5
        return (int) (100 * pow($this->level, 1.5));
    }

    /**
     * Calculate progress toward next level (0-100).
     */
    public function getLevelProgressAttribute(): int
    {
        $xpNeeded = $this->getXpForNextLevel();
        return $xpNeeded > 0 ? (int) (($this->experience / $xpNeeded) * 100) : 0;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a department head.
     */
    public function isDepartmentHead(): bool
    {
        return $this->role === 'department_head';
    }

    /**
     * Check if user is a team lead.
     */
    public function isTeamLead(): bool
    {
        return $this->role === 'team_lead';
    }
}
