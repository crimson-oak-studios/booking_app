<?php
namespace App\Http\Requests; use Illuminate\Foundation\Http\FormRequest;
class AppointmentUpdateRequest extends FormRequest { public function authorize(): bool { return true; } public function rules(): array { return ['service_id'=>'required|integer|exists:services,id','customer_id'=>'required|integer|exists:customers,id','staff_user_id'=>'nullable|integer|exists:users,id','starts_at'=>'required|date','ends_at'=>'required|date|after:starts_at','status'=>'nullable|string','payment_status'=>'nullable|string','notes'=>'nullable|string']; }}
