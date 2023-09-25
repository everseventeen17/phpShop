<?php

namespace core\base\models;

abstract class BaseModelMethods
{
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
            $order_by = 'ORDER BY ';
            $direct_count = 0;
            foreach ($set['order'] as $order) {
                if (!empty($set['order_direction'][$direct_count])) {
                    $order_direction = strtoupper($set['order_direction'][$direct_count]);
                    $direct_count++;
                } else {
                    $order_direction = strtoupper($set['order_direction'][$direct_count - 1]);
                }
                if (is_int($order)) $order_by .= $order . ' ' . $order_direction . ",";
                else $order_by .= $table . $order . " " . $order_direction . ",";
            }
            $order_by = rtrim($order_by, ',');
        }
        return $order_by;
    }

    protected function createWhere($set, $table = false, $instruction = 'WHERE')
    {
        $table = $table ? $table . '.' : '';
        $where = '';

        if (is_array($set['where']) and !empty($set['where'])) {
            @$set['operand'] = (is_array($set['operand']) and !empty($set['operand'])) ? $set['operand'] : ['='];
            @$set['condition'] = (is_array($set['condition']) and !empty($set['condition'])) ? $set['condition'] : ['AND'];

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
                    if (is_string($value) and strpos($value, "SELECT") === 0) {
                        $in_str = $value;
                    } else {
                        if (is_array($value)) $temp_item = $value;
                        else $temp_item = explode(',', $value);
                        $in_str = '';
                        foreach ($temp_item as $v) {
                            $in_str .= "'" . addslashes(trim($v)) . "',";
                        }
                    }
                    $where .= $table . $key . ' ' . $operand . ' (' . trim($in_str, ',') . ') ' . $condition;
                } elseif (strpos($operand, 'LIKE') !== false) {
                    $like_template = explode('%', $operand);
                    foreach ($like_template as $lt_key => $lt) {
                        if (!$lt) {
                            if (!$lt_key) {
                                $value = '%' . $value;
                            } else {
                                $value .= '%';
                            }
                        }
                    }
                    $where .= $table . $key . ' LIKE ' . "'" . addslashes($value) . "' $condition";
                } else {
                    if (strpos($value, 'SELECT') === 0) {
                        $where .= $table . $key . $operand . '(' . $value . ') ' . " $condition";
                    } else {
                        $where .= $table . $key . $operand . "'" . addslashes($value) . "'" . " $condition";
                    }
                }
            }
            $where = substr($where, 0, strrpos($where, $condition));
        }
        return $where;
    }

    protected function createJoin($table, $set, $new_where = false)
    {
        $fields = '';
        $join = '';
        $where = '';
        if (!empty($set['join'])) {
            $join_table = $table;
            foreach ($set['join'] as $key => $item) {
                $a = $key;
                if (is_int($key)) {
                    if (!isset($item['table'])) continue;
                    else $key = $item['table'];
                }
                if ($join) $join .= ' ';

                if (!empty($item['on'])) {

                    $join_fields = [];
                    switch (2) {

                        case isset($item['on']['fields']) && count($item['on']['fields']):
                            $join_fields = $item['on']['fields'];
                            break;
                        case count($item['on']) :
                            $join_fields = $item['on'];
                            break;
                        default:
                            continue 2;
                            break;
                    }
                    if (!empty($item['type'])) $join .= 'LEFT JOIN ';
                    else $join .= trim(strtoupper($item['type'])) . ' JOIN ';

                    $join .= $key . ' ON ';
                    if ($item['on']['table']) $join .= $item['on']['table'];
                    else $join .= $join_table;

                    $join .= '.' . $join_fields[0] . '=' . $key . '.' . $join_fields[1];
                    $join_table = $key;
                    if ($new_where) {
                        if ($item['where']) {
                            $new_where = false;
                        }
                        $group_condition = 'WHERE';

                    } else {
                        $group_condition = isset($item['group_condition']) ? strtoupper($item['group_condition']) : 'AND';
                    }
                    $fields .= $this->createFields($key, $item);
                    $where .= $this->createWhere($item, $key, $group_condition);
                }
            }
        }
        return compact('fields', 'where', 'join');
    }

    protected function createInsert($fields, $files, $except)
    {
        if (empty($fields)) {
            $fields = $_POST;
        }
        $insert_arr = [];
        if ($fields) {
            $sql_func = ['NOW()'];
            foreach ($fields as $row => $value) {
                if ($except and in_array($row, $except)) continue;
                @$insert_arr['fields'] .= $row . ',';
                if (in_array($value, $sql_func)) {
                    $insert_arr['values'] .= $value . ",";
                }else{
                    @$insert_arr['values'] .= "'" . addslashes($value) . "',";
                }
            }
        }
        if (!empty($files)) {
            foreach ($files as $row => $file) {
                $insert_arr['fields'] .= $row . ',';
                if (is_array($file)) {
                    $insert_arr['values'] .= "'" . addslashes(json_encode($file)) . "',";
                } else {
                    $insert_arr['values'] .= "'" . addslashes($file) . "',";
                }
            }
        }
        if ($insert_arr) {
            foreach ($insert_arr as $key => $arr) $insert_arr[$key] = rtrim($arr, ',');
        }
        return $insert_arr;
    }
}