<?php
/* Borrowed from Quip: https://github.com/splittingred/Quip/blob/develop/_build/setup.options.php */
/* set some default values */
$values = array(
    'mailTo' => 'my@emailhere.com',
    'mailFrom' => 'my@emailhere.com',
    'mailReplyTo' => 'my@emailhere.com',
);
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        $setting = $modx->getObject('modSystemSetting',array('key' => 'notify404.mailTo'));
        if ($setting != null) { $values['mailTo'] = $setting->get('value'); }
        unset($setting);

        $setting = $modx->getObject('modSystemSetting',array('key' => 'notify404.mailFrom'));
        if ($setting != null) { $values['mailFrom'] = $setting->get('value'); }
        unset($setting);

        $setting = $modx->getObject('modSystemSetting',array('key' => 'notify404.mailReplyTo'));
        if ($setting != null) { $values['mailReplyTo'] = $setting->get('value'); }
        unset($setting);
    break;
    case xPDOTransport::ACTION_UNINSTALL: break;
}

$output = '<label for="notify404-emailsTo">To Email:</label>
<input type="text" name="mailTo" id="notify404-mail-to" width="300" value="'.$values['mailTo'].'" />
<br /><br />

<label for="notify404-emailsFrom">From Email:</label>
<input type="text" name="mailFrom" id="notify404-mail-from" width="300" value="'.$values['mailFrom'].'" />
<br /><br />

<label for="notify404-emailsReplyTo">Reply-To Email:</label>
<input type="text"  name="mailReplyTo" id="notify404-mail-replyto" width="300" value="'.$values['mailReplyTo'].'" />';

return $output;
