<?php
session_start();
require 'vendor/autoload.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ./login.php");
    exit();
}

$client = new MongoDB\Client;
$db = $client->ttkeys;
$collection = $db->users;
$productCollection = $db->products;

if(isset($_GET['delete'])) {
  $delete_id = new MongoDB\BSON\ObjectID($_GET['delete']);
  $user = $collection->findOne(['_id' => $delete_id]);
  $username = $user['username'];
  $result = $collection->deleteOne(['_id' => $delete_id]);

  if($result->getDeletedCount() >= 1) {
    $products = $productCollection->find();
    foreach ($products as $product) {
      if (isset($product['liked_users'])) {
        if (in_array($username, iterator_to_array($product['liked_users']))) {
          $productCollection->updateOne(
              ['_id' => $product['_id']],
              ['$inc' => ['likes' => -1]]
          );
          $productCollection->updateOne(
            ['_id' => $product['_id']],
            ['$pull' => ['liked_users' => $username]]
          );
        }
      }
        
      if (isset($product['disliked_users'])) {
        if (in_array($username, iterator_to_array($product['disliked_users']))) {
          $productCollection->updateOne(
              ['_id' => $product['_id']],
              ['$inc' => ['dislikes' => -1]]
          );
          $productCollection->updateOne(
            ['_id' => $product['_id']],
            ['$pull' => ['disliked_users' => $username]]
          );
        }
      }
      
      $comments = array_filter(iterator_to_array($product['comments']), function ($comment) use ($username) {
          return $comment['username'] !== $username;
      });
      $productCollection->updateOne(
          ['_id' => $product['_id']],
          ['$set' => ['comments' => $comments]]
      );
    }
    header("Location: admin_users.php");
    exit();
  } else {
    echo 'Error deleting user.';
  }
}

$result = $collection->find();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <title>Admin Panel - Users</title>
</head>
<body>
  <div class="container my-5">
    <h1 class="text-center">Admin Panel - Users</h1>
    <p>
        <a href="admin.php" class="btn btn-primary">Back to Admin Panel</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </p>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Email</th>
          <th>Date of Birth</th>
          <th>Username</th>
          <th>Register Date</th>
          <th>Login Records</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($result as $user): ?>
        <tr>
          <td><?php echo $user['firstname'] ?></td>
          <td><?php echo $user['lastname'] ?></td>
          <td><?php echo $user['email'] ?></td>
          <td><?php echo date('d-m-Y', strtotime($user['dateofbirth'])) ?></td>
          <td><?php echo $user['username'] ?></td>
          <td><?php echo date('d-m-Y', $user['register_date']->toDateTime()->getTimestamp()) ?></td>
          <td><a href="view_login_records.php?id=<?php echo $user['_id'] ?>" class="btn btn-primary">View</a></td>
          <td>
            <a href="edit_user.php?id=<?php echo $user['_id'] ?>" class="btn btn-success mr-2">Edit</a>
            <a href="admin_users.php?delete=<?php echo $user['_id'] ?>" class="btn btn-danger">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </body>
</html>