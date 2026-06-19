<?php

namespace App\Services;

class PaymentGatewayService
{
    /**
     * Charge a given amount to the specified card details.
     */
    public function charge(float $amount, array $cardDetails): bool
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than zero.');
        }

        // Validate basic card details presence
        if (empty($cardDetails['cardholder_name']) || empty($cardDetails['card_number']) || empty($cardDetails['card_expiry']) || empty($cardDetails['card_cvc'])) {
            throw new \InvalidArgumentException('Incomplete payment details.');
        }

        // Basic format validations
        $cardNumber = preg_replace('/\s+/', '', $cardDetails['card_number']);
        if (! preg_match('/^\d{15,16}$/', $cardNumber)) {
            throw new \InvalidArgumentException('Invalid card number format.');
        }

        if (! preg_match('/^\d{3,4}$/', $cardDetails['card_cvc'])) {
            throw new \InvalidArgumentException('Invalid CVC format.');
        }

        if (! preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $cardDetails['card_expiry'])) {
            throw new \InvalidArgumentException('Invalid expiry date format. Expected MM/YY.');
        }

        // Simulate network processing delay (50ms)
        usleep(50000);

        return true;
    }
}
