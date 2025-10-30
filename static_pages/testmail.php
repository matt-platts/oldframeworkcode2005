<?php

$to="amilnejr@sbcglobal.net";
$to="mattplatts@gmail.com";
$from='"Gonzo Multimedia Support" <help@gonzomultimedia.co.uk>';
$subject="Test Message from Gonzo Multimedia";
$message="Hi,\n\nThis is a test message from gonzomultimedia.co.uk. If your spam filters are set to junk/trash our emails this message may turn up in the appropriate folder.\n\nThe email addresses used to send email from our site are:\norders@gonzomultimedia.co.uk\nsales@gonzomultimedia.co.uk\nno-reply@gonzomultimedia.co.uk\nmailinglist@gonzomultimedia.co.uk\n\nYou should whitelist these email addresses in order to guarantee that mail sent from our server reaches your inbox.\n\nThanks,\nMatt Platts\nGonzo Multimedia Support.";
$headers="From: $from\r\nReply-To: $from\r\nContent-type:text/plain\r\n";

mail($to,$subject,$message,$headers);

print "Mail sent to $to\n";

?>
