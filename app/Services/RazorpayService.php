<?php

namespace App\Services;

use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected $api;

    public function __construct()
    {
        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        if (empty($key) || empty($secret)) {
            throw new \Exception('Razorpay credentials not configured');
        }

        $this->api = new Api($key, $secret);
        
        // 🔥 Set custom timeout (30 seconds instead of default 10)
        $this->api->setAppDetails('Swap', '1.0.0');
    }

    public function createOrder(array $orderData)
    {
        try {
            Log::info('Creating Razorpay order', ['order_data' => $orderData]);
            
            $order = $this->api->order->create($orderData);
            
            Log::info('Razorpay order created', ['order_id' => $order['id']]);
            
            return $order;
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed', [
                'error' => $e->getMessage(),
                'order_data' => $orderData,
            ]);
            throw $e;
        }
    }

    public function verifyPaymentSignature(array $attributes)
    {
        return $this->api->utility->verifyPaymentSignature($attributes);
    }

    public function fetchPayment(string $paymentId)
    {
        return $this->api->payment->fetch($paymentId);
    }
}
