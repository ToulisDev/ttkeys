<?php
session_start();
require 'vendor/autoload.php';

$m = new MongoDB\Client("mongodb://127.0.0.1/");
$db = $m->ttkeys;

if (!isset($_GET['product_id'])) {
    header("Location: index.php");
    exit();
}
if (isset($_SESSION['is_admin'])) {
    unset($_SESSION['is_admin']);
}

$collection = $db->products;

if (isset($_POST['description'])) {
	$products = $db->products;
    $product = $products->findOne(['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])]);
    $comment = [
        'username' => $_SESSION['username'],
        'comment' => $_POST['description'],
    ];
    $products->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])],
        ['$push' => ['comments' => $comment]]
    );
}

if (isset($_POST['wishlist'])) {
	$user_collection = $db->users;
	$user = $user_collection->findOne(['username' => $_SESSION['username']]);
	$product_id = new MongoDB\BSON\ObjectId($_POST['wishlist']);
	if (!isset($user['favourites'])) {
        $user_collection->updateOne(
			['_id' => new MongoDB\BSON\ObjectId($user['_id'])],
			['$set' => ['favourites' => []]]
		);
    }
	$user = $user_collection->findOne(['username' => $_SESSION['username']]);
	if (!in_array($product_id, iterator_to_array($user['favourites']))) {
		$user_collection->updateOne(
            ['_id' => $user['_id']],
            ['$push' => ['favourites' => $product_id]]
        );
	} elseif (in_array($product_id, iterator_to_array($user['favourites']))) {
		$user_collection->updateOne(
            ['_id' => $user['_id']],
            ['$pull' => ['favourites' => $product_id]]
        );
	}
}

if (isset($_POST['like'])) {
    $products = $db->products;
    $product = $products->findOne(['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])]);
	if (!isset($product['liked_users'])) {
        $products->updateOne(
			['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])],
			['$set' => ['liked_users' => []]]
		);
    }
	if (!isset($product['disliked_users'])) {
        $products->updateOne(
			['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])],
			['$set' => ['disliked_users' => []]]
		);
    }
	$product = $products->findOne(['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])]);
    if (!in_array($_SESSION['username'], iterator_to_array($product['liked_users']))
        && !in_array($_SESSION['username'], iterator_to_array($product['disliked_users']))) {
        $products->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])],
            ['$inc' => ['likes' => 1]]
        );
		$products->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])],
            ['$push' => ['liked_users' => $_SESSION['username']]]
        );
    } elseif (in_array($_SESSION['username'], iterator_to_array($product['disliked_users']))) {
        $products->updateOne(
			['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])],
			['$pull' => ['disliked_users' => $_SESSION['username']]]
		);
		$products->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])],
            ['$push' => ['liked_users' => $_SESSION['username']]]
        );
        $products->updateOne(
			['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])],
			['$inc' => ['likes' => 1, 'dislikes' => -1]]
		);
    }
}

if (isset($_POST['dislike'])) {
    $products = $db->products;
    $product = $products->findOne(['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])]);
	if (!isset($product['liked_users'])) {
        $products->updateOne(
			['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])],
			['$set' => ['liked_users' => []]]
		);
    }
	if (!isset($product['disliked_users'])) {
        $products->updateOne(
			['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])],
			['$set' => ['disliked_users' => []]]
		);
    }
	$product = $products->findOne(['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])]);
    if (!in_array($_SESSION['username'], iterator_to_array($product['liked_users']))
        && !in_array($_SESSION['username'], iterator_to_array($product['disliked_users']))) {
        $products->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])],
            ['$inc' => ['dislikes' => 1]]
        );
		$products->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])],
            ['$push' => ['disliked_users' => $_SESSION['username']]]
        );
    } elseif (in_array($_SESSION['username'], iterator_to_array($product['liked_users']))) {
        $products->updateOne(
			['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])],
			['$pull' => ['liked_users' => $_SESSION['username']]]
		);
		$products->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])],
            ['$push' => ['disliked_users' => $_SESSION['username']]]
        );
        $products->updateOne(
			['_id' => new MongoDB\BSON\ObjectId($_GET['product_id'])],
			['$inc' => ['dislikes' => 1, 'likes' => -1]]
		);
    }
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
		<div class="body-wrapper">
			<div id="main-wrapper" class="fancy-scrollbar">
				<div class="nav">
					<div class="nav-info-wrapper">
						<a class="nav-brand" href="./index.php">
							<i class="fa-regular fa-key"></i>
							<h3>TT.KEYS</h3>
						</a>
						<form action="search.php" method="get" id="search">
							<input name="search_query" class="search-input" type="search" placeholder="Search" />
							<button class="search-button" type="submit">
								<i class="fa-solid fa-magnifying-glass"></i>
							</button>
						</form>
						<button onclick="cartPage()" class="nav-cart" type="button">
							<i id="cart-icon" class="fa-solid fa-cart-shopping nav-cart-items" value=<?php 
							if (isset($_SESSION['cart_amount'])) {
								echo $_SESSION['cart_amount'];
							}
							else {
								echo '0';
							}
							?>></i>
						</button>
						<div class="nav-largedropdown">
						<?php
						if (isset($_SESSION['username']))
						{
							echo "<button class=\"nav-profile\"	onclick=\"toggleNavMenu('nav-fullmenu')\" type=\"button\">" . $_SESSION['username'] . "<i class=\"fa-solid fa-user\"></i></button>";

						}
						else
						{
							echo "<button class=\"nav-profile\" 
							onclick=\"window.open('./authenticate.php', '_self')\" type=\"button\"> Login <i class=\"fa-solid fa-user\"></i>
							</button>";
						}
						?>
							<div class="nav-largedropdown-menu" id="nav-fullmenu">
								<?php
								if (isset($_SESSION['username']))
								{
									echo "<a href=\"./myprofile.php\">My Profile</a>";
									echo "<a href=\"./myfavourite.php\">My Favourite</a>";
									echo "<a href=\"./myorders.php\">My Orders</a>";
									echo "<a href=\"./logout.php\">Sign Out</a>";
								}
								?>
							</div>
						</div>
						<div class="nav-dropdown">
							<button class="nav-menu-toggle" onclick="toggleNavMenu('nav-menu')" type="button">
								<i class="fa-light fa-bars-sort"></i>
							</button>
							<ul class="nav-dropdown-menu" id="nav-menu">
								<?php
								if (isset($_SESSION['username']))
								{
									echo "<li><a href=\"./myprofile.php\">My Profile</a></li>";
									echo "<li><a href=\"./myfavourite.php\">My Favourite</a></li>";
									echo "<li><a href=\"./myorders.php\">My Orders</a></li>";
									echo "<li><a href=\"./logout.php\">Sign Out</a></li>";
								}
								else {
									echo "<li><a href=\"./authenticate.php\">Login</a></li>";
								}
								?>
							</ul>
						</div>
					</div>
				</div>
				<div id="content">
                    <div id="featured-slider">
                        <div class="featured-slider-items">
							<?php
                                $product_id = new MongoDB\BSON\ObjectId($_GET['product_id']);
								$product = $collection->findOne(['_id' => $product_id]);
                                $grid = $db->selectGridFSBucket();
                                $image = $grid->findOne(['_id' => $product['image_id']]);
                                if ($image) {
                                    $downloadStream = $grid->openDownloadStream($product['image_id']);
                                    $image_data = stream_get_contents($downloadStream);
                                    $image_url = 'data:image/jpg;base64,' . base64_encode($image_data);
                                }
                                    echo '<a class="featured-slider-item active" style="cursor: default; background-image: url(\'' . $image_url . '\');" >';
                                    echo '<div class="featured-slider-item-info-wrapper">';
                                    echo '<div class="featured-slider-item-info no-description">';
                                    echo '<h2 class="title">' . $product['title'] . '</h2>';
                                    echo '<h3 class="price highlight">€' . $product['price'] . '</h3>';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</a>';
							?>
						</div>
					</div>
                    <div class="product-info-wrapper">
                        <div class="product-info">
                            <h3>Release Date: 
                            <?php echo date('m-d-Y', $product['release_date']->toDateTime()->getTimestamp()) ?>
                            <?php
                            $nowDate = new DateTime();
                            $releaseDate = $product['release_date']->toDateTime();
                            if ($nowDate < $releaseDate): ?>
                                <span class="highlight">(Upcoming)</span>
                            <?php endif; ?>
                            </h3>
                            <h3>Description:</h3>
                            <p><?php echo $product['description'] ?></p>
                        </div>
                        <div class="product-button-wrapper">
							<?php if (isset($_SESSION['username'])) { ?>
								<?php 
								$user_collection = $db->users;
								$user = $user_collection->findOne(['username' => $_SESSION['username']]);
								if (isset($user['favourites'])) { 
									if (in_array($product_id, iterator_to_array($user['favourites']))) { ?>
										<button class="product-button active hit" style="margin-right: 1rem;" onclick="wishlistProduct('<?php echo $product_id ?>')" type="submit"><i class="fa-solid fa-heart"></i></button>
									<?php } else { ?>
										<button class="product-button active" style="margin-right: 1rem;" onclick="wishlistProduct('<?php echo $product_id ?>')" type="submit"><i class="fa-solid fa-heart"></i></button>
									<?php } ?>
								<?php } else { ?>
									<button class="product-button active" style="margin-right: 1rem;" onclick="wishlistProduct('<?php echo $product_id ?>')" type="submit"><i class="fa-solid fa-heart"></i></button>
								<?php } ?>
							<?php } ?>
                            <?php
                            $checkoutBtnStr = '';
                            $addBtnStr = ' active';
                            if (isset($_SESSION['cart_products'])) { 
                                if (in_array($product_id, $_SESSION['cart_products'])) {
                                    $checkoutBtnStr = ' active';
                                    $addBtnStr = '';
                                }
                            }
                            ?> 
							<?php
								$bShowComment = 0;
								if (isset($_SESSION['username'])) {
									$order_collection = $db->orders;
									$user_collection = $db->users;
									$user = $user_collection->findOne(['username' => $_SESSION['username']]);
									if ($user) {
										if (isset($user['owned_products'])) {
											$owned_products = iterator_to_array($user['owned_products']);
											if (in_array($product_id, $owned_products)) {
												$bShowComment = 1;
											}
										}
									}
								}
							?>
							<?php if ($bShowComment !== 0) { ?>
									<?php if (!isset($product['liked_users'])) { ?>
										<button class="product-button active hit" style="margin-right: 1rem;" onclick="likeProduct('<?php echo $product_id ?>')" type="submit"><i class="fa-solid fa-thumbs-up"></i></button>
									<?php } else { ?>
										<button class="product-button active<?php if (in_array($_SESSION['username'], iterator_to_array($product['liked_users']))) { echo ' hit'; } ?>" style="margin-right: 1rem;" onclick="likeProduct('<?php echo $product_id ?>')" type="submit"><i class="fa-solid fa-thumbs-up"></i></button>
									<?php } ?>
									<?php if (!isset($product['disliked_users'])) { ?>
										<button class="product-button active hit" style="margin-right: 1rem;" onclick="dislikeProduct('<?php echo $product_id ?>')" type="submit"><i class="fa-solid fa-thumbs-down"></i></button>
									<?php } else { ?>
										<button class="product-button active<?php if (in_array($_SESSION['username'], iterator_to_array($product['disliked_users']))) { echo ' hit'; } ?>" style="margin-right: 1rem;" onclick="dislikeProduct('<?php echo $product_id ?>')" type="submit"><i class="fa-solid fa-thumbs-down"></i></button>
									<?php } ?>
								<a class="product-button active" style="text-decoration: none;" href="#comment-section">Add/Check Comments</a>
							<?php } else { ?>
								<button id="btn-checkout" class="product-button<?php echo $checkoutBtnStr; ?>" onclick="cartPage()" type="button">Checkout</button>
								<button id="btn-add-cart" class="product-button<?php echo $addBtnStr; ?>" onclick="addToCart('<?php echo $product_id ?>')" type="button">Add to Cart</button>
							<?php } ?>
                        </div>
                    </div>
                    <hr style="width: 25rem;">
                    <?php if ($bShowComment !== 0): ?>
					<div class="comment-wrapper">
						<h3 for="description">Description:</h3>
						<form action="product.php?product_id=<?php echo $_GET['product_id'] ?>" method="post" >
							<textarea title="Comment" placeholder="Comment here..." name="description" maxlength="400" required></textarea>
							<button class="product-button active" type="submit">Add Comment</button>
						</form>
					</div>
                    <?php endif;?>
					<div id="comment-section" class="comment-section">
						<h2>Comments:</h3>
						<div class="comments-wrapper">
						<?php
							foreach ($product['comments'] as $comment) {
								echo '<div class="comment">';
								echo '<div class="comment-info">
									<div class="username-wrapper">';
								echo '<span class="username">' . $comment['username'] . '</span>';
								echo '</div>
									<div class="description-wrapper">';
								echo '<span class="description">' . $comment['comment'] . '</span>';
								echo '</div>
									</div>
									</div>';
							}?>
						</div>
					</div>
				</div>
				<div class="footer">
          			<div class="container">
            			<div class="row">
              				<div class="footer-col">
                				<h4>COMPANY</h4>
                				<p>Our team of experts works tirelessly 
                  				to ensure that our inventory is constantly 
                  				updated with the newest and most in-demand games.</p>
                				<ul>
									<li>Address: <a href="https://www.google.com/maps/place/%CE%A0%CE%B1%CE%BD%CE%B5%CF%80%CE%B9%CF%83%CF%84%CE%AE%CE%BC%CE%B9%CE%BF+%CE%A0%CE%B5%CE%B9%CF%81%CE%B1%CE%B9%CF%8E%CF%82/@37.9415137,23.6528681,15z/data=!4m2!3m1!1s0x0:0x3e0dce8e58812705?sa=X&ved=2ahUKEwiYmYyXyPL8AhVgRPEDHbsuBVIQ_BJ6BAhhEAg">Καραολή και Δημητρίου 80</a></li>
									<li>Telephone: <a href="tel:+302104142000">210 4142000</a></li>
									<li>E-Mail: <a href="mailto:aggelos_sachtouris@hotmail.com">aggelos_sachtouris@hotmail.com</a></li>
                  					<li>For More Information: <a href="./aboutus.php">Click Here!</a></li>
                				</ul>
							</div>
						</div>
					</div>
              	</div>
            </div>
        </div>
		<script src="./sources/sript.js"></script>
	</body>
</html>