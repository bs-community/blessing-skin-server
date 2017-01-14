<?php

namespace App\Http\Controllers;

use Session;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Services\Repositories\UserRepository;

class HomeController extends Controller
{
    public function index(UserRepository $users, Request $request)
    {
        return view('index')->with('user', $users->getCurrentUser());
    }

}
