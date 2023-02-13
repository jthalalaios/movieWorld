<?php

namespace App\Http\Controllers\Movie;

use App\Http\Controllers\EntityController;
use App\Http\Resources\Movie\MovieResource;
use App\Http\Resources\MovieLike\MovieLikeResourceCollection;
use App\Models\Movie;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class MovieLikeController extends EntityController
{

    public function __construct()
    {
        //************ Middlewares ************\\
        $this->middleware('auth:sanctum', ['only' => ['store']]);
        parent::__construct();
    }

    /**
     * Store a newly created resource in storage.
     * Like and Unlike a movie
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $current_date_time = Carbon::now()->toDateTimeString();
        $movie_id = $request->movie_id;
        $like_status = $request->like;
        $current_user = Auth::user();

        // validate if movie exists by id and if the current user that wants to hate/unhate its not creted by him/her
        $movie_exists = $this->getValidationRules($request, (int) $movie_id, $current_user)['movie_exists'];
        $movie_created_by_this_current_user = $this->getValidationRules($request, (int) $movie_id, $current_user)['movie_created_by_this_current_user'];

        if ($movie_created_by_this_current_user == true) {
            $data['message'] =  Lang::get("movie.user_has_no_rights_to_like_unlike_movie");
            return response()->json($data, Response::HTTP_BAD_REQUEST);
        }

        if ($movie_exists == false) {
            $data['message'] =  trans("movie.movie_not_found");
            return response()->json($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            //checking if the user has already liked the movie
            $user_already_liked = DB::table('movie_user_likes')->where('user_id', $current_user->id)->where('movie_id', $movie_id)->exists();
            if ($user_already_liked == true && $like_status == true) {
                $data['message'] =  trans("movie.user_already_like_this_movie");
                return response()->json($data, Response::HTTP_BAD_REQUEST);
            } else {
                //checking the like from payload if its status is true, insert user's like for this movie and remove the hate if it exists
                if ($like_status == true) {
                    DB::table('movie_user_likes')->insert([
                        'movie_id'    => $movie_id,
                        'user_id'     => $current_user->id,
                        'created_at'  => $current_date_time,
                        'updated_at'  => $current_date_time
                    ]);

                    DB::table('movie_user_hates')->where('user_id', $current_user->id)->where('movie_id', $movie_id)->delete();
                    $data['message'] =  trans("movie.user_movie_like_success");
                    $data['like'] = true;
                    return response()->json($data, Response::HTTP_OK);
                } else {
                    if ($user_already_liked == true) {
                        DB::table('movie_user_likes')->where('user_id', $current_user->id)->where('movie_id', $movie_id)->delete();
                        $data['message'] =  trans("movie.user_movie_unlike_success");
                        return response()->json($data, Response::HTTP_OK);
                    } else {
                        $data['message'] =  Lang::get("movie.user_movie_can_not_unlike");
                        return response()->json($data, Response::HTTP_BAD_REQUEST);
                    }
                }
            }
        }
    }

    /**
     * Returns form validation rules
     * @param obj, entity  , used for unique validations on update
     * @return array
     */
    protected function getValidationRules($request, int $movie_id, $current_user)
    {
        $movie_exists = Movie::where('id', $movie_id)->exists();
        $movie_created_by_this_current_user = Movie::where('id', $movie_id)->where('user_id', $current_user->id)->exists();

        $validate_data = [
            'movie_exists'                       => $movie_exists,
            'movie_created_by_this_current_user' => $movie_created_by_this_current_user,
        ];
        return $validate_data;
    }
}
