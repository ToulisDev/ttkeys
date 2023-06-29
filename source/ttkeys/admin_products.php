<?php
    session_start();
    require 'vendor/autoload.php';
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header("Location: ./index.php");
        exit();
    }
    
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $db = $client->ttkeys;
    $collection = $db->products;

    if (isset($_GET['delete_id'])) {
        $id = new MongoDB\BSON\ObjectId($_GET['delete_id']);
        $product = $collection->findOne(['_id' => $id]);
        $tagsCollection = $db->tags;
        $tagstoDelete = iterator_to_array($product['tags']);
        foreach ($tagstoDelete as $tag) {
            $tagsCollection->updateOne(
                ['name' => $tag],
                ['$pull' => ['product_ids' => $id]]
            );
        }
        if (isset($product['image_id'])) {
            $bucket = $db->selectGridFSBucket();
            $bucket->delete($product['image_id']);
        }
        $deleteResult = $collection->deleteOne(['_id' => $id]);

        if ($deleteResult->getDeletedCount() >= 1) {
            
            header("Location: admin_products.php");
            exit();
        } else {
            echo 'Error deleting product.';
        }
    }

    $products = $collection->find();
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Admin Products</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  </head>
  <body>
    <div class="container mt-5">
      <h1 class="text-center">Admin Panel - Products</h1>
      <p>
        <a href="add_product.php" class="btn btn-success">Add Product</a>
        <a href="admin.php" class="btn btn-primary">Back to Admin Panel</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
      </p>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Price €</th>
            <th>Release Date</th>
            <th>Tags</th>
            <th>Likes</th>
            <th>Dislikes</th>
            <th>Comments</th>
            <th>Image</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $product) : ?>
            <tr>
              <td><?php echo $product['title']; ?></td>
              <td><?php echo $product['description']; ?></td>
              <td><?php echo $product['price']; ?></td>
              <td><?php echo date('d-m-Y', $product['release_date']->toDateTime()->getTimestamp()); ?></td>
              <td><?php echo implode(', ', iterator_to_array($product['tags'])); ?></td>
              <td><?php echo $product['likes']; ?></td>
              <td><?php echo $product['dislikes']; ?></td>
              <td><?php 
              foreach ($product['comments'] as $comment) {
                echo $comment['username'] . ':' . $comment['comment'] . ',';
              }
              ?></td>
              <td> 
                <?php 
                    if (isset($product['image_id'])) {
                    $grid = $db->selectGridFSBucket();
                    $image = $grid->findOne(['_id' => $product['image_id']]);
                    if ($image) {
                        $downloadStream = $grid->openDownloadStream($product['image_id']);
                        $image_data = stream_get_contents($downloadStream);
                        $image_data_uri = 'data:image/jpg;base64,' . base64_encode($image_data);
                    }
                ?>
                <img src="<?php echo $image_data_uri; ?>" alt="<?php echo $product['title']; ?>" width="400" height="240" />
                <?php } ?>
            </td>
              <td>
                <a href="edit_product.php?id=<?php echo $product['_id']; ?>" class="btn btn-warning">Edit</a>
                <a href="admin_products.php?delete_id=<?php echo $product['_id']; ?>" class="btn btn-danger">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </body>
</html>

