<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    protected $table = 'user';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function link()
    {
        return $this->hasMany('App\Models\Link');
    }

    public function register($data)
    {
        $user = User::firstOrCreate(
            ['line_id' =>  $data["line_id"]],
            [
                'name' =>  $data["name"],
                'line_id' => $data["line_id"]
            ]
        );
        return $user;
    }
}
