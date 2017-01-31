<?php

namespace Models;


use Longman\TelegramBot\DB;

class AbstractModel
{
    const TABLE = null;
    const PRIMARY_KEY = 'id';
//    public static function get($condition = '', array $params = []) {
//        if (empty($condition)) {
//            return null;
//        }
//        if (is_numeric($condition)) {
//            $pk = static::PRIMARY_KEY;
//            $pkVal = $condition;
//            $condition = "{$pk} = :{$pk}";
//            $params = [$pk => $pkVal];
//        }
//        $table = static::TABLE;
//        Db::getPdo()->query("SELECT * FROM $table where ");
//    }

    protected function assign($data){
        foreach ($data as $k => $v) {
            if (property_exists(get_class($this), $k)) {
                $this->{$k} = $v;
            } else {
                throw new \Exception("Property $k does not exist");
            }
        }

        return $this;
    }
}