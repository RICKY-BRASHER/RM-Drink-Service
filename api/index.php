<?php

// Redirection et configuration des répertoires temporaires pour le Serverless
require __DIR__ . '/../public/index.php';

$app->useStoragePath('/tmp/storage');
