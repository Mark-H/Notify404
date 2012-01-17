<?php
$s = array(
    'mailTo' => '',
    'mailFrom' => '',
    'mailReplyto' => '',
    'mailTemplate' => 'notifyDefaultTpl',
    'autoban' => true,
    'autoban.expiration' => 5,
);

$settings = array();

foreach ($s as $key => $value) {
    if (is_string($value) || is_int($value)) { $type = 'textfield'; }
    elseif (is_bool($value)) { $type = 'combo-boolean'; }
    else { $type = 'textfield'; }

    $settings['bdlistings.'.$key] = $modx->newObject('modSystemSetting');
    $settings['bdlistings.'.$key]->set('key', 'notify404.'.$key);
    $settings['bdlistings.'.$key]->fromArray(array(
        'value' => $value,
        'xtype' => $type,
        'namespace' => 'notify404',
        'area' => 'Default'
    ));
}

return $settings;


