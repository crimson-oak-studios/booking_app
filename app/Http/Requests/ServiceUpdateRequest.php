<?php
namespace App\Http\Requests; use Illuminate\Foundation\Http\FormRequest;
class ServiceUpdateRequest extends FormRequest { public function authorize(): bool { return true; } public function rules(): array { return ['name'=>'required|string|max:255','description'=>'nullable|string','duration_minutes'=>'required|integer|min:5','price_cents'=>'required|integer|min:0','is_active'=>'boolean']; }}
