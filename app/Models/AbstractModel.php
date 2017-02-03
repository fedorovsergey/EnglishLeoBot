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

    protected static $_fields = [];

    protected function assign($data){
        foreach ($data as $k => $v) {
            $className = get_class($this);
            if (property_exists($className, $k)) {
                $this->{$k} = $v;
            } else {
                throw new Exception("Property `$k` does not exist in $className");
            }
        }

        return $this;
    }

    public function save()
    {
        $fields = $this->getRawData();
        if (empty($fields[self::PRIMARY_KEY]) ? $this->insert($fields) : $this->update($fields)) {
            return $this;
        } else {
            throw new \Exception('Не удалось сохранить объект '.static::class);
        }
    }

    public static function getFields()
    {
        return static::$_fields;
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

    private function getRawData()
    {
        $data = [];
        foreach (static::getFields() as $field) {
            $data[$field] = $this->{$field};
        }
        return $data;
    }
}