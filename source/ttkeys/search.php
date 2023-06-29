<?php
session_start();
require 'vendor/autoload.php';
$m = new MongoDB\Client("mongodb://127.0.0.1/");
$db = $m->ttkeys;


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
					<form action="search.php" method="get" id="search">
						<input name="search_query" class="search-input" type="search" placeholder="Search" />
						<button class="search-button" type="submit">
							<i class="fa-solid fa-magnifying-glass"></i>
						</button>
					</form>
          <?php
            if (isset($_GET['search_query'])):
            	$search_query = $_GET['search_query'];
              
              	$collection = $db->products;
          
              	$search_results = $collection->find([
					'title' => [
						'$regex' => new MongoDB\BSON\Regex($search_query, 'i')
					]
					]);
              ?>
              <h2 class="search-title">Search Results for "<span class="search-term highlight"><?php echo $search_query; ?></span>"</h2>
              <div id="list" class="">
                <div class="list-items">
                <?php
                    foreach ($search_results as $product) {
                      $grid = $db->selectGridFSBucket();
                      $image = $grid->findOne(['_id' => $product['image_id']]);
                      if ($image) {
                        $downloadStream = $grid->openDownloadStream($product['image_id']);
                        $image_data = stream_get_contents($downloadStream);
                        $image_url = 'data:image/jpg;base64,' . base64_encode($image_data);
                      }
                        echo '<a href="./product.php?product_id=' . $product['_id'] . '" class="list-item" data-tags="' . implode(' ', iterator_to_array($product['tags'])) . '">';
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
                              </a>';
                    }?>
                </div>
              </div>
          <?php endif; ?>
		  <?php
			if (isset($_GET['type'])):
				$type = $_GET['type'];
				$collection = $db->products;
				$search_results = [];

				switch ($type) {
				case 'New':
					$search_results = $collection->find([ 
						'release_date' => ['$lte' => new MongoDB\BSON\UTCDateTime(time() * 1000)] ],[ 
						"sort" => [ 'release_date' => -1, 
						]]);
					break;
				case 'Tags':
					$search_results = $collection->find();
					$tagsCollection = $db->tags;
					$tags = $tagsCollection->find();
					break;
				case 'Specials':
					$search_results = $collection->find([
					'price' => ['$lt' => 10],
					]);
					break;
				case 'Arcade':
					$search_results = $collection->find([
					'tags' => 'Arcade',
					]);
					break;
				}
				?>
              <div id="list" class="no-scrollbar">
				<?php if (isset($tags)): ?>
					<div class="list-options">
						<?php foreach ($tags as $tag): ?>
						<button class="list-option" type="button"><?php echo $tag['tag']; ?></button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
                <div class="list-items">
                <?php
                    foreach ($search_results as $product) {
                      $grid = $db->selectGridFSBucket();
                      $image = $grid->findOne(['_id' => $product['image_id']]);
                      if ($image) {
                        $downloadStream = $grid->openDownloadStream($product['image_id']);
                        $image_data = stream_get_contents($downloadStream);
                        $image_url = 'data:image/jpg;base64,' . base64_encode($image_data);
                      }
                        echo '<a href="./product.php?product_id=' . $product['_id'] . '" class="list-item" data-tags="' . implode(' ', iterator_to_array($product['tags'])) . '">';
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
                              </a>';
                    }?>
                </div>
              </div>
          <?php endif; ?>
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

			function sortList() {
				const activeOption = document.querySelector('.list-option.active').textContent.toLowerCase();
				const listItems = document.querySelectorAll('.list-item');
				
				listItems.forEach((listItem) => {
					if (listItem.dataset.tags.toLowerCase().includes(activeOption)) {
						listItem.style.display = 'flex';
					} else {
						listItem.style.display = 'none';
					}
				});
			}

		</script>
	</body>
</html>