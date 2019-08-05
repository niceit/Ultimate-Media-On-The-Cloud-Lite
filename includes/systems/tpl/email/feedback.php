<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ultimate Media On The Cloud</title>
</head>
<body>
    <h1>You got a feedback message</h1>
    <ul>
        <li>Name: <?php echo $data['name'] ?></li>
        <li>Email:  <?php echo $data['email'] ?></li>
        <li>Site URL:  <?php echo $site_url ?> </li>
        <li>Type:  <?php echo $data['type'] ?></li>
        <li>Subject:  <?php echo $data['subject'] ?></li>
        <li>Body:  <?php echo $data['body'] ?></li>
    </ul>
    <p>Ultimate Media On The Cloud Version: <?php echo $plugin_version ?> - <?php echo $plugin_release ?></p>
</body>
</html>