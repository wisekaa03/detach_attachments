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

$labels['deleteconfirmation']='If you proceed the attachment will be removed from the server permanently. Do you want to proceed?';
$labels['downloadquestion']='Do you want to download the attachment before deletion?';
$labels['downloads']='download<small>(s)</small>';
$labels['hint']='The download count indicates how many times attachments have been downloaded.<br />If attachments were sent by error, please delete them to prevent further downloads.';
$labels['storeattachments']='Keep attachments on the server';
$labels['cumsize']='total attachments size';

$labels['pluginname']='Отсоединение вложений';
$labels['plugindescription']='Отсоединяет вложения в исходящих сообщениях, добавляя в сообщение ссылку на скачивание вложения. Пожалуйста, обратите внимание: Ссылки на откреплённые вложения, будут недоступны (устареют), через 30 дней, после создания сообщения.';
$labels['expires']='Ссылка на загрузку до: ';
$labels['deletelink']='Удалить вложения с сервера';
$labels['storeattachments']='Хранить (оставить) вложения на сервере';
$labels['filenotfound']='Файл не найден';

$labels['hint']='<p>Количество загрузок данного вложения.<br />Если завершилось с ошибкой, то пожалуйста удалите вложение.</p><p>The download count indicates how many times attachments have been downloaded.<br />If attachments were sent by error, please delete them to prevent further downloads.</p>';
