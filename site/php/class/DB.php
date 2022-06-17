<?php
class DB extends PDO {
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
        {
            try{
                self::$instance = new PDO(PDO_dsn,PDO_username,PDO_password);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch (PDOException $e){
                die($e->getMessage());
            }
        }

        return self::$instance;
    }

}

