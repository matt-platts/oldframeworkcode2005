<?php

$dbinit="../../init_database.php";
include_once($dbinit);

$imgsrc=$_GET['imgsrc'];
$imgtit=$_GET['imgtitle'];

?>
<html>
<head>
<title>Pixlr Editor</title>
<script type="text/javascript" src="scripts/pixlr.js"></script>
<script type="text/javascript">
<!--

function sendToPixlr(imgSrc,imgTitle){

pixlr.edit({
        image: imgSrc,
        title: imgTitle,
        target:'<?=HTTP_PATH?>/apps/pixlr/pixlr_save.php',
        service: 'editor',
        method: 'GET',
        exit:'<?=HTTP_PATH;?>/apps/pixlr/pixlr.php'
        });
}

//-->
</script>

</head>
<body onLoad="sendToPixlr('<?php echo $imgsrc;?>','<?php echo $imgtit;?>')">
Loading image editor... 
</body>
</html>
