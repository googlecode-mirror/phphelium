<?php

/*
 * emailer.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Send queued e-mails by cron script
 *          recommended cron available in the root directory of this code (file: cron)
 */

require_once('../src/core/DB.php');

// Load initialization settings
$ini = parse_ini_file('../settings.ini',true);
if (!empty($ini['database'])) {
    define('MASTER_DB_STRING',$ini['database']['master']);
    define('SLAVE_DB_STRING',$ini['database']['slave']);
    define('DEFAULT_DB',$ini['database']['default']);
}

if (!empty($ini['database'])) {
    $db = new DB($ini['database']['slave']);

    // Grab all unsent e-mails...
    $sql = 'SELECT * FROM email_queue WHERE sent_date IS NULL;';
    $results = $db->getAll($sql);
    foreach($results as $row) {
        $headers  = 'MIME-Version: 1.0'."\r\n";
        $headers .= 'Content-type: text/html'."\r\n";
        $headers .= 'From: '.$row->sender."\r\n";

        mail($row->recipient,$row->subject,$row->content,$headers);

        // Update the database and mark e-mail as sent.
        $sql = 'UPDATE email_queue SET sent_date = NOW(), is_active = 0 WHERE email_queue_id = '.$row->email_queue_id.';';
        $db->update($sql);
    }
}

?>