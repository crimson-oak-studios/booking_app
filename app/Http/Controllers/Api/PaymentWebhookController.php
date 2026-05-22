<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Http\Requests\PaymentWebhookStoreRequest; use App\Mail\BookingConfirmationMail; use App\Models\Appointment; use Illuminate\Support\Facades\Mail;
class PaymentWebhookController extends Controller { public function store(PaymentWebhookStoreRequest $r){$a=Appointment::findOrFail($r->integer('appointment_id'));$status=(string)$r->input('status');$a->update(['payment_status'=>$status,'status'=>$status==='paid'?'booked':$a->status]);if($a->payment_status==='paid'){Mail::to($a->customer->email)->send(new BookingConfirmationMail($a));}return response()->json(['ok'=>true]);} }
