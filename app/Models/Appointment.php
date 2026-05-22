<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Appointment extends Model {
 protected $fillable=['business_id','service_id','customer_id','staff_user_id','starts_at','ends_at','status','payment_status','square_payment_link_id','notes'];
 protected $casts=['starts_at'=>'datetime','ends_at'=>'datetime'];
 public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}
