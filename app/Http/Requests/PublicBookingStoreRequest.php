<?php
namespace App\Http\Requests; use Illuminate\Foundation\Http\FormRequest;
class PublicBookingStoreRequest extends FormRequest { public function authorize(): bool { return true; } public function rules(): array { return ['business_id'=>'required|integer|exists:businesses,id','service_id'=>'required|integer|exists:services,id','customer_name'=>'required|string|max:255','customer_email'=>'required|email','customer_phone'=>'nullable|string|max:30','staff_user_id'=>'nullable|integer|exists:users,id','starts_at'=>'required|date']; }}
