<?php
$plugins = array();

/* create the plugin object */
$plugins[0] = $modx->newObject('modPlugin');
$plugins[0]->set('id',1);
$plugins[0]->set('name','Notify404');
$plugins[0]->set('description','The plugin that does the magic of notifying you per email of 404\'s.');
$plugins[0]->set('plugincode', getSnippetContent($sources['source_core'] . '/elements/plugins/notify404.plugin.php'));
$plugins[0]->set('category', 0);

$events = include 'events.notify404.php';
if (is_array($events) && !empty($events)) {
    $plugins[0]->addMany($events);
    $modx->log(xPDO::LOG_LEVEL_INFO,'Packaged in '.count($events).' Plugin Events for Notify404.'); flush();
} else {
    $modx->log(xPDO::LOG_LEVEL_ERROR,'Could not find plugin events for Notify404!');
}
unset($events);

return $plugins;

?>
