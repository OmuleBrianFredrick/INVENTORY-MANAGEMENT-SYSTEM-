<?php
return ['default'=>env('QUEUE_CONNECTION','sync'),'connections'=>['sync'=>['driver'=>'sync']],'batching'=>['database'=>'sqlite'],'failed'=>['driver'=>'database-uuids','database'=>'sqlite','table'=>'failed_jobs']];
