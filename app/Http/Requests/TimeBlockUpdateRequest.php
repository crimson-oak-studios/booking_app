<?php
namespace App\Http\Requests; use Illuminate\Foundation\Http\FormRequest;
class TimeBlockUpdateRequest extends FormRequest { public function authorize(): bool { return true; } public function rules(): array { return ['staff_user_id'=>'nullable|integer|exists:users,id','starts_at'=>'required|date','ends_at'=>'required|date|after:starts_at','reason'=>'nullable|string']; }}
