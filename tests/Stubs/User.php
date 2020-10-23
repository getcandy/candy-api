<?php

namespace Tests\Stubs;

use GetCandy\Api\Core\Traits\HasCandy;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, HasCandy;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Create token.
     *
     * @param $string
     * @return string
     */
    public function createToken($string)
    {
        return $string;
    }
}
