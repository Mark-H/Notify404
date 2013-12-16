<?php
$chunks = array();
$chunks[1]= $modx->newObject('modChunk');
$chunks[1]->fromArray(array(
    'id' => 1,
    'name' => 'notifyDefaultTpl',
    'description' => '',
    'snippet' => file_get_contents($sources['source_core'].'/elements/chunks/notifytpl.chunk.tpl'),
    'properties' => '',
),'',true,true);

$chunks[2]= $modx->newObject('modChunk');
$chunks[2]->fromArray(array(
    'id' => 1,
    'name' => 'notifyDefaultFilter',
    'description' => '',
    'snippet' => file_get_contents($sources['source_core'].'/elements/chunks/notifydefaultfilter.chunk.tpl'),
    'properties' => '',
),'',true,true);

foreach ($chunks as $ch) {
    $attributes= array(
        xPDOTransport::UNIQUE_KEY => 'name',
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::RELATED_OBJECTS => false,
    );
    
    $vehicle = $builder->createVehicle($ch, $attributes);
    $builder->putVehicle($vehicle);
}
return true;
