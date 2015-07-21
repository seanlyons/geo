<?php

$orig = json_decode(file_get_contents('sf.json'), TRUE);

$features = $orig['features'];

$container = array();
foreach ($features as $k => $v) {
    echo $k;
    $container[$k] = array();
    $container[$k]['type'] = "FeatureCollection";
    $container[$k]['features'] = array($v);
}
echo '<pre>';
echo json_encode($container, JSON_PRETTY_PRINT);
echo '</pre>';
echo '!debug!3';