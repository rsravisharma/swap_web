<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    /**
     * Check if admin is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function uploadedBy()
    {
        return $this->belongsTo(Admin::class, 'uploaded_by_admin_id');
    }

    public function uploadedBooks()
    {
        return $this->hasMany(PdfBook::class, 'uploaded_by_admin_id');
    }

    /**
    * Check if admin is manager
    */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if admin is moderator
     */
    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    /**
     * Check if admin is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Scope for active admins
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for super admins
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('role', 'super_admin');
    }

    /**
     * Update last login information
     */
    public function updateLastLogin($ip)
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }
}
