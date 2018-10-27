<?php

$loader = require_once 'vendor/autoload.php';

$batchma = new \hdvianna\Batchma\BatchMailer();
$batchma->execute();