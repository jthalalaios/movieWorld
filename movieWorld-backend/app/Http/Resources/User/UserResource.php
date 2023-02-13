<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        /** @var \App\Models\User */
        $user = $this;

        if ($user->deleted_at != null) {
            return [
                'id'                          => $user->id,
                'name'                        => $user->name,
                'last_name'                   => $user->last_name,
                'username'                    => $user->username,
                'email'                       => $user->email,
                'created_at'                  => $user->created_at,
                'updated_at'                  => $user->updated_at,
                'deleted_at'                  => $user->deleted_at,
            ];
        } else {
            return [
                'id'                          => $user->id,
                'name'                        => $user->name,
                'last_name'                   => $user->last_name,
                'username'                    => $user->username,
                'email'                       => $user->email,
                'created_at'                  => $user->created_at,
                'updated_at'                  => $user->updated_at,
            ];
        }
    }
}
