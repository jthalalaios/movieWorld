<?php

namespace App\Http\Resources\Movie;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class MovieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        /** @var \App\Models\Movie */
        $movie = $this;

        $user = User::withTrashed()->where('id', $movie->user_id)->first();
        $user_data = isset($user) ? $user_data = [
            'id'        => $user->id,
            'name'      => $user->name,
            'last_name' => $user->last_name,
        ] : null;

        return [
            'id'               => $movie->id,
            'title'            => $movie->title,
            'description'      => $movie->description,
            'user'             => $user_data,
            'created_at'       => $movie->dates != null ? $movie->dates : $movie->created_at,
            'likes'            => $movie->likes == null ? 0 : $movie->likes,
            'hates'            => $movie->hates == null ? 0 : $movie->hates
        ];
    }
}
