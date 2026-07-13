<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
  use HasFactory, Notifiable, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
    'department',
    'job_title',
    'role',
    'status',
    'access_level',
    'provider',
    'provider_id',
    'avatar',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
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
      'password' => 'hashed',
      'access_level' => 'array',
    ];
  }

  /**
   * Get the faculty record associated with the user.
   */
  public function faculty(): HasOne
  {
    return $this->hasOne(Faculty::class, 'user_id');
  }

  /**
   * Get all evaluations for this faculty user.
   */
  public function evaluations()
  {
    return $this->hasMany(Evaluation::class, 'faculty_id');
  }
}
