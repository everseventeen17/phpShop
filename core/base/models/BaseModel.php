<?php

namespace core\base\models;

use core\base\exceptions\DbException;

class BaseModel
{
    use \core\base\controllers\Singleton;

    protected $db;

    private function __construct()
    {
        $this->db = @new \mysqli(HOST, USER, PASS, DB_NAME);
        if ($this->db->connect_error) {
            throw new DbException('Ошибка подключения к базе данных: ' . $this->db->connect_errno . ' ' . $this->db->connect_error);
        }
        $this->db->query("SET NAMES UTF8");
    }

    final public function query($query, $crud = 'r', $return_id = false)
    {
        $result = $this->db->query($query);
        if ($this->db->affected_rows === -1) {
            throw new DbException('Ошибка в SQL запросе ' . $query . ' - ' . $this->db->errno . ' ' . $this->db->error);
        }
        switch ($crud) {
            case 'r';
                if ($result->num_rows) {
                    $res = [];
                    for ($i = 0; $i < $result->num_rows; $i++) {
                        $res[] = $result->fetch_assoc();
                    }
                    return $res;
                }
                return false;
                break;
            case 'c';
                if ($return_id) $this->db->insert_id;
                return true;
                break;
            default:
                return true;
                break;
        }
    }

    final public function get($table, $set = [])
    {
        $fields = $this->createFields($table, $set);
        $order = $this->createOrder($table, $set);

        $where = $this->createWhere($table, $set);

        $join_arr = $this->createJoin($table, $set);

        $fields .= $join_arr['fields'];
        $join = $join_arr['fields'];
        $where .= $join_arr['where'];
        $fields = rtrim($fields, ',');

        $limit = $set['limit'] ? $set['limit'] : '';

        $query = "SELECT $fields FROM $table $join $where $order $limit";
        return $this->query($query);
    }

    protected function createFields($table, $set)
    {
        $set['fields'] = (is_array($set['fields']) and !empty($set['fields'])) ? $set['fields'] : ['*'];
        $table = $table ? $table . '.' : '';
        $fields = '';
        foreach ($set['fields'] as $field) {
            $fields .= $table . $field . ',';
        }
        return $fields;
    }

    protected function createOrder($table, $set)
    {
        $table = $table ? $table . '.' : '';
        $order_by = '';
        if (is_array($set['order']) and !empty($set['order'])) {
            $set['order_direction'] = (is_array($set['order_direction']) and !empty($set['order_direction'])) ? $set['order_direction'] : ['ASC'];
            $order_by = 'ORDER_BY ';
            $direct_count = 0;
            foreach ($set['order'] as $order) {
                if ($set['order_direction'][$direct_count]) {
                    $order_direction = strtoupper($set['order_direction'][$direct_count]);
                    $direct_count++;
                } else {
                    $order_direction = strtoupper($set['order_direction'][$direct_count - 1]);
                }
                $order_by .= $table . $order . " " . $order_direction . ",";
            }
            $order_by = rtrim($order_by, ',');
        }
        return $order_by;
    }

    protected function createWhere($table = false, $set, $instruction = 'WHERE')
    {
        $table = $table ? $table . '.' : '';
        $where = '';

        if (is_array($set['where']) and !empty($set['where'])) {
            $set['operand'] = (is_array($set['operand']) and !empty($set['operand'])) ? $set['operand'] : ['='];
            $set['condition'] = (is_array($set['condition']) and !empty($set['condition'])) ? $set['condition'] : ['AND'];

            $where = $instruction;
            $o_count = 0;
            $c_count = 0;
            foreach ($set['where'] as $key => $value) {
                $where .= ' ';
                if (!empty($set['operand'][$o_count])) {
                    $operand = $set['operand'][$o_count];
                    $o_count++;
                } else {
                    $operand = $set['operand'][$o_count - 1];
                }

                if (!empty($set['condition'][$c_count])) {
                    $condition = $set['condition'][$c_count];
                    $c_count++;
                } else {
                    $condition = $set['condition'][$c_count - 1];
                }

                if ($operand === 'IN' or $operand === 'NOT IN') {
                    if (is_string($value) and strpos($value, "SELECT")) {
                        $in_str = $value;
                    }else{
                        if(is_array($value)) $temp_item = $value;
                            else $temp_item = explode(',', $value);
                        $in_str = '';
                        foreach ($temp_item as $v){
                            $in_str .= "'" . trim($v) . "',";
                        }
                    }
                    $where .= $table . $key . ' ' . $operand . ' (' . trim($in_str, ',') . ') ' . $condition;
                }elseif(strpos($operand, 'LIKE') !== false ){
                    $like_template = explode('%', $operand);
                    foreach ($like_template as $lt_key => $lt){
                        if(!$lt){
                            if(!$lt_key){
                                $value = '%' . $value;
                            }else{
                                $value .= '%';
                            }
                        }
                    }
                    $where .= $table . $key . ' LIKE ' . "'" . $value . "' $condition";
                }else{
                    if(strpos($value, 'SELECT') === 0){
                        $where .= $table . $key . $operand . '(' . $value . ') ' . " $condition";
                    }else{
                        $where .= $table . $key . $operand . "'" . $value . "'" . " $condition";
                    }
                }
            }
            $where = substr($where, 0, strrpos($where, $condition));
        }
        return $where;
    }

}