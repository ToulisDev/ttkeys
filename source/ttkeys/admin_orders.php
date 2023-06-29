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

if (isset($_GET['delete_id'])) {
  $orderId = new MongoDB\BSON\ObjectId($_GET['delete_id']);
  $order = $ordersCollection->findOne(['_id' => $orderId]);
  $deleteResult = $ordersCollection->deleteOne(['_id' => $orderId]);

  if ($deleteResult->getDeletedCount() >= 1) {
    foreach (iterator_to_array($order['products']) as $product) {
      $usersCollection->updateOne(
        ['_id' => $order['user_id']],
        ['$pull' => ['owned_products' => $product]]
      );
    }
    header("Location: ./admin_orders.php");
    exit();
  } else {
    echo 'Error deleting order.';
  }
}

$orders = $ordersCollection->find();

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <title>Admin Panel - Orders</title>
</head>
<body>
  <div class="container my-5">
    <h1 class="text-center">Admin Panel - Orders</h1>
    <p>
        <a href="admin.php" class="btn btn-primary">Back to Admin Panel</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </p>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>User ID</th>
          <th>Products</th>
          <th>Total Price</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($orders as $order): ?>
        <tr>
          <td><?php echo $order['_id']; ?></td>
          <td><?php echo $order['user_id']; ?></td>
          <td>
            <?php
            $productIds = implode(', ', iterator_to_array($order['products']));
            echo $productIds;
            ?>
          </td>
          <td><?php echo $order['total_price']; ?></td>
          <td><?php echo date('d-m-Y', $order['date']->toDateTime()->getTimestamp()); ?></td>
          <td>
            <a href="view_order.php?_id=<?php echo $order['_id']; ?>" class="btn btn-info">View</a>
            <a href="admin_orders.php?delete_id=<?php echo $order['_id']; ?>" class="btn btn-danger">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</body>
</html>
