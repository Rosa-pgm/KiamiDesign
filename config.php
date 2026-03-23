<?php
require_once 'vendor/autoload.php';

// Estas chaves dizem à Stripe sou (o vendedor)
$stripeSecretKey = 'sua_password_Stripe';
$stripePublishableKey = 'sua_password_stripe';

\Stripe\Stripe::setApiKey($stripeSecretKey);


?>
