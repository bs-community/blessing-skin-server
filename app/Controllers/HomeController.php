<?php

namespace App\Controllers;

use App\Models\User;

class HomeController extends BaseController
{

    public function index()
    {
        echo \View::make('index')->with('user', (isset($_SESSION['email']) ? new User($_SESSION['email']) : null));
    }

}
