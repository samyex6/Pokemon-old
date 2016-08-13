<?php

class Database {

    protected static $db;
    protected static $query_num;

    public static function connect($host, $user, $password, $database, $charset = 'utf8') {

        self::$db = new mysqli($host, $user, $password, $database);
        self::$db->query('SET NAMES ' . $charset);

        if(self::$db->connect_errno)
            echo 'Failed to connect to MySQL: (' . self::$db->connect_errno . ') ' . self::$db->connect_error;

    }

    public static function query($sql, $values = []) {
        $query = mysqli_query(self::$db, vsprintf($sql, $values));
        if(mysqli_errno(self::$db))
            echo 'Failed to connect to MySQL: (' . mysqli_errno(self::$db) . ') ' . mysqli_error(self::$db);
        self::$query_num++;
        return $query;
    }

    public static function fetch_first($sql, $values = []) {
        return self::fetch(self::query($sql, $values));
    }

    public static function fetch($resource, $type = MYSQLI_ASSOC) {
        return mysqli_fetch_array($resource, $type);
    }

    public static function result_first($sql, $values = []) {
        return zfetch(self::query($sql, $values), MYSQLI_NUM)[0];
    }

    public static function prepare($statement) {
        return mysqli_prepare(self::$db, $statement);
    }

    public static function affected_rows() {
        return mysqli_affected_rows(self::$db);
    }

    public static function get_query_num() {
        return self::$query_num;
    }

}

class DB extends Database {
}