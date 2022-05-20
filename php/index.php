<?php include "server.php" ?>

<?php
  $error = false;
  if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST["delete"])) {
    if (!$_POST["status"] || !$_POST["id"]) {
      $error = 'Error when trying to update';
      return;
    } else {
      $sql = 'UPDATE orders SET status="'. $_POST["status"] .'" WHERE id='. $_POST["id"] .';';
      $conn->query($sql);
    }
  }
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) { 
    $sql = 'DELETE FROM orders WHERE id='. $_POST["delete"] .';';
    $conn->query($sql);
  }
?>

<?php
  function get_orders($conn) {
    $sql = "SELECT * FROM orders ORDER BY created_at desc;"; 
    $result = $conn->query($sql); 
    $orders = Array(); 
    if ($result) {
      while ($row = $result->fetch_assoc()) { 
        array_push($orders, $row); 
      } 
    }
    return $orders;
  }

  function join_orders_dishes($orders, $conn) {
    $ids = [];
    foreach ($orders as $key => $value) {
      array_push($ids, $value['id']);
    }
    $sql = 'SELECT *, dishes.title, dishes.image FROM order_dishes LEFT OUTER JOIN dishes ON dishes.id = order_dishes.dish_id where order_id in ('. implode(',', $ids) .');'; 
    $dishes = $conn->query($sql); 

    $joined_orders = array();
    foreach ($orders as $key => $value) {
      $joined_orders[$value['id']] = $value;
      $joined_orders[$value['id']]['dishes'] = [];
    }

    if ($dishes) {
      while ($dish = $dishes->fetch_assoc()) { 
        array_push($joined_orders[$dish['order_id']]['dishes'], $dish);
      } 
    }

    $orders = [];
    foreach ($joined_orders as $key => $value) {
      array_push($orders, $value);
    }

    return $orders;
  }

  $orders = get_orders($conn);
  $orders =  join_orders_dishes($orders, $conn);  
?>



<?php
  $sql = "SELECT
    DATE(orders.created_at) as date,
    SUM(orders.total) as total
  FROM
    orders
  GROUP BY
    DATE(orders.created_at);"; 
  
  $result = $conn->query($sql); 
  $sales = Array(['Date', 'Revenue']); 
  if ($result) {
    while ($row = $result->fetch_assoc()) { 
      array_push($sales, [$row['date'], $row['total']]); 
    } 
  }
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include "headings.php" ?>
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
      google.charts.load('current', {packages: ['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = google.visualization.arrayToDataTable( <?php echo json_encode($sales, JSON_NUMERIC_CHECK) ?>);
        var options = {
          title: 'All Time Revenue',
          subtitle: 'by day',
          curveType: 'function',
          legend: { position: 'bottom' }
        };
        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

        chart.draw(data, options);

        }
    </script>
  </head>
  <body>
    <?php include "navbar.php" ?>
    <div class="container pt-4">
      <?php 
        if ($error) {
          echo '<div class="alert alert-danger" role="alert">'. $error .'</div>';
        }
      ?>
      <div class="container-fluid py-4">
        <div class="card px-2 py-4">
          <h3 class="fw-semibold">
            Analytics
          </h3>
          <div id="curve_chart" style="width: 100%;"></div>
        </div>
      <div class="card mt-4">
        <div class="d-flex flex-row px-2 border-bottom py-4">
          <h3 class="fw-semibold">
            Orders
          </h3>
         
        </div>
        <table class="table mb-0">
          <thead>
            <tr>
              <th scope="col">Order#</th>
              <th scope="col">Invoice</th>
              <th scope="col">Placed at</th>
              <th scope="col">Total</th>
              <th scope="col">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              function dishes_mapper($dish) {
                return ('
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                      <div class="ms-2 me-auto">
                        <div class="fw-bold">'.$dish['title'].'</div>
                          '.$dish['instructions'].'
                        </div>
                      <span class="badge text-bg-light rounded-pill">Qty: '.$dish['quantity'].'</span>
                    </li>');
              }
              foreach ($orders as $key => $value) {
                echo '<tr>
                  <th scope="row">'. $value['id'] .'</th>
                  <td>
                    <ul class="list-group">
                      '. implode(array_map('dishes_mapper',$value['dishes'])) .'
                    </ul>
                  </td>
                  <td>'. $value['created_at'] .'</td>
                  <td>'. $value['total'] .'</td>
                  <td>
                    <div class="d-flex flex-row">
                      <form method="POST" class="d-flex flex-grow-1 flex-row"> 
                        <select name="status" value="'. $value['status'] .'" class="form-select">
                          <option '. ($value['status'] == "pending" ? 'selected' : '')  .' value="pending">Pending</option>
                          <option '. ($value['status'] == "in_progress" ? 'selected' : '')  .' value="in_progress">In-progress</option>
                          <option '. ($value['status'] == "completed" ? 'selected' : '')  .' value="completed">Completed</option>
                          <option '. ($value['status'] == "cancelled" ? 'selected' : '')  .' value="cancelled">Cancelled</option>
                        </select>
                        <input hidden name="id" value="'. $value['id'] .'" />
                        <button class="btn btn-primary mx-2">
                          Save
                        </button>
                      </form>
                      <form method="POST"> 
                        <input hidden name="delete" value="'. $value['id'] .'" />
                        <button class="btn btn-danger mx-2">
                          Delete
                        </button>
                      </div>
                    </form>
                  </td>
                </tr>';
              } 
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </body>
</html>

<?php   try {
$conn->close(); 
} catch (e) {}  ?>