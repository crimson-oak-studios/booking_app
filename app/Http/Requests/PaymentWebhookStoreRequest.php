<?php
namespace App\Http\Requests; use Illuminate\Foundation\Http\FormRequest;
class PaymentWebhookStoreRequest extends FormRequest { public function authorize(): bool { return true; } public function rules(): array { return ['appointment_id'=>'required|integer|exists:appointments,id','status'=>'required|string']; }}
