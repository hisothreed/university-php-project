<?php include "server.php" ?>
<?php
  $sql = "SELECT * FROM dishes"; 
  $result = $conn->query($sql); 
  $rows = Array(); 
  if ($result) {
    while ($row = $result->fetch_assoc()) { 
      array_push($rows, $row); 
    } 
  }
?>
<?php
  $error = false;
  function create_order() {
    $sql = 'INSERT INTO orders ("status") VALUES("in_progress");';
    if (mysqli_query($conn, $sql)) {
      $last_id = mysqli_insert_id($conn);
      return $last_id;
    } else {
      echo "Error: " . $sql . "<br>";
    }
  }
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$_POST["cart-items"]) {
      $error = 'No items added to the cart';
    }
    if ($_POST["cart-items"]) {
      $cart_items = json_decode($_POST["cart-items"], true);
      $total = 0;
      foreach ($cart_items as $key => $value) {
        $total += intval($value['price'] * $value['quantity']);
      }
      $total = $total + ($total * 0.16);
      $sql = 'INSERT INTO orders (status, total) VALUES("in_progress", "'. $total .'");';
      if (mysqli_query($conn, $sql)) {
        $last_id = mysqli_insert_id($conn);
        $sql = "";
        foreach ($cart_items as $key => $value) {
          $sql .= 'INSERT INTO order_dishes (order_id, dish_id, price, quantity, instructions) 
                  VALUES ('. $last_id .', '. $value["id"] .','. $value["price"] .','. $value["quantity"] .',"'. $value["instructions"] .'");
                ';
        }
        if (!mysqli_multi_query($conn, $sql)) {
           echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
      } else {
        echo "Error: " . $sql . "<br>";
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include "headings.php" ?>
    <script type="text/javascript" src="newOrder.js"></script>
    <script>
      document.addEventListener(
        "DOMContentLoaded",
        () => {
          renderDishes(<?php echo json_encode($rows); ?>);
        },
        false
      );
    </script>
  </head>
  <body>
    <?php include "navbar.php" ?>
    <div class="container py-4">
      <?php 
        if ($error) {
          echo '<div class="alert alert-danger" role="alert">'. $error .'</div>';
        }
      ?>
      <div id="dishes" class="row align-items-start mt-2">
        <!-- this will be filled by JS or returend from the server -->
      </div>
      <div class="mt-4">
        <h5>Cart</h5>
        <div>
          <div id="cart"></div>
          <hr />
          <h5>Checkout</h5>
          <div class="card px-2 py-2">
            <div class="d-flex flex-row py-2 border-bottom">
              <p class="flex-grow-1 mb-0">Total before tax</p>
              <p id="total-bt" class="mb-0 fw-semibold">$0</p>
            </div>
            <div class="d-flex flex-row mt-2 py-2 border-bottom">
              <p class="mb-0 flex-grow-1">Tax</p>
              <p id="tax" class="mb-0 fw-semibold">16%</p>
            </div>
            <div class="d-flex flex-row mt-2 py-2 border-bottom">
              <p class="mb-0 flex-grow-1">Total to be paid</p>
              <p id="total" class="mb-0 fw-semibold">$0</p>
            </div>
            <div class="mt-4">
              <form method="POST" name="my-form" id="my-form">
                <button onclick="submit()" type="button" class="btn btn-primary">Checkout</button>
                <input name="cart-items" id="cart-items-input" hidden />
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
