<?php

/**
 * folders_subscribe.php
 *
 * Copyright (c) 1999-2005 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Subscribe and unsubcribe from folders. 
 * Called from folders.php
 *
 * @version $Id: folders_subscribe.php,v 1.1 2005/06/14 13:34:58 indigoleopard Exp $
 * @package squirrelmail
 */

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
//define('SM_PATH','../');
define('SM_PATH','modules/squirrelmail-1.4.4/');
/* SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/display_messages.php');

/* globals */
sqgetGlobalVar('key',       $key,           SQ_COOKIE);
sqgetGlobalVar('username',  $username,      SQ_SESSION);
sqgetGlobalVar('onetimepad',$onetimepad,    SQ_SESSION);
sqgetGlobalVar('method',    $method,        SQ_GET);
sqgetGlobalVar('mailbox',   $mailbox,       SQ_POST);
/* end globals */

$location = get_location();

if (!isset($mailbox) || !isset($mailbox[0]) || $mailbox[0] == '') {
    header("Location: index.php?module=squirrelmail-1.4.4&action=folders");

    exit(0);
}

    $key = OneTimePadEncrypt("p1", $onetimepad);
$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);

if ($method == 'sub') {
    if($no_list_for_subscribe && $imap_server_type == 'cyrus') {
       /* Cyrus, atleast, does not typically allow subscription to
        * nonexistent folders (this is an optional part of IMAP),
        * lets catch it here and report back cleanly. */
       if(!sqimap_mailbox_exists($imapConnection, $mailbox[0])) {
          header("Location: index.php?module=squirrelmail-1.4.4&action=folders?success=subscribe-doesnotexist");
          sqimap_logout($imapConnection);
          exit(0);
       }
    }
    for ($i=0; $i < count($mailbox); $i++) {
        $mailbox[$i] = trim($mailbox[$i]);
        sqimap_subscribe ($imapConnection, $mailbox[$i]);
    }
    $success = 'subscribe';
} else {
    for ($i=0; $i < count($mailbox); $i++) {
        $mailbox[$i] = trim($mailbox[$i]);
        sqimap_unsubscribe ($imapConnection, $mailbox[$i]);
    }
    $success = 'unsubscribe';
}

sqimap_logout($imapConnection);
header("Location: index.php?module=squirrelmail-1.4.4&action=folders?success=$success");

?>
