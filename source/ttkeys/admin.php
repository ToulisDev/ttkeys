<?php
session_start();
require 'vendor/autoload.php';
if (isset($_SESSION["username"])) {
    header("Location: ./logout.php");
    exit();
}

if(isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if($username === "admin" && $password === "123") {
        $_SESSION['is_admin'] = true;
    }
    else 
    {
        $error = "Wrong Credentials!";
    }
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <h2 class="text-center">Login</h2>
            <?php if(isset($error)): ?>
            <p class="text-danger text-center">
                <?= $error ?>
            </p>
            <?php endif; ?>
            <form action="" method="post" class="form-group">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <input type="submit" value="Submit" class="btn btn-primary btn-block">
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <title>Admin Panel</title>
</head>
<body>
  <div class="container my-5">
    <h1 class="text-center">Admin Panel</h1>
    <div class="d-flex justify-content-between align-items-center my-3">
      <a href="admin_users.php" class="btn btn-primary">Users</a>
      <a href="admin_products.php" class="btn btn-primary">Products</a>
      <a href="admin_orders.php" class="btn btn-primary">Orders</a>
      <a href="admin_ratings.php" class="btn btn-primary">Ratings</a>
      <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
  </div>
</body>
</html>