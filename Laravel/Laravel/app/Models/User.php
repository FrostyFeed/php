<?php

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject; 

class User extends Authenticatable implements JWTSubject 
{
    use HasFactory, Notifiable;

    public const ROLE_CLIENT = 'Client';
    public const ROLE_MANAGER = 'Manager';
    public const ROLE_ADMIN = 'Admin';

    protected $fillable = ['name', 'email', 'password', 'role']; 
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

    public function getJWTIdentifier() { return $this->getKey(); }
    public function getJWTCustomClaims() { return ['role' => $this->role]; } 

    public function hasRole(string $role): bool { return $this->role === $role; }
    public function isClient(): bool { return $this->role === self::ROLE_CLIENT; }
    public function isManager(): bool { return $this->role === self::ROLE_MANAGER; }
    public function isAdmin(): bool { return $this->role === self::ROLE_ADMIN; }
}
