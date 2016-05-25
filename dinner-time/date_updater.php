<?php
include dirname(__FILE__) . '/links.inc';

/*
You will get 'pk', 'name' and 'value' in $_POST array.
*/
$name  = $_POST['name'];
$value = $_POST['value'];

file_put_contents('./logs/log', var_export($_POST, true), FILE_APPEND);

/*
 Check submitted value
*/
if(!empty($value)) {
    $sql = "UPDATE `people`
            SET `attendance` = DATE('".$value."')
            WHERE `name` = '$name'";
    $res = $db->query($sql);

    header('HTTP/1.1 200 OK', true, 200);
} else {
    header('HTTP/1.0 400 Bad Request', true, 400);
    print 'I died :[';
}
?>
