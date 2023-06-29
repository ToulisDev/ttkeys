<?php
session_start();
require 'vendor/autoload.php';
$m = new MongoDB\Client("mongodb://127.0.0.1/");
$db = $m->ttkeys;

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$usersCollection = $db->users;
$ratesCollection = $db->rates;

if(isset($_POST['rate'])) {
    $user = $usersCollection->findOne(['username' => $_SESSION['username']]);
    $ratesCollection->insertOne([
        'user_id' => $user['_id'],
        'rate' => intval($_POST['rate'])
    ]);
    $usersCollection->updateOne(
        ['username' => $_SESSION['username']],
        ['$set' => ['hasRated' => true]]
    );
    header("Location: index.php");
    exit();
}

$user = $usersCollection->findOne(['username' => $_SESSION['username']]);

if (isset($user['hasRated'])) {
    header("Location: index.php");
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Rate Us</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="./sources/fontawesome/css/all.css">
    <style>
        .rate-star {
            font-size: 2em;
            color: #b0b0b0;
            cursor: pointer;
        }
        .rate-star.selected {
            color: #ffc107;
        }
    </style>
</head>
<body>
<div class="container mt-5" style="
    display: flex;
    flex-direction: column;
    align-items: center;
">
<h1 class="text-center" style="margin-bottom: 2rem;">Rate Us</h1>
<form action="" method="post">
<div class="form-group">
    <label for="rate">Please rate us:</label>
    <div class="d-flex">
        <i class="fas fa-star rate-star" id="rate-star-1"></i>
        <i class="fas fa-star rate-star" id="rate-star-2"></i>
        <i class="fas fa-star rate-star" id="rate-star-3"></i>
        <i class="fas fa-star rate-star" id="rate-star-4"></i>
        <i class="fas fa-star rate-star" id="rate-star-5"></i>
    </div>
    <input type="hidden" name="rate" id="rate" value="">
</div>
<button type="submit" class="btn btn-primary">Submit</button>
</form>
</div>
<script>
    const rateStars = document.querySelectorAll('.rate-star');
    rateStars.forEach((star, index) => {
        star.addEventListener('click', function() {
            rateStars.forEach(star => {
                star.classList.remove('selected');
            });
            for (let i = 0; i <= index; i++) {
                rateStars[i].classList.add('selected');
            }
            document.getElementById('rate').value = index + 1;
        });
    });
</script>
</body>
</html>