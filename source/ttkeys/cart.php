<?php
session_start();
require 'vendor/autoload.php';
$m = new MongoDB\Client("mongodb://127.0.0.1/");
$db = $m->ttkeys;

if (!isset($_SESSION['cart_products'])) {
    $_SESSION['cart_products'] = array();
}

if (!isset($_SESSION['cart_amount'])) {
    $_SESSION['cart_amount'] = 0;
}

if (isset($_POST['del_product_id'])) {
    if (in_array($_POST['del_product_id'], $_SESSION['cart_products'])) {
        $_SESSION['cart_amount'] = $_SESSION['cart_amount'] - 1;
        $key = array_search($_POST['del_product_id'], $_SESSION['cart_products']);
        unset($_SESSION['cart_products'][$key]);
    }
}

if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
    if (!in_array($_POST['product_id'], $_SESSION['cart_products'])) {
        array_push($_SESSION['cart_products'], $_POST['product_id']);
        if (isset($_SESSION['cart_amount'])) {
            $_SESSION['cart_amount'] = $_SESSION['cart_amount'] + 1;
        } else {
            $_SESSION['cart_amount'] = 1;
        }
        echo 'Successfuly added Item';
    } else {
        echo 'Item Already Exists';
    }
    exit();
}

if (!isset($_SESSION['username'])) {
    header("Location: authenticate.php");
    exit();
}

$order_collection = $db->orders;
$user_collection = $db->users;
$user = $user_collection->findOne(['username' => $_SESSION['username']]);
if ($user && isset($user['owned_products'])) {
	$owned_products = iterator_to_array($user['owned_products']);
	foreach ($_SESSION['cart_products'] as $alreadyBoughtItem) {
		if (in_array($alreadyBoughtItem, $owned_products)) {
			$key = array_search($alreadyBoughtItem, $_SESSION['cart_products']);
			unset($_SESSION['cart_products'][$key]);
			$_SESSION['cart_amount'] = $_SESSION['cart_amount'] - 1;
		}
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
				<div id="content" class="search-results">
					<form action="search.php" method="get" id="search">
						<input name="search_query" class="search-input" type="search" placeholder="Search" />
						<button class="search-button" type="submit">
							<i class="fa-solid fa-magnifying-glass"></i>
						</button>
                    </form>
                    <h2 class="search-title">Your Cart has <?php echo $_SESSION['cart_amount'] . " Item(s):"; ?></h2>
                    <div id="list" class="" data-active-index="0">
                        <div class="list-items">
                        <?php
                            $totalPrice = 0;
                            foreach ($_SESSION['cart_products'] as $product_id) {
                                if (!isset($product_id)) {
                                    continue;
                                }
                                $mngproduct_id = new MongoDB\BSON\ObjectId($product_id);
                                $collection = $db->products;
                                $product = $collection->findOne(['_id' => $mngproduct_id]);
                                $totalPrice = $totalPrice + $product['price'];
                                $grid = $db->selectGridFSBucket();
                                $image = $grid->findOne(['_id' => $product['image_id']]);
                                if ($image) {
                                    $downloadStream = $grid->openDownloadStream($product['image_id']);
                                    $image_data = stream_get_contents($downloadStream);
                                    $image_url = 'data:image/jpg;base64,' . base64_encode($image_data);
                                }
                                echo '<div class="list-item" style="background-color: transparent !important; cursor: default;">';
                                echo '<img class="list-item-image" src="' . $image_url . '" alt="' . $product['title'] .'" />';
                                echo '<div class="list-item-info">
                                    <div class="title-wrapper section">';
                                echo '<span class="title">' . $product['title'] . '</span>';
                                echo '</div>
                                    <div class="numbers section">';
                                echo '<span class="price">€' . $product['price'] . '</span>';
                                echo '</div>
                                    </div>
                                    <div class="delete-cart-item-wrapper">
                                        <button onclick="deleteFromCart(\'' . $product_id . '\')" class="delete-cart-item">
                                            <i class="fa-solid fa-trash-xmark"></i>
                                        </button>
                                    </div>
                                </div>';
                            }?>
                            <?php if($totalPrice > 0) {
                                $_SESSION['cart_total_price'] = $totalPrice;
                            } else {
                                $_SESSION['cart_total_price'] = 0;
                            } ?>
                        </div>
                    </div>
                    <div class="cart-total">
                        <h2>Total Price:</h3>
                        <?php if ($_SESSION['cart_amount'] > 0) { ?>
                            <h3>€ <?php echo $totalPrice; ?></h3>
                            <button class="product-button active" onclick="checkoutPage()" type="button">Buy Now</button>
                        <?php } else { ?>
                            <h3>€ -.--</h3>
                        <?php } ?>
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