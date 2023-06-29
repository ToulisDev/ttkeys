<?php
    session_start();
    require 'vendor/autoload.php';
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header("Location: ./index.php");
        exit();
    }
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $db = $client->ttkeys;
    $ratesCollection = $db->rates;
    $rates = $ratesCollection->find();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <title>Admin Panel - Ratings</title>
</head>
<body>
  <div class="container my-5">
    <h1 class="text-center">Admin Panel - Ratings</h1>
    <p>
        <a href="admin.php" class="btn btn-primary">Back to Admin Panel</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </p>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>User ID</th>
          <th>Rating</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rates as $rate): ?>
        <tr>
          <td><?= $rate['user_id'] ?></td>
          <td><?= $rate['rate'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>