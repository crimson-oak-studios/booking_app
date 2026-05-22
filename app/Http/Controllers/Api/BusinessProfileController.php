<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Http\Requests\BusinessUpdateRequest; use App\Http\Resources\BusinessResource;
class BusinessProfileController extends Controller {
    public function show() { return new BusinessResource(request()->user()->business); }
    public function update(BusinessUpdateRequest $request) { $b=$request->user()->business; $b->update($request->validated()); return new BusinessResource($b); }
}
