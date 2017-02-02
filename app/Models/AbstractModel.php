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

    public function save(array $fields)
    {
        if (empty($fields[self::PRIMARY_KEY]) ? $this->insert($fields) : $this->update($fields)) {
            return $this;
        } else {
            throw new \Exception('�� ������� ��������� ������ '.static::class);
        }
    }

    private function insert($fields)
    {
        $tableName = static::TABLE;
        $fieldNames = implode(', ', array_keys($fields));
        $fieldPlaceholders = implode(', ', array_map(function($v){return ':'.$v;}, array_keys($fields)));
        $sql = "INSERT INTO $tableName ($fieldNames) VALUES ($fieldPlaceholders)";
        $stmt = Db::getPdo()->prepare($sql);
        $stmt->execute($fields);
        $this->setId((int)Db::getPdo()->lastInsertId());
        return true;
    }
    private function update($fields)
    {
        return $this;
    }

    private function setId($param)
    {
        $this->{self::PRIMARY_KEY} = $param;
    }
}