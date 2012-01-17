<?php

$events = array();

$e = array('OnPageNotFound');

foreach ($e as $ev) {
    $events[$ev] = $modx->newObject('modPluginEvent');
    $events[$ev]->fromArray(array(
        'event' => $ev,
        'priority' => 99,
        'propertyset' => 0
    ),'',true,true);
}

return $events;


?>
