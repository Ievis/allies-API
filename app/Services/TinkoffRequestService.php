<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TinkoffRequestService
{
    private $terminal_key;
    private $password;
    private $base_api_url;
    private $customer_key;

    private $api_method_name;
    private $request_data;

    private $order_id;
    private $amount;
    private $rebill_id;
    private $payment_id;

    public function __construct($user_id)
    {
        $this->terminal_key = env('TINKOFF_TERMINAL_KEY');
        $this->password = env('TINKOFFF_PASSWORD');
        $this->base_api_url = env('TINKOFF_BASE_API_URL');

        $this->customer_key = 'Customer' . $user_id;
    }

    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;

        return $this;
    }

    public function setPaymentId($payment_id)
    {
        $this->payment_id = $payment_id;

        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount * 100;

        return $this;
    }

    public function setRebillId($rebill_id)
    {
        $this->rebill_id = $rebill_id;

        return $this;
    }

    public function setChargeRequestData()
    {
        $this->request_data = [
            'Password' => $this->password,
            'PaymentId' => $this->payment_id,
            'RebillId' => $this->rebill_id,
            'TerminalKey' => $this->terminal_key
        ];

        return $this;
    }

    public function formChargeRequest()
    {
        $this->getToken($this->request_data);
        $this->api_method_name = 'Charge';

        return $this;
    }

    public function setCheckOrderRequestData()
    {
        $this->request_data = [
            'OrderId' => 37,
            'Password' => $this->password,
            'TerminalKey' => $this->terminal_key
        ];

        return $this;
    }

    public function formCheckOrderRequest()
    {
        $this->getToken($this->request_data);
        $this->api_method_name = 'CheckOrder';

        return $this;
    }

    public function setGetCustomerRequestData()
    {
        $this->request_data = [
            'CustomerKey' => $this->customer_key,
            'Password' => $this->password,
            'TerminalKey' => $this->terminal_key
        ];

        return $this;
    }

    public function formGetCustomerRequest()
    {
        $this->getToken($this->request_data);
        $this->api_method_name = 'GetCustomer';

        return $this;
    }

    public function setAddCustomerRequestData()
    {
        $this->request_data = [
            'CustomerKey' => $this->customer_key,
            'Password' => $this->password,
            'TerminalKey' => $this->terminal_key
        ];

        return $this;
    }

    public function formGetCardListRequest()
    {
        $this->getToken($this->request_data);
        $this->api_method_name = 'GetCardList';

        return $this;
    }

    public function setGetCardListRequestData()
    {
        $this->request_data = [
            'CustomerKey' => $this->customer_key,
            'Password' => $this->password,
            'TerminalKey' => $this->terminal_key
        ];

        return $this;
    }

    public function formAddCustomerRequest()
    {
        $this->getToken($this->request_data);
        $this->api_method_name = 'AddCustomer';

        return $this;
    }

    public function makeRequest()
    {
        return json_decode(Http::post($this->base_api_url . $this->api_method_name, $this->request_data));
    }

    public function setInitRequestData($is_annual = true)
    {
        $this->request_data = $is_annual
            ? [
                'Amount' => $this->amount,
                //'CustomerKey' => $this->customer_key,
                'NotificationURL' => env('SERVER_HOST') . '/api/v1/payment/callback',
                'OrderId' => $this->order_id,
                'Password' => $this->password,
                //'Recurrent' => 'Y',
                'SuccessURL' => env('SERVER_HOST') . '/?success=1',
                'FailURL' => env('SERVER_HOST') . '/?success=0',
                'TerminalKey' => $this->terminal_key
            ]
            : [
                'Amount' => $this->amount,
                'CustomerKey' => $this->customer_key,
                'NotificationURL' => env('SERVER_HOST') . '/api/v1/payment/callback',
                'OrderId' => $this->order_id,
                'Password' => $this->password,
                'Recurrent' => 'Y',
                'SuccessURL' => env('SERVER_HOST') . '/?success=1',
                'FailURL' => env('SERVER_HOST') . '/?success=0',
                'TerminalKey' => $this->terminal_key
            ];

        return $this;
    }

    public function formInitRequest()
    {
        $this->getToken($this->request_data);
        $this->api_method_name = 'Init';

        return $this;
    }

    private function getToken()
    {
        $request_string = implode('', $this->request_data);
        $token = hash('sha256', $request_string);
        $this->request_data['Token'] = $token;

        unset($this->request_data['Password']);
    }
}
