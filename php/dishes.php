<?php include "server.php" ?>
<?php
  $sql = "SELECT * FROM dishes"; 
  $result = $conn->query($sql); 
  $dishes = Array(); 
  if ($result) {
    while ($row = $result->fetch_assoc()) { 
      array_push($dishes, $row); 
    } 
  }
?>
<?php
  $error = false;
  function create_order() {
    $sql = 'INSERT INTO orders ("status", "payment_method") VALUES("in_progress", "cash");';
    if (mysqli_query($conn, $sql)) {
      $last_id = mysqli_insert_id($conn);
      return $last_id;
    } else {
      echo "Error: " . $sql . "<br>";
    }
  }
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo json_encode($_POST);
    if (isset($_POST["is-toggle"])) {
      $sql = 'UPDATE dishes set enabled='. (isset($_POST["enabled"]) ? 1 : 0)  .' where id='. $_POST["id"] .';';
      if (!mysqli_query($conn, $sql)) {
        echo "Error: " . $sql . "<br>";
      } 

    } else {

      $sql = 'INSERT INTO dishes (title, price, image, enabled) VALUES("'.$_POST["title"].'", "'.$_POST["price"].'","'.$_POST["image"].'", "'.( isset($_POST["enabled"]) ? 1 : 0).'");';
      if (!mysqli_query($conn, $sql)) {
        echo "Error: " . $sql . "<br>";
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include "headings.php" ?>
  </head>
  <body>
    <?php include "navbar.php" ?>
    <div class="container pt-4">
      <?php 
        if ($error) {
          echo '<div class="alert alert-danger" role="alert">'. $error .'</div>';
        }
      ?>
      <div class="d-flex flex-row px-2 border-bottom py-4">
        <h3 class="fw-semibold">
          Dishes
        </h3>
        <button type="button" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modal">
          Add Dish
        </button>   
      </div>
      
      <div id="dishes" class="row align-items-start mt-2">
        <?php 
            foreach ($dishes as $key => $value) {
              echo '
                <div class="col-12 col-lg-4 col-md-4 py-1 col-sm-6 px-2">
                  <div class="card pb-2 px-0" id="1">
                    <img
                      src="'. $value['image'] .'"
                      class="img-thumbnail border-0 mx-0 px-0 py-0"
                      style="max-height: 200px; object-fit: cover;"
                    />
                    <div class="px-2 w-100 d-flex flex-column pt-4">
                      <h5 class="mb-0">
                        '. $value['title'] .'
                      </h5>
                      <p class="mt-1 mb-0 fw-semibold">
                        $'. $value['price'] .'
                      </p>
                      <form method="POST">
                      <div class="form-check form-switch pt-0">
                        <label class="form-check-label" >
                          <input 
                            class="form-check-input" 
                            type="checkbox" 
                            name="enabled" 
                           '.($value['enabled'] === '1' ?  "checked" : "").'
                            role="switch">
                          Enabled
                        </label>
                      </div>
                      <input hidden name="is-toggle" value="true" />
                      <input hidden name="id" value="'. $value['id'] .'" />
                      <button class="btn btn-primary mt-1">
                        Save
                      </button>
                    </form>
                    </div>
                  </div>
                </div>
              ';
            }
        ?>
      </div>
    </div>
    <div class="modal" id="modal" tabindex="-1">
      <form method="POST">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Create Dish</h5>
              <button type="reset" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3 w-100">
                <label class="form-label w-100">Image Url
                  <input class="form-control w-100" required name="image" placeholder="Image Url">
                </label>
              </div>
              <div class="mb-3 w-100">
                <label class="form-label w-100">Title
                  <input class="form-control w-100" required name="title"  placeholder="Title">
                </label>
              </div>
              <div class="mb-3">
                <label class="form-label">Price in $
                  <input class="form-control" type="number" required name="price" class="w-100" placeholder="0.00" value="0">
                </label>
              </div>
              <div class="form-check form-switch mb-3">
                <label class="form-check-label">
                  <input 
                    class="form-check-input" 
                    type="checkbox"
                    name="enabled" 
                    checked
                    role="switch">
                  Enabled
                </label>
              </div>
            </div>
            <div class="modal-footer">
              <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </body>
</html>