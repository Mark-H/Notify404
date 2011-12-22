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
$mailto = $modx->getOption('notify404.mail.to',null, $modx->getOption('emailsender'));
$mailfrom = $modx->getOption('notify404.mail.from',null, $modx->getOption('emailsender'));
$replyto = $modx->getOption('notify404.mail.replyto',null, $modx->getOption('emailsender'));
$mailchunk = $modx->getOption('notify404.mail.template',null, 'notifyTpl');

if (empty($mailto)) {
    $modx->log(modX::LOG_LEVEL_ERROR,'[Notify404] Error: mailto not specified for Notify404. Go to System Settings, find the Notify404 namespace and add the email to notify to the notify404.mail.to setting.');
    return;
}

/* Prepare the data */
$phs = array();
$phs['ip'] = $_SERVER['REMOTE_ADDR'];
$phs['host'] = 'http'. ($_SERVER['HTTPS'] ? 's' : null) .'://'. $_SERVER['HTTP_HOST'];
$phs['request'] = $_SERVER['REQUEST_URI'];
$phs['timestamp'] = date('d M Y - G:i:s');
$phs['referer'] = ($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Not available.';
$phs['user_agent'] = ($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Not available.';

/* Set the message */
$message = $modx->getChunk($mailchunk,$phs);
if (empty($message)) {
    $modx->log(modX::LOG_LEVEL_ERROR,'[Notify404] Error: empty message. Most likely the Notify404 template chunk does not exist or hasn\'t been set.');
    $message = 'A 404 error was encountered on the site, however Notify404 isn\'t configured properly and cannot provide you properly formatted data. <br /> '.print_r($phs,true);
}

/* Send the email */
$modx->getService('mail', 'mail.modPHPMailer');
$modx->mail->set(modMail::MAIL_BODY,$message);
$modx->mail->set(modMail::MAIL_FROM,$mailfrom);
$modx->mail->set(modMail::MAIL_FROM_NAME,'Notify404');
$modx->mail->set(modMail::MAIL_SENDER,'Notify404');
$modx->mail->set(modMail::MAIL_SUBJECT,'[Notify404] Not found: '.$phs['request']);
$modx->mail->address('to',$mailto);
$modx->mail->address('reply-to',$replyto);
$modx->mail->setHTML(true);
if (!$modx->mail->send()) {
    $modx->log(modX::LOG_LEVEL_ERROR,'[Notify404] An error occurred while trying to send a 404 Page Not Found notification email.');
}
$modx->mail->reset();
return '';

?>
