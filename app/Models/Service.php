<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Service extends Model { protected $fillable=['business_id','name','description','duration_minutes','price_cents','is_active']; }
