<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserAuthenticationResource extends JsonResource
{
    /** @var string|null */
    protected ?string $token = null;

    /**
     * Set the response token.
     *
     * @param  string  $token
     * @return $this
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

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

        return [
            'id'                          => $user->id,
            'name'                        => $user->name,
            'last_name'                   => $user->last_name,
            'username'                    => $user->username,
            'email'                       => $user->email,
            'token'                       => $this->when($this->token, $this->token),
        ];
    }
}
