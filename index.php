<?php 
required_one('config.php');
?>
<!-- Include Stripe.js -->
 <!DOCTYPE html>
 <html lang="en">
 <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://js.stripe.com/v3/"></script>
 </head>
 <body>

<button id="payment-request-button">Loading...</button>

<script>
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

  const elements = stripe.elements();
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
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ payment_method_id: ev.paymentMethod.id }),
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