<?php
$requiredApacheModules = ["mod_rewrite"];
$requiredPhpModules = ["curl","mysql"];
$apacheModules = apache_get_modules();
$enabledPhpModules = [];
$enabledApacheModules = [];

for ($i=0;$i<sizeof($requiredApacheModules);$i++){
    $isAvailable = false;
    for ($j=0;$j<sizeof($apacheModules);$j++){
        if ($requiredApacheModules[$i] === $apacheModules[$j]){
            $isAvailable = true;
        }
    }
    array_push($enabledApacheModules, $isAvailable);
}

for ($i=0;$i<sizeof($requiredPhpModules);$i++){
    $isAvailable = extension_loaded ($requiredPhpModules[$i]);
    array_push($enabledPhpModules, $isAvailable);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Page Title</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="main.css" />
</head>
<body>
    <p>Following Apache modules are required to be enabled</p>
    <ul>
    <?php
        for ($i=0;$i<sizeof($requiredApacheModules);$i++){
            echo "<li>$requiredApacheModules[$i] : $enabledApacheModules[$i]</li>";
        }
    ?>
    </ul>

    <p>Following PHP modules are required to be enabled</p>
    <ul>
    <?php
        for ($i=0;$i<sizeof($requiredPhpModules);$i++){
            echo "<li>$requiredPhpModules[$i] : $enabledPhpModules[$i]</li>";
        }
    ?>
    </ul>
</body>
</html>