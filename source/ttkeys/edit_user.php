<?php
session_start();
require 'vendor/autoload.php';

// Check if the user is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ./index.php');
    exit();
}

$m = new MongoDB\Client("mongodb://127.0.0.1/");
$db = $m->ttkeys;
$collection = $db->users;

// Get the user ID from the URL
$id = new MongoDB\BSON\ObjectId($_GET['id']);
$user = $collection->findOne(['_id' => $id]);

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update the user information
    $updateResult = $collection->updateOne(
        ['_id' => $id],
        ['$set' => [
            'firstname' => $_POST['firstname'],
            'lastname' => $_POST['lastname'],
            'email' => $_POST['email'],
            'dateofbirth' => $_POST['dateofbirth'],
            'username' => $_POST['username'],
        ]]
    );

    // Redirect to the admin page
    header('Location: ./admin.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-3">Edit User</h1>
        <p class="d-flex justify-content-between mb-3">
            <a href="admin.php" class="btn btn-secondary">Back to Admin Panel</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </p>
        <form action="" method="post">
            <div class="form-group">
                <label for="firstname">First Name:</label>
                <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo $user['firstname']; ?>">
            </div>
            <div class="form-group">
                <label for="lastname">Last Name:</label>
                <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo $user['lastname']; ?>">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>">
            </div>
            <div class="form-group">
                <label for="dateofbirth">Date of Birth:</label>
                <input type="date" class="form-control" id="dateofbirth" name="dateofbirth" value="<?php echo $user['dateofbirth']; ?>">
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>">
            </div>
            <input type="submit" class="btn btn-primary" value="Update User">
        </form>
    </div>
</body>
</html>
