<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Models\User;

use Route;

class EntityController extends Controller
{
    /**
     *
     * @var string, 1st url segment of controller routes
     */
    protected $routeBase;
    /**
     *
     * @var string name of base model used by controller
     */
    protected $modelName;

    /**
     * Constructor,
     * initializes baseRoute and modelName
     */
    public function __construct()
    {
        $this->routeBase = explode('.', Route::currentRouteName())[0];
        $this->modelName = ucfirst($this->routeBase);
        $this->modelName = 'App\Models\\' . '' . $this->modelName;
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
        $entity = $entity ?: new $this->modelName;
        //assign values to model's table fields
        foreach ($post as $key => $value) {
            $entity->$key = $value;
        }
        return $entity;
    }

    protected function attachToUser($password, $entity)
    {
        $user = isset($entity) ? $entity : new User();
        // set user's model password
        if ($password) {
            $user->password = Hash::make($password);
        }
        return $user;
    }

    /**
     * Returns entity object from id
     * @param  int $id
     * @return object
     */
    protected function getEntity($id)
    {
        $model = $this->modelName;
        $entity = $model::findOrFail($id);

        return $entity;
    }

    /**
     * Checks entity can be like by logged user
     * @return boolean
     */
    protected function canLike()
    {
        $logged_user = auth('sanctum')->check();
        $permission_like_status = !$logged_user ? false : true;

        return $permission_like_status;
    }

    /**
     * Checks entity can be hate by logged user
     * @return boolean
     */
    protected function canHate()
    {
        $logged_user = auth('sanctum')->check();
        $permission_hate_status = !$logged_user ? false : true;

        return $permission_hate_status;
    }
}
