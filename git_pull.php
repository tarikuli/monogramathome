<?php

// Show all information, defaults to INFO_ALL
//putenv('PATH='. getenv('PATH') .':/var/www/devmonogramathome.monogramonline.com/public_html/');
exec('git pull /var/www/www.monogramathome.com/  2>&1', $output);
echo "<pre>";
print_r($output); 
echo "</pre>";


?>