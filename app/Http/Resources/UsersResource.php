<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UsersResource extends ResourceCollection
{
    protected $adminsTotal;

    public function __construct($resource, $adminsTotal = 0)
    {
        parent::__construct($resource);
        $this->adminsTotal = $adminsTotal;
    }

    public function toArray($request): array
    {
        return [
            'users' => $this->collection->transform(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                ];
            }),
            'pagination' => [
                'current_page' => $this->resource->currentPage(),
                'per_page' => $this->resource->perPage(),
                'total' => $this->resource->total(),
                'last_page' => $this->resource->lastPage(),
                'from' => $this->resource->firstItem(),
                'to' => $this->resource->lastItem(),
            ],
            'admins_total' => $this->adminsTotal
        ];
    }
}
