<?php

namespace App;

class Admin extends User
{
    protected $guard = 'admin';
    protected $table = 'users';
}
