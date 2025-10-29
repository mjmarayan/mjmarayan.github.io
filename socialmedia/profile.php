<?php
require "functions.php";
check_login();

// -------------------- DELETE POST --------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['action']) && $_POST['action'] === 'post_delete') {

    $id = $_GET['id'] ?? 0;
    $user_id = $_SESSION['info']['id'];

    $query = "SELECT * FROM posts WHERE id = '$id' AND user_id = $user_id LIMIT 1";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (!empty($row['image']) && file_exists($row['image'])) {
            unlink($row['image']);
        }
    }

    $query = "DELETE FROM posts WHERE id = '$id' AND user_id = '$user_id' LIMIT 1";
    mysqli_query($con, $query);

    header("Location: profile.php");
    die;
}

// -------------------- EDIT POST --------------------
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['action']) && $_POST['action'] === 'post_edit') {

    $id = $_GET['id'] ?? 0;
    $user_id = $_SESSION['info']['id'];
    $image_added = false;

    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] == 0 && in_array($_FILES['image']['type'], $allowed)) {

        $folder = __DIR__ . "/uploads/";
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES['image']['name']);
        $webPath = "uploads/" . $filename;

        move_uploaded_file($_FILES['image']['tmp_name'], $folder . $filename);

        // remove old image
        $query = "SELECT * FROM posts WHERE id = '$id' AND user_id = $user_id LIMIT 1";
        $result = mysqli_query($con, $query);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if (!empty($row['image']) && file_exists($row['image'])) {
                unlink($row['image']);
            }
        }

        $image_added = true;
    }

    $post = addslashes($_POST['post']);

    if ($image_added) {
        $query = "UPDATE posts SET post = '$post', image = '$webPath' WHERE id = $id AND user_id = $user_id LIMIT 1";
    } else {
        $query = "UPDATE posts SET post = '$post' WHERE id = $id AND user_id = $user_id LIMIT 1";
    }

    mysqli_query($con, $query);
    header("Location: profile.php");
    die;
}

// -------------------- DELETE USER --------------------
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['action']) && $_POST['action'] === 'delete') {

    $id = $_SESSION['info']['id'];

    $query = "DELETE FROM users WHERE id = $id LIMIT 1";
    mysqli_query($con, $query);

    if (!empty($_SESSION['info']['image']) && file_exists($_SESSION['info']['image'])) {
        unlink($_SESSION['info']['image']);
    }

    $query = "DELETE FROM posts WHERE user_id = $id";
    mysqli_query($con, $query);

    header("Location: logout.php");
    die;
}

// -------------------- EDIT PROFILE --------------------
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['username'])) {

    $image_added = false;
    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] == 0 && in_array($_FILES['image']['type'], $allowed)) {

        $folder = __DIR__ . "/uploads/";
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES['image']['name']);
        $webPath = "uploads/" . $filename;

        move_uploaded_file($_FILES['image']['tmp_name'], $folder . $filename);

        if (!empty($_SESSION['info']['image']) && file_exists($_SESSION['info']['image'])) {
            unlink($_SESSION['info']['image']);
        }

        $image_added = true;
    }

    $username = addslashes($_POST['username']);
    $email = addslashes($_POST['email']);
    $password = addslashes($_POST['password']);
    $id = $_SESSION['info']['id'];

    if ($image_added) {
        $query = "UPDATE users SET username = '$username', email = '$email', password = '$password', image = '$webPath' WHERE id = $id LIMIT 1";
    } else {
        $query = "UPDATE users SET username = '$username', email = '$email', password = '$password' WHERE id = $id LIMIT 1";
    }

    mysqli_query($con, $query);

    $query = "SELECT * FROM users WHERE id = $id LIMIT 1";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['info'] = mysqli_fetch_assoc($result);
    }

    header("Location: profile.php");
    die;
}

// -------------------- CREATE POST --------------------
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['post'])) {

    $image = "";
    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] == 0 && in_array($_FILES['image']['type'], $allowed)) {

        $folder = __DIR__ . "/uploads/";
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES['image']['name']);
        $webPath = "uploads/" . $filename;

        move_uploaded_file($_FILES['image']['tmp_name'], $folder . $filename);
        $image = $webPath;
    }

    $post = addslashes($_POST['post']);
    $user_id = $_SESSION['info']['id'];
    $date = date('Y-m-d H:i:s');

    $query = "INSERT INTO posts (user_id, post, image, date) VALUES ($user_id, '$post', '$image', '$date')";
    mysqli_query($con, $query);

    header("Location: profile.php");
    die;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
</head>
<body>

<?php require "header.php"; ?>

<div style="margin: auto; max-width: 600px;">

<?php
// -------------------- DELETE POST CONFIRM --------------------
if (!empty($_GET['action']) && $_GET['action'] == 'post_delete' && !empty($_GET['id'])):
    $id = (int)$_GET['id'];
    $query = "SELECT * FROM posts WHERE id = '$id' LIMIT 1";
    $result = mysqli_query($con, $query);
    if (mysqli_num_rows($result) > 0):
        $row = mysqli_fetch_assoc($result);
?>
    <h3>Are you sure you want to delete this post?</h3>
    <form method="post" enctype="multipart/form-data" style="margin: auto; padding:10px;">
        <?php if (!empty($row['image'])): ?>
            <img src="<?= htmlspecialchars($row['image']) ?>" style="width:100%; height:200px; object-fit:cover;"><br>
        <?php endif; ?>
        <div><?= htmlspecialchars($row['post']) ?></div><br>
        <input type="hidden" name="action" value="post_delete">
        <button>Delete</button>
        <a href="profile.php"><button type="button">Cancel</button></a>
    </form>
<?php
    endif;

// -------------------- EDIT POST FORM --------------------
elseif (!empty($_GET['action']) && $_GET['action'] == 'post_edit' && !empty($_GET['id'])):
    $id = (int)$_GET['id'];
    $query = "SELECT * FROM posts WHERE id = '$id' LIMIT 1";
    $result = mysqli_query($con, $query);
    if (mysqli_num_rows($result) > 0):
        $row = mysqli_fetch_assoc($result);
?>
    <h5>Edit Post</h5>
    <form method="post" enctype="multipart/form-data" style="margin: auto; padding:10px;">
        <?php if (!empty($row['image'])): ?>
            <img src="<?= htmlspecialchars($row['image']) ?>" style="width:100%; height:200px; object-fit:cover;"><br>
        <?php endif; ?>
        Image: <input type="file" name="image"><br>
        <textarea name="post" rows="8"><?= htmlspecialchars($row['post']) ?></textarea><br>
        <input type="hidden" name="action" value="post_edit">
        <button>Save</button>
        <a href="profile.php"><button type="button">Cancel</button></a>
    </form>
<?php
    endif;

// -------------------- EDIT PROFILE --------------------
elseif (!empty($_GET['action']) && $_GET['action'] === 'edit'):
?>
    <h2 style="text-align: center;">Edit Profile</h2>
    <form method="post" enctype="multipart/form-data" style="margin: auto; padding:10px">
        <img src="<?= htmlspecialchars($_SESSION['info']['image']) ?>" style="width:100px; height:100px; object-fit:cover; display:block; margin:auto;"><br>
        Choose image: <input type="file" name="image"><br>
        <input value="<?= htmlspecialchars($_SESSION['info']['username']) ?>" type="text" name="username" placeholder="Username" required><br>
        <input value="<?= htmlspecialchars($_SESSION['info']['email']) ?>" type="email" name="email" placeholder="Email" required><br>
        <input value="<?= htmlspecialchars($_SESSION['info']['password']) ?>" type="text" name="password" placeholder="Password" required><br>
        <button>Save</button>
        <a href="profile.php"><button type="button">Cancel</button></a>
    </form>

<?php
// -------------------- DELETE PROFILE --------------------
elseif (!empty($_GET['action']) && $_GET['action'] === 'delete'):
?>
    <h2 style="text-align: center;">Are you sure you want to delete your profile?</h2>
    <div style="margin: auto; max-width: 600px; text-align: center;">
        <form method="post" style="margin: auto; padding:10px">
            <img src="<?= htmlspecialchars($_SESSION['info']['image']) ?>" style="width:100px; height:100px; object-fit:cover; margin:auto;"><br>
            <div><?= htmlspecialchars($_SESSION['info']['username']) ?></div><br>
            <div><?= htmlspecialchars($_SESSION['info']['email']) ?></div><br>
            <input type="hidden" name="action" value="delete">
            <button>Delete</button>
            <a href="profile.php"><button type="button">Cancel</button></a>
        </form>
    </div>

<?php
// -------------------- DEFAULT PROFILE PAGE --------------------
else:
?>
    <br>
    <h2 style="text-align: center;">User Profile</h2>
    <div style="margin: auto; max-width: 600px; text-align: center;">
        <div>
            <img src="<?= htmlspecialchars($_SESSION['info']['image']) ?>" style="width:150px; height:150px; object-fit:cover;">
        </div>
        <div><?= htmlspecialchars($_SESSION['info']['username']) ?></div>
        <div><?= htmlspecialchars($_SESSION['info']['email']) ?></div><br>
        <a href="profile.php?action=edit"><button>Edit Profile</button></a>
        <a href="profile.php?action=delete"><button>Delete Profile</button></a>
        <br><br>
    </div>

    <hr>
    <h5>Create a post</h5>
    <form method="post" enctype="multipart/form-data" style="margin: auto; padding:10px;">
        Choose image: <input type="file" name="image"><br>
        <textarea name="post" rows="8"></textarea><br>
        <button>Post</button>
    </form>
    <hr>

    <?php
    $id = $_SESSION['info']['id'];
    $query = "SELECT * FROM posts WHERE user_id = '$id' ORDER BY id DESC LIMIT 10";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0):
        while ($row = mysqli_fetch_assoc($result)):
            $user_id = $row['user_id'];
            $query = "SELECT username, image FROM users WHERE id = '$user_id' LIMIT 1";
            $result2 = mysqli_query($con, $query);
            $user_row = mysqli_fetch_assoc($result2);
    ?>
        <div style="background-color:white; display:flex; border:1px solid #aaa; border-radius:10px; margin:10px 0; padding:10px;">
            <div style="flex:1;text-align: center;">
                <img src="<?= htmlspecialchars($user_row['image']) ?>" style="border-radius:50%;margin:10px;width:100px;height:100px;object-fit:cover;">
                <br><?= htmlspecialchars($user_row['username']) ?>
            </div>
            <div style="flex:8">
                <?php if (!empty($row['image'])): ?>
                    <div>
                        <img src="<?= htmlspecialchars($row['image']) ?>" style="width:100%; height:200px; object-fit:cover;">
                    </div>
                <?php endif; ?>
                <div>
                    <?= htmlspecialchars($row['post']) ?>
                    <div style="color: #888;"><?= date("jS M, Y", strtotime($row['date'])) ?></div>
                    <br><br>
                    <a href="profile.php?action=post_edit&id=<?= $row['id']; ?>"><button>Edit Post</button></a>
                    <a href="profile.php?action=post_delete&id=<?= $row['id']; ?>"><button>Delete Post</button></a>
                </div>
            </div>
        </div>
    <?php endwhile; endif; ?>
<?php endif; ?> <!-- closes main if chain -->

</div>

<?php require "footer.php"; ?>
</body>
</html>