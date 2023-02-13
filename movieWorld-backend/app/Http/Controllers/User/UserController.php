<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\EntityController;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserResourceCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

class UserController extends EntityController
{
    public function __construct()
    {
        //************ Middlewares ************\\
        $this->middleware('auth:sanctum', ['only' => ['update', 'destroy', 'index', 'show']]);

        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //get the number that we want to paginate user's records else default paginate 5 per page
        $items = $request->items ? $request->items : 5;
        //getting all active users
        $users = User::orderBy('id', 'desc')->paginate($items);
        return (new UserResourceCollection($users))->additional(['message' => Lang::get('user.success_fetching_users')])->response()->setStatusCode(Response::HTTP_OK);
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
            $exceptions = ['password', 'password_confirmation'];
            $entity = $this->saveEntity($request->except($exceptions));
            if ($entity) {
                $this->attachToUser($request->get('password'), $entity);
                $entity->save();
            }
            return (new UserResource($entity))->additional(['message' => Lang::get('user.created_ok')])->response()->setStatusCode(Response::HTTP_OK);
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
        $user = User::withTrashed()->where('id', $id)->first();
        if (!$user) {
            $data['message'] =  trans("user.user_not_found");
            return response()->json($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($user && $user->deleted_at != null) {
            $data['message'] =  trans("user.user_is_soft_deleted");
            return response()->json($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return (new UserResource($user))->additional(['message' => Lang::get('user.fetched_ok')])->response()->setStatusCode(Response::HTTP_OK);
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
            $exceptions = ['password', 'password_confirmation'];
            $entity = $this->saveEntity($request->except($exceptions), $entity);
            if ($entity && $request->get('password')) {
                $this->attachToUser($request->get('password'), $entity);
            }
            $entity->save();

            return (new UserResource($entity))->additional(['message' => Lang::get('user.updated_ok')])->response()->setStatusCode(Response::HTTP_OK);
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
        //getting the user and checking if it is soft deleted else deleted the selected user
        $user = User::withTrashed()->where('id', $id)->first();
        if (!$user) {
            $data['message'] =  trans("user.user_not_found");
            return response()->json($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($user && $user->deleted_at != null) {
            return response()->json(['message' => trans("user.user_is_already_deleted")], Response::HTTP_OK);
        }

        $user->delete();
        return response()->json(['message' => trans("user.success_deleted_user")], Response::HTTP_OK);
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
        $name_rule = $entity ? 'max:20' : 'required|min:3|max:20';
        $last_name_rule = $entity ? 'max:40' : 'required|min:3|max:40';
        $username_rule = $entity ? 'min:3|max:100|unique:users,username,' . $entity->id : 'min:3|max:100|required|unique:users';
        $password_rule = $entity ? '' : 'required|min:3';
        $password_confirmation_rule = 'required_with:password|same:password|min:3';
        $email_rule = $entity ? 'email|max:255|unique:users,email,' . $entity->id : 'email|max:255|required|unique:users';

        return [
            'name'                        => $name_rule,
            'last_name'                   => $last_name_rule,
            'username'                    => $username_rule,
            'password'                    => $password_rule,
            'password_confirmation'       => $password_confirmation_rule,
            'email'                       => $email_rule
        ];
    }
}
