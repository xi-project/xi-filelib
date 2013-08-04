<?php

use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Authorization\AccessDeniedException;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../renderer-common.php';

$folder = $filelib->getFolderOperator()->createByUrl('pictures/of/very beaütiful manatees');

try {
    $file = $filelib->upload(__DIR__ . '/../manatees/manatus-12.jpg', $folder);

} catch (AccessDeniedException $e) {

    echo  $e->getMessage();
    die();

}

?>

<html>
    <head>
        <title>Mighty manatee</title>
    </head>
    <body>
        <h1>You just renderer a picture of a manatee and a cinemascope thumbnail. Aww!!!</h1>

        <p>
            <a href="renderer-backend.php?id=<?php echo $file->getId(); ?>&version=original&download=true">Download the original file</a>
        </p>


        <p>
            <img src="renderer-backend.php?id=<?php echo $file->getId(); ?>&version=original" />
        </p>

        <p>
            <img src="renderer-backend.php?id=<?php echo $file->getId(); ?>&version=cinemascope" />
        </p>

        <p>
            <img src="renderer-backend.php?id=<?php echo $file->getId(); ?>&version=nonexistant" />
        </p>

    </body>
</html>
