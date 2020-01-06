<?php

define('DB_PATH', __DIR__ . '/../data/database.db');

class DB
{
    private static $pdo;

    public static function init() {
        self::$pdo = new PDO("sqlite:".DB_PATH);
    }

    public static function exec($sql = '') {
        self::$pdo->exec($sql);
        return true;
    }

    public static function run($sql = '', $params = []) {
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return true;
    }

    public static function lastId() {
        return self::$pdo->lastInsertId();
    }

    public static function get($sql = '', $params = []) {
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function all($sql = '', $params = []) {
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

DB::init();

if (!file_exists(__DIR__ . '/../install.lock')) {
  DB::exec('CREATE TABLE IF NOT EXISTS "book" (
    "id"	INTEGER PRIMARY KEY AUTOINCREMENT,
    "marc_no"	TEXT,
    "isbn"	TEXT,
    "cover_url"	TEXT,
    "cover_filename"	TEXT
  )');
  touch(__DIR__. '/../install.lock');
}
