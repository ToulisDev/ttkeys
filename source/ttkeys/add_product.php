<?php
session_start();
require "vendor/autoload.php";

if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("Location: ./index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"];
    $description = $_POST["description"];
    $price = floatval($_POST["price"]);
    $release_date = new MongoDB\BSON\UTCDateTime(
        strtotime($_POST["release_date"]) * 1000
    );
    $tags = explode(",", $_POST["tags"]);
    $likes = 0;
    $dislikes = 0;
    $comments = [];

    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    $product_bulk = new MongoDB\Driver\BulkWrite();
    $tag_bulk = new MongoDB\Driver\BulkWrite();

    $product_id = new MongoDB\BSON\ObjectId();

    // Connect to MongoDB database
    $client = new MongoDB\Client();

    // Get the database and GridFS collections
    $db = $client->ttkeys;
    $gridFS = $db->selectGridFSBucket();

    // Store image in GridFS
    $file = $_FILES["image"]["tmp_name"];
    $stream = fopen($file, "r");
    $id = $gridFS->uploadFromStream("image", $stream);

    // Insert the product
    $product_bulk->insert([
        "_id" => $product_id,
        "title" => $title,
        "description" => $description,
        "price" => $price,
        "release_date" => $release_date,
        "tags" => $tags,
        "image_id" => $id,
        "likes" => $likes,
        "dislikes" => $dislikes,
        "comments" => $comments,
    ]);

    // Insert the tags
    foreach ($tags as $tag) {
        $tag_bulk->update(
            ["tag" => $tag],
            ['$addToSet' => ["products" => $product_id]],
            ["upsert" => true]
        );
    }

    $manager->executeBulkWrite("ttkeys.products", $product_bulk);
    $manager->executeBulkWrite("ttkeys.tags", $tag_bulk);

    header("Location: ./admin_products.php");
    exit();
}
?>
 
<html>
	<head>
		<title>Add Product</title>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	</head>
	<body>
		<div class="container mt-5">
			<h1 class="text-center mb-5">Add Product</h1>
			<form action="add_product.php" method="post" enctype="multipart/form-data">
				<div class="form-group">
					<label for="title">Title:</label>
					<input type="text" id="title" name="title" class="form-control" required>
				</div>
				<div class="form-group">
					<label for="description">Description:</label>
					<textarea id="description" name="description" class="form-control" required></textarea>
				</div>
				<div class="form-group">
					<label for="price">Price €:</label>
					<input type="number" min="0" step="any" id="price" name="price" class="form-control" required>
				</div>
				<div class="form-group">
					<label for="release_date">Release Date:</label>
					<input type="date" id="release_date" name="release_date" class="form-control" required>
				</div>
				<div class="form-group">
					<label for="tags">Tags:</label>
					<input type="text" id="tags" name="tags" class="form-control" required>
					<div id="tag-list"></div>
				</div>
				<div class="form-group">
					<label for="image">Image:</label>
					<div class="custom-file">
						<input type="file" class="custom-file-input" id="image" name="image" accept="image/jpeg" required>
						<label class="custom-file-label" for="image">Choose File</label>
					</div>
				</div>
				<div class="form-group text-center">
					<input type="submit" value="Add Product" class="btn btn-primary">
					<a href="admin_products.php" class="btn btn-secondary">Back</a>
				</div>
			</form>
		</div>
		<script>
			document.getElementById("image").addEventListener("change", function() {
				const fileName = this.value.split("\\").pop();
				document.querySelector(".custom-file-label").innerHTML = fileName;
			});
		</script>
	</body>
</html>