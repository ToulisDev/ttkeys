<?php
session_start();
require 'vendor/autoload.php';
$m = new MongoDB\Client("mongodb://127.0.0.1/");
$db = $m->ttkeys;

if (!isset($_SESSION['username'])) {
    header("Location: authenticate.php");
    exit();
}

$user_collection = $db->users;
$user = $user_collection->findOne(['username' => $_SESSION['username']]);

if (isset($_POST["submit"])) {
    $password = $_POST["currentpassword"];
    $newPassword = $_POST['password'];
    if (password_verify($password, $user["password"])) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $user_collection->updateOne(
            ['_id' => $user['_id']],
            ['$set' => [
            'firstname' => $_POST['firstname'],
            'lastname' => $_POST['lastname'],
            'dateofbirth' => $_POST['dateofbirth'],
            'password' => $hashedPassword,
        ]]
    );
        header("Location: ./myprofile.php");
        exit();
    } else {
        $error = "Incorrect Password";
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
                    <section class="authenticate-wrapper">
                        <div class="profile"> 
                            <div class="profile-form">
                                <h2>Personal Info</h2>
                            <form method="post">
                                <div class="authenticate-input-box">
                                    <input type="text" name="firstname" value="<?php echo $user['firstname']; ?>" required="required">
                                    <span>First Name</span>
                                    <i></i>
                                </div>
                                <div class="authenticate-input-box">
                                    <input type="text" name="lastname" value="<?php echo $user['lastname']; ?>" required="required">
                                    <span>Last Name</span>
                                    <i></i>
                                </div>
                                <div class="authenticate-input-box">
                                    <input type="date" name="dateofbirth" value="<?php echo $user['dateofbirth']; ?>" required="required">
                                    <span>Date Of Birth</span>
                                    <i></i>
                                </div>
                                <div class="authenticate-input-box">
                                    <input type="password" name="currentpassword" required="required">
                                    <span>Current Password</span>
                                    <i></i>
                                </div>
                                <div class="authenticate-input-box">
                                    <input type="password" name="password" required="required">
                                    <span>Change Password</span>
                                    <i></i>
                                </div>
                                <div class="authenticate-links">
                                    <a href=""></a>
                                </div>
                                <button type="submit" name="submit">Update</button>
                            </form>
                            <?php
                                if (isset($error))
                                {
                                    echo "<p class=\"authenticate-error\">Error: $error</p>";
                                }
                            ?>
                            </div>
                        </div>
                    </section>
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