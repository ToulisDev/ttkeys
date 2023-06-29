<?php
session_start();
require "vendor/autoload.php";
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("Location: ./index.php");
    exit();
}
if (!isset($_GET["id"])) {
    header("Location: ./admin_products.php");
    exit();
}

$client = new MongoDB\Client("mongodb://localhost:27017");
$db = $client->ttkeys;
$productsCollection = $db->products;
$tagsCollection = $db->tags;
$id = new MongoDB\BSON\ObjectId($_GET["id"]);
$product = $productsCollection->findOne(["_id" => $id]);

if (!$product) {
    header("Location: ./admin_products.php");
    exit();
}

if (isset($_POST["submit"])) {
    $updateResult = $productsCollection->updateOne(
        ["_id" => $id],
        [
            '$set' => [
                "title" => $_POST["title"],
                "description" => $_POST["description"],
                "price" => (float) $_POST["price"],
                "release_date" => new MongoDB\BSON\UTCDateTime(
                    (new DateTime($_POST["release_date"]))->getTimestamp() *
                        1000
                ),
                "tags" => explode(",", $_POST["tags"]),
            ],
        ]
    );

    $oldTags = iterator_to_array($product["tags"]);
    $newTags = explode(",", $_POST["tags"]);
    $tagsToDelete = array_diff($oldTags, $newTags);
    $tagsToAdd = array_diff($newTags, $oldTags);

    foreach ($tagsToDelete as $tag) {
        $tagsCollection->updateOne(
            ["name" => $tag],
            ['$pull' => ["product_ids" => $id]]
        );
    }

    foreach ($tagsToAdd as $tag) {
        $tagsCollection->updateOne(
            ["name" => $tag],
            ['$addToSet' => ["product_ids" => $id]],
            ["upsert" => true]
        );
    }

    if ($updateResult->getMatchedCount() == 1) {
        header("Location: admin_products.php");
        exit();
    } else {
        echo "Error updating product.";
    }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  </head>
  <body>
    <div class="container mt-5">
      <h1>Edit Product</h1>
      <p>
        <a href="admin.php" class="btn btn-primary">Back to Admin Panel</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
      </p>
      <form action="" method="post">
        <div class="form-group">
          <label for="title">Title</label>
          <input type="text" name="title" id="title" value="<?php echo $product['title']; ?>" class="form-control">
        </div>
        <div class="form-group">
          <label for="description">Description</label>
          <textarea name="description" id="description" rows="5" class="form-control"><?php echo $product['description']; ?></textarea>
          </div>
    <div class="form-group">
      <label for="price">Price</label>
      <input type="number" min="0" step="any" name="price" id="price" value="<?php echo $product['price']; ?>" class="form-control">
    </div>
    <div class="form-group">
      <label for="release_date">Release Date</label>
      <input type="date" name="release_date" id="release_date" value="<?php echo date('Y-m-d', $product['release_date']->toDateTime()->getTimestamp()); ?>" class="form-control">
    </div>
    <div class="form-group">
      <label for="tags">Tags (comma-separated)</label>
      <input type="text" name="tags" id="tags" value="<?php echo implode(',', iterator_to_array($product['tags'])); ?>" class="form-control">
    </div>
    <input type="submit" name="submit" value="Save Changes" class="btn btn-primary">
  </form>
</div>
</body>
</html>
