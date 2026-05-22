<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Http\Requests\StaffStoreRequest; use App\Http\Resources\UserResource; use App\Models\User;
class StaffController extends Controller { public function index(){return UserResource::collection(request()->user()->business->users()->where('role','staff')->get());} public function store(StaffStoreRequest $r){$u=User::create($r->validated()+['business_id'=>$r->user()->business_id,'role'=>'staff']);return (new UserResource($u))->response()->setStatusCode(201);} }
