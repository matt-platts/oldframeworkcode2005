<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<meta name="author" content="Paragon Digital" />
<meta name="copyright" content="Matt Platts" />
<meta name="content-language" content="EN" />
<meta http-equiv="cache-control" content="no-cache" />
<title>Pixlr Demo</title>
<script type="text/javascript" src="scripts/pixlr.js"></script>

<script type="text/javascript">
<!--

function sendToPixlr(imgSrc,imgTitle){

pixlr.edit({
	image: imgSrc,
	title: imgTitle, 
	target:'http://www.paragon-digital.net/medico_dev2/apps/pixlr/pixlr_save.php', 
	service: 'editor',
	method: 'GET',
	exit:'http://www.paragon-digital.net/medico_dev2/apps/pixlr/pixlr.php'
	});
}

//-->
</script>

</head>
<body>


<a href="javascript:sendToPixlr('http://developer.pixlr.com/_image/example3.jpg','Test Image Title')">Edit image</a>
<p></p>


<a href="javascript:pixlr.edit({image:'http://developer.pixlr.com/_image/example3.jpg', title:'Example image 3', service:'express', target:'http://www.paragon-digital.net/dev/current/apps/pixlr/save_pixlr.php', exit:'http://www.paragon-digital.net/dev/current/apps/pixlr/pixlr.html'});"><img src="http://developer.pixlr.com/_image/example3_thumb.jpg" width="250" height="150" title="Edit in pixlr" />editit</a>
</body>
</html>

