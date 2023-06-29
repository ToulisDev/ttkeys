<?php
	session_start();
	require 'vendor/autoload.php';

	$m = new MongoDB\Client("mongodb://127.0.0.1/");
	
	$db = $m->ttkeys;
	$collection = $db->products;
	if (isset($_SESSION['is_admin'])) {
		unset($_SESSION['is_admin']);
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
								$search_results = $collection->find(
									[],
									[
										'sort' => ['release_date' => -1],
										'limit' => 4
									]
								);
								$numberOfProducts = 0;
								foreach ($search_results as $product) {
									$grid = $db->selectGridFSBucket();
									$image = $grid->findOne(['_id' => $product['image_id']]);
									if ($image) {
										$downloadStream = $grid->openDownloadStream($product['image_id']);
										$image_data = stream_get_contents($downloadStream);
										$image_url = 'data:image/jpg;base64,' . base64_encode($image_data);
									}
										echo '<a class="featured-slider-item active" style="background-image: url(\'' . $image_url . '\');" href="./product.php?product_id=' . $product['_id'] . '">';
										echo '<div class="featured-slider-item-info-wrapper">';
										echo '<div class="featured-slider-item-info">';
										echo '<h2 class="title">' . $product['title'] . '</h2>';
										echo '<h3 class="price highlight">€' . $product['price'] . '</h3>';
										echo '<p class="description">' . $product['description'] . '</p>';
										echo '</div>';
										echo '</div>';
										echo '</a>';
										$numberOfProducts++;
								}?>
						</div>
						<div class="featured-slider-navigator">
							<?php for ($i = 0; $i < $numberOfProducts; $i++): ?>
								<div class="featured-slider-navigator-bar"></div>
							<?php endfor; ?>
						</div>
					</div>
					<form action="search.php" method="get" id="search">
						<input name="search_query" class="search-input" type="search" placeholder="Search" />
						<button class="search-button" type="submit">
							<i class="fa-solid fa-magnifying-glass"></i>
						</button>
					</form>
					<div id="browse">
						<a class="browse-option" href="./search.php?type=New" type="button">
							<div class="browse-option-background"></div>
							<i class="fa-solid fa-bolt"></i>
							<span class="label">New</span>
						</a>
						<a class="browse-option" href="./search.php?type=Tags" type="button">
							<div class="browse-option-background"></div>
							<i class="fa-solid fa-tags"></i>
							<span class="label">Tags</span>
						</a>
						<a class="browse-option" href="./search.php?type=Specials" type="button">
							<div class="browse-option-background"></div>
							<i class="fa-light fa-percent"></i>
							<span class="label">Specials</span>
						</a>
						<a class="browse-option" href="./search.php?type=Arcade" type="button">
							<div class="browse-option-background"></div>
							<i class="fa-solid fa-sparkles"></i>
							<span class="label">Arcade</span>
						</a>
					</div>
					<div id="list" class="no-scrollbar">
						<div class="list-options">
							<button class="list-option active" type="button"> New </button>
							<button class="list-option" type="button"> Trending </button>
							<button class="list-option" type="button"> Upcoming </button>
						</div>
						<div class="list-items">
							<?php
							$products = $collection->find();
							foreach ($products as $product) {
							$grid = $db->selectGridFSBucket();
							$image = $grid->findOne(['_id' => $product['image_id']]);
							if ($image) {
								$downloadStream = $grid->openDownloadStream($product['image_id']);
								$image_data = stream_get_contents($downloadStream);
								$image_url = 'data:image/jpg;base64,' . base64_encode($image_data);
							}
							echo '<a href="./product.php?product_id=' . $product['_id'] . '" class="list-item">';
							echo '<img class="list-item-image" src="' . $image_url . '" alt="' . $product['title'] .'" />';
							echo '<div class="list-item-info">
								<div class="title-wrapper section">';
							echo '<span class="title">' . $product['title'] . '</span>';
							echo '</div>
								<div class="tags section">';
							$tags = iterator_to_array($product['tags']);
							$lastTag = end($tags);
							foreach ($tags as $tag) {
							echo '<span class="tag">' . $tag . '</span>';
							if ($tag !== $lastTag) {
								echo '<span class="dot">·</span>';
							}
							}
							echo '</div>
								<div class="numbers section">';
							echo '<span class="price">€' . $product['price'] . '</span>
								<span class="dot">·</span>';
							$total_ratings = $product['likes'] + $product['dislikes'];
							if ($total_ratings > 0) {
							$rating = ($product['likes']/$total_ratings) * 100;
							if ($rating > 0) {
								echo '<span class="rating">' . $rating . '%</span>';
							} else {
								echo '<span class="rating">-%</span>';
							}
							} else {
							echo '<span class="rating">-%</span>';
							}
							echo '</div>
								</div>
								<p name="release-date" style="display: none;">' . date('m-d-Y', $product['release_date']->toDateTime()->getTimestamp()) . '</p>
								</a>';
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
        </div>
			</div>
		</div>
		</div>
		<script src="./sources/sript.js"></script>
		<script>
			// Sorting functionality
			const listOptions = document.querySelectorAll('.list-option');
			const listItems = document.querySelectorAll('.list-item');

			listOptions.forEach(option => {
			option.addEventListener('click', function() {
				let activeOption = document.querySelector('.list-option.active');
				if (activeOption) {
				activeOption.classList.remove('active');
				}
				this.classList.add('active');
				sortList();
			});
			});

			sortList();

			function sortList() {
				let activeOption = document.querySelector('.list-option.active').textContent.toLowerCase();
				let parent = document.querySelector(".list-items");

				switch (activeOption) {
					case " new ":
					let sortedNew = Array.from(listItems).sort((a, b) => {
						let dateA = new Date(a.querySelector("p[name='release-date']").innerHTML);
						let dateB = new Date(b.querySelector("p[name='release-date']").innerHTML);
						return dateB - dateA;
					});

					parent.innerHTML = "";

					sortedNew.forEach(item => {
						let releaseDate = new Date(item.querySelector("p[name='release-date']").innerHTML);
						if (releaseDate <= new Date()) {
						parent.appendChild(item);
						}
					});
					break;
					case " trending ":
					let sortedTrending = Array.from(listItems).sort((a, b) => {
						let ratingA = parseInt(a.querySelector(".rating").innerHTML);
						let ratingB = parseInt(b.querySelector(".rating").innerHTML);
						return ratingB - ratingA;
					});

					parent.innerHTML = "";

					sortedTrending.forEach(item => {
						parent.appendChild(item);
					});
					break;
					case " upcoming ":
					let sortedUpcoming = Array.from(listItems).sort((a, b) => {
						let dateA = new Date(a.querySelector("p[name='release-date']").innerHTML);
						let dateB = new Date(b.querySelector("p[name='release-date']").innerHTML);
						return dateA - dateB;
					});

					parent.innerHTML = "";

					sortedUpcoming.forEach(item => {
						let releaseDate = new Date(item.querySelector("p[name='release-date']").innerHTML);
						if (releaseDate > new Date()) {
						parent.appendChild(item);
						}
					});
					break;
				}
			}
		</script>
	</body>
</html>