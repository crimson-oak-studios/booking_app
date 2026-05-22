<?php
namespace App\Http\Resources;
use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
class BusinessResource extends JsonResource { public function toArray(Request ): array { return parent::toArray();} }
