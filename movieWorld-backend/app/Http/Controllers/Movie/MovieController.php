<?php

namespace App\Http\Controllers\Movie;

use App\Http\Controllers\EntityController;
use App\Http\Resources\Movie\MovieResource;
use App\Http\Resources\Movie\MovieResourceCollection;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

class MovieController extends EntityController
{
    public function __construct()
    {
        //************ Middlewares ************\\
        $this->middleware('auth:sanctum', ['except' => ['index']]);

        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //for ordering by date, likes, hates
        $ordering_schema_column = [
            'dates', 'likes', 'hates'
        ];
        $ordering_schema = [
            'asc', 'desc'
        ];

        $movies_query = Movie::leftJoin('movie_user_hates', 'movies.id', '=', 'movie_user_hates.movie_id')
            ->leftJoin("movie_user_likes", "movies.id", "=", "movie_user_likes.movie_id")
            ->leftJoin("users", "movies.user_id", "=", "users.id")->where('users.deleted_at', '=', null);

        $movies_query = $movies_query->selectRaw(
            "movies.id as id,
            movies.title as title,
            movies.description as description,
            movies.user_id as user_id,
            movies.created_at as dates,
            count(movie_user_likes) as likes,
            count(movie_user_hates) as hates"
        );
        $movies_query->groupBy('movies.id');

        //getting the query params if we want to user ordering
        $order = $request->order;
        $order_by = $request->order_by;
        //items on the query params are used for paginate the records below the default pagination's number is 4 items per page
        $items = $request->items;

        //if order and order_key contain on ordering_schema_column and ordering_schema then order the query else default by id
        $movies = in_array($order_by, $ordering_schema_column) && in_array($order, $ordering_schema)
            ? $movies_query->orderBy($order_by, $order) : $movies_query->orderBy('movies.id', 'desc');

        $pagination_items = isset($items) ? $items : 4;
        $movies = $movies->paginate($pagination_items);

        //checking if user is logged and get the permissions like (like/unlike) and hate(hate/unhate)
        $like_permissions = $this->canLike();
        $hate_permissions = $this->canHate();
        $permissions = [
            'like_unlike' => $like_permissions,
            'hate_unhate' => $hate_permissions
        ];

        return (new MovieResourceCollection($movies))
            ->additional([
                'message' => Lang::get('movie.success_fetching_movies'), 'metadata' => ['permissions' => $permissions]
            ])->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // validate posted data
        $validator = Validator::make($request->all(), $this->getValidationRules($request));

        //if invalid, return response with error messages
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                "metadata" => [
                    "response_message" => $errors
                ],
                'message' => 'ERROR',
            ], Response::HTTP_BAD_REQUEST);
        } else {
            $entity = $this->saveEntity($request->all());
            $entity->save();
            return (new MovieResource($entity))->additional(['message' => Lang::get('movie.created_ok')])->response()->setStatusCode(Response::HTTP_OK);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //getting the user , if the user is deleted return the right message
        $movie_query = Movie::where('movies.id', $id)->leftJoin('movie_user_hates', 'movies.id', '=', 'movie_user_hates.movie_id')
            ->leftJoin("movie_user_likes", "movies.id", "=", "movie_user_likes.movie_id");

        $movie_query = $movie_query->selectRaw(
            "movies.id as id,
        movies.title as title,
        movies.description as description,
        movies.user_id as user_id,
        movies.created_at as dates,
        count(movie_user_likes) as likes,
        count(movie_user_hates) as hates"
        );

        $movie_query->groupBy('movies.id');
        $movie = $movie_query->first();

        if (!$movie) {
            $data['message'] =  trans("movie.movie_not_found");
            return response()->json($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return (new MovieResource($movie))->additional(['message' => Lang::get('movie.fetched_ok')])->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $entity = $this->getEntity($id);
        // validate posted data
        $validator = Validator::make($request->all(), $this->getValidationRules($request, $entity));

        //if invalid, return response with error messages
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                "metadata" => [
                    "response_message" => $errors
                ],
                'message' => 'ERROR',
            ], Response::HTTP_BAD_REQUEST);
        } else {
            $exceptions = ['user_id'];
            $entity = $this->saveEntity($request->except($exceptions), $entity);
            $entity->save();

            return (new MovieResource($entity))->additional(['message' => Lang::get('movie.updated_ok')])->response()->setStatusCode(Response::HTTP_OK);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $current_user = Auth::user();
        //getting the user and checking if it is soft deleted else deleted the selected user
        $movie = Movie::where('id', $id)->first();
        if (!$movie) {
            $data['message'] =  trans("movie.movie_not_found");
            return response()->json($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        //checking if the current user is the same user that made the movie, if yes delete movie
        if ($current_user->id != $movie->user_id) {
            return response()->json(['message' => trans("movie.user_can_not_delete_this_movie")], Response::HTTP_OK);
        }

        $movie->delete();
        return response()->json(['message' => trans("movie.success_deleted_movie")], Response::HTTP_OK);
    }

    /**
     * Inserts/updates entity
     * @param  array post  Posted data
     * @param  obj entity, on update cases
     * @param  array logging data
     * @return array ($entity)
     */
    protected function saveEntity($post, $entity = null)
    {
        return parent::saveEntity($post, $entity);
    }

    /**
     * Returns form validation rules
     * @param obj, entity  , used for unique validations on update
     * @return array
     */
    protected function getValidationRules($request, $entity = null)
    {
        $title_rule = $entity ? 'max:255|unique:movies,title,' . $entity->id : 'required|min:3|max:255|unique:movies';
        $description_rule = $entity ? 'max:3000' : 'required|max:3000';
        $user_id = $entity ? 'exists:users,id' : 'required|exists:users,id';

        return [
            'title'          => $title_rule,
            'description'    => $description_rule,
            'user_id'        => $user_id,
        ];
    }
}
