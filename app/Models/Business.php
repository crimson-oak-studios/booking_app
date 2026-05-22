<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'timezone'];

    public function users(): HasMany { return $this->hasMany(User::class); }
    public function services(): HasMany { return $this->hasMany(Service::class); }
    public function customers(): HasMany { return $this->hasMany(Customer::class); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class); }
}
