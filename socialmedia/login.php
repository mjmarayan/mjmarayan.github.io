<?php require "functions.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = addslashes($_POST['email']);
    $password = addslashes($_POST['password']);

    $query = "SELECT * FROM users WHERE email = '$email' && password = '$password' LIMIT 1";
    $result = mysqli_query($con, $query);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['info'] = $row;
        header("Location: profile.php");
        die;
    } else {
        $error = "<script>alert('Wrong email or password! Please try again.');</script>";
    }

}
?>
<!DOCTYPE html>
<head>
    <title>Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: Tahoma, sans-serif;
        }

        .content {
            flex: 1;
        }

        form {
            text-align: center;
        }

        input {
            width: 80%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            background: #007bff;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        footer {
            margin-top: auto;
            text-align: center;
            background-color: #f5f5f5;
            padding: 10px;
        }

        .show-pass {
            font-size: 14px;
        }
        
    </style>
</head>
<body>
  <?php require "header.php"; ?>

  <div class="content">
    <div style="margin: auto; max-width: 600px;">
      <?php if(!empty($error)){ echo "<div>".$error."</div>"; } ?>

      <h2 style="text-align: center;">Login</h2>

      <form method="post" style="margin: auto; padding:10px;">
        <input type="email" name="email" placeholder="Email" required><br>

        <input type="password" id="password" name="password" placeholder="Password" required><br><br>

        <button>Login</button>
      </form>
    </div>
  </div>

  <?php require "footer.php"; ?>

</body>
</html>