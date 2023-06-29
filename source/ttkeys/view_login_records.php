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

$id = new MongoDB\BSON\ObjectID($_GET['id']);
$user = $collection->findOne(['_id' => $id]);

function calculateSessionDuration($login, $logout) {
  $interval = $logout->diff($login);

  return $interval->format('%h hours %i minutes');
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <title>Admin Panel - View Login Records</title>
</head>
<body>
  <div class="container my-5">
    <h1 class="text-center">User - <?php echo $user['username'] ?></h1>
    <p>
        <a href="admin_users.php" class="btn btn-primary">Back to User List</a>
        <a href="admin.php" class="btn btn-secondary">Back to Admin Panel</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </p>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Login Time</th>
          <th>Logout Time</th>
          <th>Duration</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($user['login_records'] as $record): ?>
        <tr>
          <td><?php echo date('d-m-Y h:i:s', $record['login']->toDateTime()->getTimestamp()) ?></td>
          <td><?php echo date('d-m-Y h:i:s', $record['logout']->toDateTime()->getTimestamp()) ?></td>
          <td><?php echo calculateSessionDuration($record['login']->toDateTime(), $record['logout']->toDateTime()) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
