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
    /* Please fill these variables with the required configuration */
    // E-mail address to send the email to.
    $mailto = '';
    // E-mail address to use as the "from" field.
    $mailfrom = 'noreply@site.com';
    // E-mail address to send along as the "reply-to" email.
    $replyto = 'noreply@site.com';
    // Chunk name to use as email template. notifyTpl is default.
    $mailchunk = 'notifyTpl';

    /* Unless you know PHP and want to modify this plugin, do not edit below */
    /*************************************************************************/
    if ($mailto == '') {
        $modx->log(modX::LOG_LEVEL_ERROR,'Error: mailto not specified for Notify404');
        return;
    }

    $phs['ip'] = $_SERVER['REMOTE_ADDR'];
    $phs['host'] = 'http'. ($_SERVER['HTTPS'] ? 's' : null) .'://'. $_SERVER['HTTP_HOST'];
    $phs['request'] = $_SERVER['REQUEST_URI'];
    $phs['timestamp'] = date('d M Y - G:i:s');
    $phs['referer'] = ($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Not available.';
    $phs['user_agent'] = ($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Not available.';

    if (strstr($phs['request'],'reflect')) { return; }
    $message = $modx->getChunk($mailchunk,$phs);
    if ($message == '') {
        $message = '<p>This is an automated email notification of a 404 error that has occured on the [[++site_name]] website.
         
        <strong>Date & time</strong>: [[+timestamp]]
        <strong>Requested</strong>: [[+request]]
        <strong>Host</strong>: [[+host]]
        <strong>Visitor IP</strong>: [[+ip]]
        <strong>Referer</strong>: [[+referer]]
        </strong>User agent</strong>: [[+user_agent]]
         
        These automatic notifications can be disabled by disabling the Notify404 plugin in your [[++site_name]] manager.
        Sent by Notify404 from [[++site_url]]</p>';
    }

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
        $modx->log(modX::LOG_LEVEL_ERROR,'An error occurred while trying to send the email: ');
    }
    $modx->mail->reset();
?>
