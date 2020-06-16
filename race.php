<?php
// Initial DB Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbName = "bankk";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbName);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Initiate Array for Alerts
$error = array();
$success = array();

// Check if there is any POST Request
if (!empty($_POST)) {

  // Checking empty inputs
  if (empty($_POST['acct'])) {
    array_push($error, "Anda belum memilih Akun!");
  }

  if (empty($_POST['from'])) {
    array_push($error, "Anda belum memilih Rekening Asal!");
  }

  if (empty($_POST['to'])) {
    array_push($error, "Anda belum memilih Rekening Tujuan!");
  }

  if ($_POST['to'] == $_POST['from']) {
    array_push($error, "Anda tidak bisa mengirim ke rekening yang sama!");
  }

  if (empty($_POST['amount'])) {
    array_push($error, "Anda belum memasukkan Jumlah!");
  }


  // Process if there is no Error
  if (count($error) == 0) {

    // Initiate Variable
    $selectedAccount;

    // Get Account from Database
    $sql = "SELECT * FROM account WHERE id = " . $_POST['acct'];
    $result = $conn->query($sql);

    // Assign result to variable
    while ($row = $result->fetch_assoc()) {
      $selectedAccount = $row;
    }

    // Check if account is disabled or locked
    if ($selectedAccount['locked'] == 1 || $selectedAccount['enabled'] == 0) {
      array_push($error, "Akun ini telah dibekukan!");
    } else {
      // Check if from balance is enough
        if ($_POST['amount'] > $selectedAccount[$_POST['from']]) {
          array_push($error, 'Saldo anda tidak mencukupi!');
        } else {

          // Process Subtraction from account
          $selectedAccount[$_POST['from']] = $selectedAccount[$_POST['from']] - $_POST['amount'];

          // Process Addition to account
          $selectedAccount[$_POST['to']] = $selectedAccount[$_POST['to']] + $_POST['amount'];

          // Update value in database
          $sql = "UPDATE `account` SET " . $_POST['from'] . " = " . $selectedAccount[$_POST['from']] . ", " . $_POST['to'] . " = " . $selectedAccount[$_POST['to']] . " WHERE `account`.`id` = " . $_POST['acct'];

          // Execute Query
          if ($conn->query($sql) === TRUE) {
            array_push($success, 'Transaction Success!');
          } else {
            array_push($error, 'Transaction Error!');
          }
        }
      }
    }
  }

// Get all account from database
$sql = "SELECT * FROM account";
$accounts = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Vulnerable Bank</title>
  <link defer rel="shortcut icon" href="https://www.bca.co.id/assets/images/favicon.png">
  <link rel="stylesheet" href="https://bootswatch.com/4/lumen/bootstrap.css">
</head>

<body>

  <form action="" method="post">
    <div class="container text-center my-5">
      <div class="row">
        <div class="col-md-8 mx-auto">
          <h1 class="">Bank Transaction</h1>
          <?php foreach ($error as $e) : ?>
            <div class="alert alert-danger alert-dismissible fade show shadow" role="alert">
              <?= $e ?>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          <?php endforeach; ?>
          <?php foreach ($success as $s) : ?>
            <div class="alert alert-success alert-dismissible fade show shadow" role="alert">
              <?= $s ?>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          <?php endforeach; ?>
          <div class="card shadow my-4">
            <div class="card-body">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Select Account</th>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Tabungan</th>
                    <th>Deposit</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($a = $accounts->fetch_array()) : ?>
                    <tr>
                      <td>
                        <input type="radio" name="acct" id="acct" value="<?= $a['id'] ?>">
                      </td>
                      <td><?= $a['id'] ?></td>
                      <td><?= $a['username'] ?></td>
                      <td><?= $a['tabungan'] ?></td>
                      <td><?= $a['deposit'] ?></td>
                      <td><?= $a['tabungan'] + $a['deposit'] ?></td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4 mx-auto">
          <div class="card shadow">
            <div class="card-body">
              <div class="form-group">
                <label for="from">Rekening Asal:</label>
                <select name="from" id="from" class="form-control">
                  <option value="tabungan">Tabungan</option>
                  <option value="deposit">Deposit</option>
                </select>
              </div>
              <div class="form-group">
                <label for="to">Rekening Tujuan:</label>
                <select name="to" id="to" class="form-control">
                  <option value="tabungan">Tabungan</option>
                  <option value="deposit">Deposit</option>
                </select>
              </div>
              <div class="form-group">
                <label for="amount">Jumlah</label>
                <input type="number" name="amount" class="form-control">
              </div>
              <input type="submit" value="Submit" class="btn btn-block btn-primary">
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

  <script src="https://bootswatch.com/_vendor/jquery/dist/jquery.min.js"></script>
  <script src="https://bootswatch.com/_vendor/popper.js/dist/umd/popper.min.js"></script>
  <script src="https://bootswatch.com/_vendor/bootstrap/dist/js/bootstrap.min.js"></script>
</body>

</html>