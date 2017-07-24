<?php
# 
# This file is part of Roundcube "detach_attachments" plugin.
# 
# Your are not allowed to distribute this file or parts of it.
# 
# This file is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# 
# Copyright (c) 2012 - 2013 Roland 'Rosali' Liebl - all rights reserved.
# dev-team [at] myroundcube [dot] com
# http://myroundcube.com
# 

$labels=array();
$labels['pluginname']='Detach Attachments';
$labels['plugindescription']='Detach outgoing email attachments sending a download link instead. Please note: Detached attachments\' download links have a 30 days expiration date.';
$labels['expires']='Download expires';
$labels['deleteconfirmation']='If you proceed the attachment will be removed from the server permanently. Do you want to proceed?';
$labels['downloadquestion']='Do you want to download the attachment before deletion?';
$labels['deletelink']='Delete attachment from server';
$labels['downloads']='download<small>(s)</small>';
$labels['hint']='The download count indicates how many times attachments have been downloaded.<br />If attachments were sent by error, please delete them to prevent further downloads.';
$labels['storeattachments']='Keep attachments on the server';
$labels['cumsize']='total attachments size';
$labels['filenotfound']='File not found.';