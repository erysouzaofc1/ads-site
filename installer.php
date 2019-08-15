<?php

// All relevant changes can be made in the data file. Please read the docs: https://github.com/flokX/devShort/wiki

$success = false;
$config_path = implode(DIRECTORY_SEPARATOR, array(__DIR__, "admin", "config.json"));
$config_content = json_decode(file_get_contents($config_path), true);

if ($config_content["installer"]["password"]) {

    // Create root .htaccess with the rewrite rules
    $installation_path = rtrim($_SERVER["REQUEST_URI"], "installer.php");
    $root_htaccess = "

# The entrys below were set by the installer.

# Rewrite rule to get the short URLs
RewriteEngine On
RewriteBase $installation_path
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ {$installation_path}redirect.php?short=$1 [L]";
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . ".htaccess", $root_htaccess, FILE_APPEND);

    // Create the .htpasswd for the secure directory. If already a hashed password is there, copy it.
    $htpasswd_path = implode(DIRECTORY_SEPARATOR, array(__DIR__, "admin", ".htpasswd"));
    $admin_password = $config_content["installer"]["password"];
    if (password_get_info($admin_password)["algo"] === 0) {
        $hash = password_hash($admin_password, PASSWORD_DEFAULT);
    } else {
        $hash = $admin_password;  
    }
    file_put_contents($htpasswd_path, $config_content["installer"]["username"] . ":" . $hash);

    // Create the .htaccess for the secure directory.
    $secure_htaccess = "# Authentication
AuthType Basic
AuthName \"devShort admin area\"
AuthUserFile $htpasswd_path
require valid-user";
    file_put_contents(implode(DIRECTORY_SEPARATOR, array(__DIR__, "admin", ".htaccess")), $secure_htaccess);

    // Change password entry to the hash and remove installer file.
    $config_content["installer"]["password"] = $hash;
    file_put_contents($config_path, json_encode($config_content, JSON_PRETTY_PRINT));
    unlink(__DIR__ . DIRECTORY_SEPARATOR . "installer.php");
    $success = true;

}

?>

<!doctype html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex, nofollow">
    <meta name="author" content="The devShort team">
    <link rel="icon" href="assets/icon.png">
    <title>Installer | devShort</title>
    <link href="assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="assets/main.css" rel="stylesheet">
</head>

<body class="d-flex flex-column h-100">

    <main role="main" class="flex-shrink-0">
        <div class="container">
            <nav class="mt-3" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">devShort</li>
                    <li class="breadcrumb-item active" aria-current="page">Installer</li>
                </ol>
            </nav>
            <?php

            if ($success) {
                echo "<h1 class=\"mt-5\">Successful installed!</h1>
<p class=\"lead\">Now you can start to shorten links. For more information visit the <a href=\"https://github.com/flokX/devShort/wiki\">devShort wiki</a>.</p>
<a href=\"admin\" class=\"btn btn-primary btn-lg \" role=\"button\">Go to the admin panel</a>";
            } else {
                echo "<h1 class=\"mt-5\">Error while installing.</h1>
<p class=\"lead\">Please configure the <i>config.json</i> as shown in the <a href=\"https://github.com/flokX/devShort/wiki/Installation#installation\">devShort wiki</a> and try again.</p>
<p>We assume that you have not yet set an admin password.</p>";
            }

            ?>
        </div>
    </main>

    <footer class="footer mt-auto py-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted">&copy; <?php echo date("Y") ?> <a href="https://github.com/flokX/devShort">devShort</a></span>
                <span class="text-muted"><a href="https://github.com/flokX/devShort/wiki" class="badge badge-secondary">devShort wiki</a> <a href="https://github.com/flokX" class="badge badge-secondary">devShort author</a></span>
            </div>
        </div>
    </footer>

</body>

</html>
