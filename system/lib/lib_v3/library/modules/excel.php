<?php
/*
Excel Functions

The basic steps to create Excel streams from PHP are
1. Call xlsBOF()
2. Write contents into cells by either using xlsWriteNumber(), or
    xlsWriteLabel()
3. Call xlsEOF()

"echo" functions can be also replaced by "fwrite" functions to write
directly to the webserver instead of parsing the contents to the
browser.
*/


// ----- begin of function lib -----
// Excel begin of file header
function xlsBOF() {
    $sOut = pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
    return $sOut;
}
// Excel end of file footer
function xlsEOF() {
    $sOut = pack("ss", 0x0A, 0x00);
    return $sOut;
}
// Function to write a Number (double) into Row, Col
function xlsWriteNumber($Row, $Col, $Value) {
    $sOut = pack("sssss", 0x203, 14, $Row, $Col, 0x0);
    $sOut .= pack("d", $Value);
    return $sOut;
}
// Function to write a label (text) into Row, Col
function xlsWriteLabel($Row, $Col, $Value ) {
    $L = strlen($Value);
    $sOut = pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
    $sOut .= $Value;
    return $sOut;
}
?>
