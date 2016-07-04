<?php
$url = $_REQUEST['url'];
echo $json=file_get_contents($url);