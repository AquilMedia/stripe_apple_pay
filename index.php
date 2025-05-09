<?php
require_once 'config.php';
?>
<!-- Include Stripe.js -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        html,
        body,
        button {
            width: 100%;
        }
    </style>
</head>

<body>

    <div id="payment-request-button">Loading...</div>

    <script>
        const STRIPE_PUBLISH = "<?php echo STRIPE_PUBLISH; ?>";
        console.log(`STRIPE_PUBLISH ${STRIPE_PUBLISH}`)
        const stripe = Stripe(STRIPE_PUBLISH);

        const paymentRequest = stripe.paymentRequest({
            country: 'US',
            currency: 'usd',
            total: {
                label: 'Total',
                amount: 5000, // Amount in cents ($50.00)
            },
            requestPayerName: true,
            requestPayerEmail: true,
        });

        //   const elements = stripe.elements();
        const options = {
            mode: 'payment',
            amount: 1099,
            currency: 'usd',
        };
        const elements = stripe.elements(options);
        const prButton = elements.create('paymentRequestButton', {
            paymentRequest: paymentRequest,
        });

        // Check if the Payment Request is available (Apple Pay)
        paymentRequest.canMakePayment().then(function(result) {
            if (result) {
                prButton.mount('#payment-request-button');
            } else {
                document.getElementById('payment-request-button').style.display = 'none';
            }
        });

        paymentRequest.on('paymentmethod', function(ev) {
            fetch('/process_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        payment_method_id: ev.paymentMethod.id
                    }),
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(responseJson) {
                    if (responseJson.error) {
                        ev.complete('fail');
                        // Optionally, log the error to your payment_failed table
                    } else {
                        ev.complete('success');
                        // Optionally, redirect to a success page
                    }
                });
        });
    </script>

</body>

</html>
