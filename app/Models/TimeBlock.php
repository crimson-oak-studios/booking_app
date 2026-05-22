<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TimeBlock extends Model {
    protected $fillable=['business_id','staff_user_id','starts_at','ends_at','reason'];
    protected $casts=['starts_at'=>'datetime','ends_at'=>'datetime'];
}
