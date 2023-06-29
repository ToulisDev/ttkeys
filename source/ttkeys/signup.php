<?php
    session_start();
    require 'vendor/autoload.php';
    if (isset($_SESSION["username"]) ) {
        header("Location: ./index.php");
        exit();
    }
    $m = new MongoDB\Client("mongodb://127.0.0.1/");

    $db = $m->ttkeys;
    $collection = $db->users;

    if(isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['email']) && isset($_POST['dateofbirth']) && isset($_POST['username']) && isset($_POST['password'])) {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $dateofbirth = $_POST['dateofbirth'];
        $username = $_POST['username'];
        $password = $_POST['password'];

        $user = $collection->findOne(['username' => $username]);
        if (empty($user))
        {
            $user = $collection->findOne(['email' => $email]);
        }

        if(empty($user)) {
            $register_date = new MongoDB\BSON\UTCDateTime();
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $user = [
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'dateofbirth' => $dateofbirth,
                'username' => $username,
                'password' => $password_hash,
                'register_date' => $register_date
            ];

        $collection->insertOne($user);

        header("Location: ./authenticate.php");
        exit();
        } else {
            $error = "User already exists.";
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
						<div id="search">
						</div>
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
						<button class="nav-profile" onclick="window.open('./authenticate.php', '_self')" type="button"> Login <i class="fa-solid fa-user"></i>
						</button>
						<div class="nav-dropdown">
							<button class="nav-menu-toggle" onclick="toggleNavMenu('nav-menu')" type="button">
								<i class="fa-light fa-bars-sort"></i>
							</button>
							<ul class="nav-dropdown-menu" id="nav-menu">
								<li><a href="./authenticate.php">Login</a></li>
							</ul>
						</div>
					</div>
				</div>
				<div id="content">
                    <section class="authenticate-wrapper">
                        <div class="authenticate" style="height: 850px;"> 
                            <div class="authenticate-form">
                                <h2>Register</h2>
                            <form method="post">
                                <div class="authenticate-input-box">
                                    <input type="text" name="firstname" required="required">
                                    <span>First Name</span>
                                    <i></i>
                                </div>
                                <div class="authenticate-input-box">
                                    <input type="text" name="lastname" required="required">
                                    <span>Last Name</span>
                                    <i></i>
                                </div>
                                <div class="authenticate-input-box">
                                    <input type="email" name="email" required="required">
                                    <span>E-Mail</span>
                                    <i></i>
                                </div>
                                <div class="authenticate-input-box">
                                    <input type="date" name="dateofbirth" required="required">
                                    <span>Date Of Birth</span>
                                    <i></i>
                                </div>
                                <div class="authenticate-input-box">
                                    <input type="text" name="username" required="required">
                                    <span>Username</span>
                                    <i></i>
                                </div>
                                <div class="authenticate-input-box">
                                    <input type="password" name="password" required="required">
                                    <span>Password</span>
                                    <i></i>
                                </div>
                                <div class="authenticate-links">
                                    <a href="./authenticate.php">Already have an account. Login Here!</a>
                                </div>
                                <button type="submit" name="submit">Sign Up</button>
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
        </div>
			</div>
		</div>
		</div>
		<script src="./sources/sript.js"></script>
	</body>
</html>