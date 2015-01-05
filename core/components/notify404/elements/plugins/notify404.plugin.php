<?php
/*
* Notify404
*
* Copyright 2011 by Mark Hamstra <hello@markhamstra.com>
 *
 * This plugin is part of Notify404, an email notification utility for 404 errors.
 *
 * Notify404 is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Notify404 is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * VersionX; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 */

/* @var modX $modx
 * @var array $scriptProperties
 */

/* Set up some variables */
$mailto = $modx->getOption('notify404.mailTo',null, $modx->getOption('emailsender'));
$mailfrom = $modx->getOption('notify404.mailFrom',null, $modx->getOption('emailsender'));
$replyto = $modx->getOption('notify404.mailReplyTo',null, $modx->getOption('emailsender'));
$mailchunk = $modx->getOption('notify404.mailTemplate',null, 'notifyDefaultTpl');
$filterchunk = $modx->getOption('notify404.filter',null, 'notifyDefaultFilter');

if (empty($mailto)) {
    $modx->log(modX::LOG_LEVEL_ERROR,'[Notify404] Error: mailto not specified for Notify404. Go to System Settings, find the Notify404 namespace and add the right email to notify to the notify404.mailTo setting.');
    return;
}

/* Auto-ban using RAMPART when requesting a known hack */
$banned = false;
if ($modx->getOption('notify404.autoban',null,true)) {
    $knownhacks = array(
        'assets/snippets/reflect/snippet.reflect.php?reflect_base=', 'muieblackcat',
        'admin/pma/index.php', 'admin/phpmyadmin/index.php', 'dbadmin/index.php', 'myadmin/index.php', 'mysql/index.php',
        'mysqladmin/index.php', 'typo3/phpmyadmin/index.php', 'phpadmin/index.php', 'phpmyadmin1/index.php', 'phpmyadmin2/index.php',
        'xampp/phpmyadmin/index.php', 'websql/index.php', 'phpmyadmin/index.php', '/admin', '/FCKeditor'
    );
    foreach ($knownhacks as $needle) {
        if (stripos($_SERVER['REQUEST_URI'],$needle)) {
            $block = true;
    
            $modelPath = $modx->getOption('rampart.core_path',null,$modx->getOption('core_path').'components/rampart/').'model/';
            $rampart = $modx->getService('rampart','Rampart',$modelPath.'rampart/');
            if ($rampart instanceof Rampart) {
                /* @var Rampart $rampart */
                $ban = array(
                    Rampart::STATUS => Rampart::STATUS_BANNED,
                    Rampart::REASON => '',
                    Rampart::IP => $_SERVER['REMOTE_ADDR'],
                    Rampart::HOSTNAME => gethostbyaddr($_SERVER['REMOTE_ADDR']),
                    Rampart::EMAIL => '',
                    Rampart::USERNAME => '',
                    Rampart::USER_AGENT => $_SERVER['HTTP_USER_AGENT'],
                    Rampart::EXPIRATION	=> $modx->getOption('notify404.autoban.expiration',null,5), //days
                    Rampart::REASON => 'Hack attempt found by Notify404. Known hack attempt '.$needle.' in request '. $_SERVER['REQUEST_URI'],
                    Rampart::SERVICE => 'Notify404',
                );
                
                /* Rampart uses $modx->resource->get('id'), so we need to prevent that from breaking */
                $modx->resource = $modx->getObject('modResource',$modx->getOption('error_page'));
                if ($rampart->addBan($ban)) {
                    $banned = true;
                }
            }
            break;
        }
    }
}

/* Filter based on user rules */
$filterText = $modx->getChunk($filterchunk);
$filterLines = explode("\n", $filterText);
$haystack = '';

$modx->log(modX::LOG_LEVEL_DEBUG,'[Notify404] Raw filter is ' . $filterText);

if(is_array($filterLines)){
    foreach ($filterLines as $line) {
        $filter = explode(':' , $line);

        $modx->log(modX::LOG_LEVEL_DEBUG,'[Notify404] Processing line ' . var_export($filter, true));

        switch (strtolower($filter[0])){
            case 'host':
                $haystack = 'http' . ($_SERVER['HTTPS'] ? 's' : null) . '://' . $_SERVER['HTTP_HOST'];
                break;
            case 'ip':
                $haystack = $_SERVER['REMOTE_ADDR'];
                break;
            case 'url':
                $haystack = $_SERVER['REQUEST_URI'];
                break;
            case 'ua':
                $haystack = $_SERVER['HTTP_USER_AGENT'];
                break;
            default:
                $modx->log(modX::LOG_LEVEL_ERROR,'[Notify404] An error occurred processing the filter: ' . $line);
        }

        if (strpos($haystack, trim($filter[1])) > 0) {
            $modx->log(modX::LOG_LEVEL_DEBUG,'[Notify404] Filter match, not sending email. Filter was: ' . $line
                . ', matched on: ' . $haystack);
            return;
        }
    }
}
/* Prepare the data */
$phs = array();
$phs['ip'] = $_SERVER['REMOTE_ADDR'];
$phs['host'] = 'http' . ($_SERVER['HTTPS'] ? 's' : null) . '://' . $_SERVER['HTTP_HOST'];
$phs['request'] = $_SERVER['REQUEST_URI'];
$phs['timestamp'] = date('d M Y - G:i:s');
$phs['referer'] = ($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Not available.';
$phs['user_agent'] = ($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Not available.';
$phs['banned'] = $banned;

/* Set the message */
$message = $modx->getChunk($mailchunk,$phs);
if (empty($message)) {
    $modx->log(modX::LOG_LEVEL_ERROR,'[Notify404] Error: empty message. Most likely the Notify404 template chunk does not exist or hasn\'t been set.');
}

/* Send the email */
$mail = $modx->getService('mail', 'mail.modPHPMailer');

$mail->set(modMail::MAIL_BODY, $message);
$mail->set(modMail::MAIL_FROM, $mailfrom);
$mail->set(modMail::MAIL_FROM_NAME, 'Notify404');
$mail->set(modMail::MAIL_SUBJECT, '[Notify404] Not found: ' . $phs['request']);

$mail->address('to', $mailto);
$mail->address('reply-to', $replyto);
$mail->setHTML(true);
if (!$mail->send()) {
    $modx->log(modX::LOG_LEVEL_ERROR,'[Notify404] An error occurred while trying to send a 404 Page Not Found notification email.');
    $modx->log(modX::LOG_LEVEL_ERROR,'[Notify404] ' . $mail->mailer->ErrorInfo);
}
$mail->reset();

return;
