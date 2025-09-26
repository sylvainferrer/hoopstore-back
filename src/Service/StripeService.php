<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    private string $secretKey;
    private string $publicKey;

    public function __construct(string $secretKey, string $publicKey)
    {
        $this->secretKey = $secretKey;
        $this->publicKey = $publicKey;
        Stripe::setApiKey($this->secretKey);
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function createCheckoutSession(array $lineItems, string $successUrl, string $cancelUrl, array $metadata, array $payment_intent_data, string $customerEmail): Session
    {
        return Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'customer_email' => $customerEmail,
            'line_items' => $lineItems,           
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'metadata' => $metadata,
            'payment_intent_data' => $payment_intent_data
        ]);
    }

    public function handleWebhook(string $payload, string $sigHeader, string $endpointSecret)
    {
        // Pas de vérification de signature en dev
        //return json_decode($payload, false, 512, JSON_THROW_ON_ERROR);
        
        return \Stripe\Webhook::constructEvent(
            $payload, $sigHeader, $endpointSecret
        );
    }
}