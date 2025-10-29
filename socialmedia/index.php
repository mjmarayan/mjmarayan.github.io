<?php require "functions.php";

check_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media Website</title>
</head>
<body>
    
        <?php include "header.php";?>
           <div style="margin: auto; max-width: 600px;">
            <br>
            <h3 style="text-align: center;">Timeline</h3>

                <?php
                        $query = "select * from posts order by id desc limit 10";

                        $result = mysqli_query($con, $query);
                        ?>

                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php 
                                    $user_id = $row['user_id'];
                                    $query = "SELECT username, image FROM users WHERE id =  '$user_id' limit 1";
                                    $result2 = mysqli_query($con, $query);
                                    
                                    $user_row = mysqli_fetch_assoc($result2);
                                    
                                    ?>
                                    <div style="background-color:white; display:flex; border:1px solid #aaa; border-radius:10px; margin:10px 0; padding:10px;">
                                    <div style="flex:1;text-align: center;">
                                        <img src="<?= $user_row['image'] ?>" style="border-radius: 50%;margin:10px;width:100px; height:100px; object-fit:cover;">
                                        <br>
                                        <?= $user_row['username'] ?>
                                    </div>
                                    <div style="flex:8">
                                        <?php if (file_exists($row['image'])): ?>
                                            <div>
                                                <img src="<?= $row['image'] ?>" style="width:100%; height:200px; object-fit:cover;">
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <?php echo $row['post'] ?>
                                           <div style="color: #888;"><?= date("jS M, Y", strtotime($row['date'])) ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                    <?php endif; ?>
            </div>  
        <?php include "footer.php";?>

       
</body>
</html>