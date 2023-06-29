<?php
session_start();
require 'vendor/autoload.php';
$m = new MongoDB\Client("mongodb://127.0.0.1/");
$db = $m->ttkeys;

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['email'])) {
    $productsCol = $db->products;
    $usersCol = $db->users;
    $ordersCol = $db->orders;

    $user = $usersCol->findOne(['username' => $_SESSION['username']]);
    $user_id = $user['_id'];

    $product_ids = array();
    foreach ($_SESSION['cart_products'] as $str_product_id) {
        if (!isset($str_product_id)) {
            continue;
        }
        $product_id = new MongoDB\BSON\ObjectId($str_product_id);
        array_push($product_ids, $product_id);
        $usersCol->updateOne(
            ['_id' => $user_id],
            ['$push' => ['owned_products' => $product_id]]
        );
    }

    $order = array(
        'user_id' => $user_id,
        'products' => $product_ids,
        'total_price' => $_SESSION['cart_total_price'],
        'date' => new MongoDB\BSON\UTCDateTime(),
    );
    $result = $ordersCol->insertOne($order);
    if ($result->getInsertedCount() == 1) {
        unset($_SESSION['cart_products']);
        unset($_SESSION['cart_amount']);
        header("Location: rateus.php");
        exit();
    } else {
        header("Location: checkout.php");
        exit();
    }
}

if (!isset($_SESSION['cart_amount'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['cart_amount'] < 1) {
    header("Location: index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<link rel="stylesheet" href="./sources/fontawesome/css/all.css">
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
		<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&amp;family=Rubik:wght@400;500;700&amp;display=swap" rel="stylesheet">
		<title>TTKeys</title>
		<meta charset="UTF-8">
	</head>
    <body>
        <div class="checkout">
            <form action="" method="post">
                <div class="checkout-row">
                    <div class="checkout-col">
                        <h3 class="checkout-title">Billing Address</h3>
                        <div class="checkout-inputBox">
                            <span>Full Name :</span>
                            <input type="text" placeholder="FirstName LastName" required>
                        </div>
                        <div class="checkout-inputBox">
                            <span>email :</span>
                            <input type="email" name="email" placeholder="example@example.com" required>
                        </div>
                        <div class="checkout-inputBox">
                            <span>Address :</span>
                            <input type="text" placeholder="Address" required>
                        </div>
                        <div class="checkout-inputBox">
                            <span>City :</span>
                            <input type="text" placeholder="City" required>
                        </div>
                        <div class="checkout-flex">
                            <div class="checkout-inputBox">
                                <span>State :</span>
                                <input type="text" placeholder="Country/State" required>
                            </div>
                            <div class="checkout-inputBox">
                                <span>Zip Code :</span>
                                <input type="text" placeholder="Zip" required>
                            </div>
                        </div>
                    </div>
                    <div class="checkout-col">
                        <h3 class="checkout-title">Payment</h3>
                        <div class="checkout-inputBox">
                            <span>Cards Accepted :</span>
                            <i class="fa-brands fa-cc-visa"></i>
                            <i class="fa-brands fa-cc-mastercard"></i>
                        </div>
                        <div class="checkout-inputBox">
                            <span>Fullname :</span>
                            <input autocomplete="off" type="text" placeholder="Name on Card" required>
                        </div>
                        <div class="checkout-inputBox">
                            <span>Credit Card Number :</span>
                            <input autocomplete="off" type="text" placeholder="1111-2222-3333-4444" required>
                        </div>
                        <div class="checkout-flex">
                            <span>Expiry Date :</span>
                            <div class="checkout-inputBox">
                                <input autocomplete="off" type="number" placeholder="MM" required>
                            </div>
                            <div class="checkout-inputBox">
                                <input autocomplete="off" type="number" placeholder="YYYY" required>
                            </div>
                        </div>
                        <div class="checkout-inputBox">
                            <span>CVV :</span>
                            <input autocomplete="off" type="number" min=0 placeholder="123" required>
                        </div>
                        <div class="checkout-inputBox">
                            <span>Total Amount: <?php echo $_SESSION['cart_total_price']; ?>€</span>
                        </div>
                    </div>
                </div>
                <input type="submit" value="Proceed to Checkout" class="submit-btn">
            </form>
        </div>    
    </body>
</html>