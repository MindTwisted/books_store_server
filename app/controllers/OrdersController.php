<?php

namespace app\controllers;

use libs\Auth;
use libs\View;
use libs\Validator;
use libs\Input;

use app\models\OrdersModel;
use app\models\CartModel;

class OrdersController
{
    protected $ordersModel;
    protected $cartModel;

    public function __construct()
    {
        $this->ordersModel = new OrdersModel();
        $this->cartModel = new CartModel();
    }

    public function index()
    {
        $sortRules = [];

        if ($sortBy = Input::get('sort_by'))
        {
            $sortBy = explode(',', $sortBy);
            
            $availableSortRules = ['created_at', 'total_price', 'total_discount'];
            $availableSortOrders = ['asc', 'desc'];
            
            foreach ($sortBy as $sort)
            {
                $sort = explode(':', $sort);

                if (count($sort) !== 2)
                {
                    continue;
                }

                $key = strtolower(trim($sort[0]));
                $value = strtolower(trim($sort[1]));
                $value = in_array($value, $availableSortOrders) ? $value : 'asc';

                if (!in_array($key, $availableSortRules))
                {
                    continue;
                }

                $sortRules[] = [$key, strtoupper($value)];
            }
        }

        $user = Auth::user();

        if ('admin' !== $user['role'])
        {
            $orders = $this->ordersModel->getOrders(null, $user['id'], $sortRules);

            return View::render([
                'data' => $orders
            ]);
        }

        $orders = $this->ordersModel->getOrders(null, null, $sortRules);

        return View::render([
            'data' => $orders
        ]);
    }

    public function show($id)
    {
        $order = $this->ordersModel->getOrders($id);

        return View::render([
            'data' => $order
        ]);
    }

    public function store()
    {
        $user = Auth::user();

        $validator = Validator::make([
            'payment_type' => "required|exists:payment_types:id"
        ]);

        if ($validator->fails())
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => $validator->errors()
            ], 422);
        }

        $cart = $this->cartModel->getUsersCart($user['id']);

        if (count($cart) === 0)
        {
            View::render([
                'text' => "There are no books in cart."
            ], 409);
        }

        $paymentType = Input::get('payment_type');

        $orderId = $this->ordersModel->addOrder($user, $cart, $paymentType);

        return View::render([
            'text' => "Order with id '$orderId' was successfully added."
        ]);
    }

    public function update($id)
    {
        $validator = Validator::make([
            'status' => "required|included:(in_process, done)"
        ]);

        if ($validator->fails())
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => $validator->errors()
            ], 422);
        }

        $status = Input::get('status');

        $this->ordersModel->updateOrder($id, $status);

        return View::render([
            'text' => "Order with id '$id' was successfully updated."
        ]);
    }

    public function delete($id)
    {
        $this->ordersModel->deleteOrder($id);

        return View::render([
            'text' => "Order with id '$id' was successfully deleted."
        ]);
    }
}