<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'Credit/Debit Card',
                'type' => 'card',
                'description' => 'Visa, MasterCard, American Express',
                'is_active' => true,
                'sort_order' => 1,
                'processing_fee' => 2.9,
                'config' => [
                    'supported_cards' => ['visa', 'mastercard', 'amex'],
                    'min_amount' => 1.00,
                    'max_amount' => 50000.00
                ]
            ],
            [
                'name' => 'PayPal',
                'type' => 'paypal',
                'description' => 'Pay with your PayPal account',
                'is_active' => true,
                'sort_order' => 2,
                'processing_fee' => 3.5,
                'config' => [
                    'min_amount' => 1.00,
                    'max_amount' => 25000.00
                ]
            ],
            [
                'name' => 'UPI',
                'type' => 'upi',
                'description' => 'Pay using UPI (India)',
                'is_active' => true,
                'sort_order' => 3,
                'processing_fee' => 0.5,
                'config' => [
                    'min_amount' => 1.00,
                    'max_amount' => 100000.00
                ]
            ],
            [
                'name' => 'Bank Transfer',
                'type' => 'bank_transfer',
                'description' => 'Direct bank transfer',
                'is_active' => true,
                'sort_order' => 4,
                'processing_fee' => 1.0,
                'config' => [
                    'min_amount' => 100.00,
                    'max_amount' => 500000.00
                ]
            ]
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }
    }
}
