<?php
require 'vendor/autoload.php';
require_once 'config.php';

\Stripe\Stripe::setApiKey(STRIPE_SECTET);

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

try {
    // Create a PaymentIntent with the received payment method ID
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => 5000, // Amount in cents
        'currency' => 'usd',
        'payment_method' => $input['payment_method_id'],
        'confirmation_method' => 'manual',
        'confirm' => true,
        'automatic_payment_methods' => [
            'enabled' => 'true',
        ],
    ]);

    // Check if further action is needed (e.g., 3D Secure)
    if ($paymentIntent->status === 'requires_action') {
        echo json_encode([
            'requires_action' => true,
            'payment_intent_client_secret' => $paymentIntent->client_secret,
        ]);
    } else if ($paymentIntent->status === 'succeeded') {
        // Payment succeeded, store in payment_details table
        // Replace with your actual database insertion code
        // Example:
        // $db->query("INSERT INTO payment_details (...) VALUES (...)");
        echo json_encode(['success' => true]);
    } else {
        // Unexpected status
        echo json_encode(['error' => 'Unexpected payment status']);
    }
} catch (\Stripe\Exception\ApiErrorException $e) {
    // Log error in payment_failed table
    // Replace with your actual database insertion code
    // Example:
    // $db->query("INSERT INTO payment_failed (...) VALUES (...)");
    echo json_encode(['error' => $e->getMessage()]);
}
?>
