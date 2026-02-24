<?php

namespace App\Repositories\User;

use App\Interfaces\User\UserRepositoryInterface;
use App\Models\User\User;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @var User
     */
    protected $user;

    /**
     * NetworkRepository constructor.
     *
     */
    public function __construct()
    {
        $this->user = new User();
    }

    public function get() {
        return User::authorizable()->with('roles')->filter(request()->only(['search']))->paginate(50)->withQueryString();
    }

    public function getById($id, $onlyActive = true) {

        $user = User::query();

        if($onlyActive) {
            $user->active();
        }

        $user->authorizable();

        return $user->whereId($id)->first();
    }

    public function count() {
        $user = User::query();

        $user->authorizable();

        return $user->count();
    }

    public function update($id, $data) {
        $user = User::whereId($id)->authorizable()->first();
        $user->update($data);

        return $user->fresh();
    }

    public function fetchByEmail($q) {
        return User::where('email', 'like', '%'.$q.'%')->get();
    }

}
