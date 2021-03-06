<?php

namespace app\controllers;

use libs\View;
use libs\Auth;
use libs\Validator;
use libs\Input;

use app\models\UsersModel;
use app\models\OrdersModel;

class UsersController
{
    protected $usersModel;
    protected $ordersModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
        $this->ordersModel = new OrdersModel();
    }

    public function index()
    {
        $users = $this->usersModel->getAllUsers();

        return View::render([
            'data' => $users
        ]);
    }

    public function show($id)
    {
        $user = $this->usersModel->getUserById($id);

        return View::render([
            'data' => $user
        ]);
    }

    public function showOrders($id)
    {
        $orders = $this->ordersModel->getOrders(null, $id);

        return View::render([
            'data' => $orders
        ]);
    }

    public function store()
    {    
        $validator = Validator::make([
            'name' => "required|minLength:6",
            'email' => "required|email|unique:users:email",
            'password' => "required|minLength:6"
        ]);

        if ($validator->fails())
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => $validator->errors()
            ], 422);
        }

        $name = Input::get('name');
        $email = Input::get('email');
        $password = Input::get('password');

        $this->usersModel->addUser($name, $email, $password);

        return View::render([
            'text' => "User '$name' was successfully registered."
        ]);
    }

    public function update($id)
    {
        $maxDiscount = MAX_DISCOUNT;

        $validator = Validator::make([
            'name' => "required|minLength:6",
            'email' => "required|email|unique:users:email:$id",
            'discount' => "numeric|min:0|max:$maxDiscount"
        ]);

        if ($validator->fails())
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => $validator->errors()
            ], 422);
        }

        $name = Input::get('name');
        $email = Input::get('email');
        $discount = Input::get('discount');

        $this->usersModel->updateUser($id, $name, $email, null, $discount);

        return View::render([
            'text' => "User '$name' was successfully updated."
        ]);
    }

    public function updateCurrent()
    {
        $user = Auth::user();
        
        $validator = Validator::make([
            'name' => "required|minLength:6",
            'email' => "required|email|unique:users:email:{$user['id']}",
            'password' => "required|minLength:6"
        ]);

        if ($validator->fails())
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => $validator->errors()
            ], 422);
        }

        $name = Input::get('name');
        $email = Input::get('email');
        $password = Input::get('password');

        $this->usersModel->updateUser($user['id'], $name, $email, $password);

        return View::render([
            'text' => "User '$name' was successfully updated."
        ]);
    }
}