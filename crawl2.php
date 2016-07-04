<?php
header("Content-type: application/json");

$request = $_REQUEST['name'];
#$decoded = json_decode($request, true);
echo $request;
