<?php
session_start();
require 'vendor/autoload.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ./login.php");
    exit();
}

$client = new MongoDB\Client("mongodb://localhost:27017");
$db = $client->ttkeys;
$ordersCollection = $db->orders;
$usersCollection = $db->users;

if (!isset($_GET['_id'])) {
    header("Location: ./admin_orders.php");
    exit();
}

$orderId = new MongoDB\BSON\ObjectId($_GET['_id']);
$order = $ordersCollection->findOne(['_id' => $orderId]);
$user = $usersCollection->findOne(['_id' => $order['user_id']]);
$productsCollection = $db->products;

$productTitles = [];
foreach ($order['products'] as $productId) {
    $product = $productsCollection->findOne(['_id' => $productId]);
    $productTitles[(string)$productId] = $product['title'];
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <title>Admin Panel - View Order</title>
</head>
<body>
  <div class="container my-5">
    <h1 class="text-center">Admin Panel - View Order</h1>
    <p>
        <a href="admin_orders.php" class="btn btn-primary">Back to Orders</a>
        <a href="admin.php" class="btn btn-secondary">Back to Admin Panel</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </p>
    <table class="table table-bordered">
      <tbody>
        <tr>
          <td>Order ID</td>
          <td><?php echo $order['_id']; ?></td>
        </tr>
        <tr>
          <td>Order Date</td>
          <td><?php echo date('d-m-Y h:i:s', $order['date']->toDateTime()->getTimestamp()); ?></td>
        </tr>
        <tr>
          <td>Username</td>
          <td><?php echo $user['username']; ?></td>
        </tr>
        <tr>
          <td>Products</td>
          <td>
            <ul>
                <?php foreach ($productTitles as $productId => $productTitle): ?>
                    <li><?php echo "$productId - $productTitle"; ?></li>
                <?php endforeach; ?>
            </ul>
          </td>
        </tr>
        <tr>
          <td>Total Price</td>
          <td><?php echo $order['total_price']; ?></td>
        </tr>
      </tbody>
    </table>
  </div>
</body>
</html>