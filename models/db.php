<?php

function getPDO() {
    $dbPath = __DIR__ . '/../Db/FRDb.db';
    return new PDO("sqlite:" . $dbPath);
}
