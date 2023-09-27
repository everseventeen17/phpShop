<?php

namespace core\base\models;

use core\base\exceptions\DbException;

class BaseModel extends BaseModelMethods
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

    /**
     * @param $query
     * @param $crud =  r - SELECT, c - INSERT, u - UPDATE, d - DELETE
     * @param $return_id
     * @return array|bool
     * @throws DbException
     */
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

    final function showColumns($table)
    {
        $query = "SHOW COLUMNS FROM $table";
        $res = $this->query($query);
        $columns = [];
        if (!empty($res)) {
            foreach ($res as $row) {
                $columns[$row['Field']] = $row;
                if (@$row['Key'] === 'PRI') {
                    $columns['id_row'] = $row['Field'];
                }
            }
        }
        return $columns;
    }

    /**
     * @param $table - Таблици базы данных
     * @param $set -Масив параметров
     *
     * 'fields' => ['id', 'name'],
     * 'where' => ['name' => 'Masha', 'surname' => 'Sergeevna', 'fio' => 'Andrei', 'car' => 'Porshe', 'color' => $color],
     * 'operand' => ['IN', 'LIKE', '<>', '=', 'NOT IN'],
     * 'condition' => ['OR', 'AND'],
     * 'order' => ['fio', 'name'],
     * 'order_direction' => ['DESC'],
     * 'limit' => '1',
     * 'join' => [
     *      'join_table' => [
     *      'table' => 'teachers',
     *      'fields' => ['id as j_id', 'name as J_name'],
     *      'type' => 'left',
     *      'where' => ['name' => 'Sasha'],
     *      'operand' => ['='],
     *      'condition' => ['OR'],
     *      'on' =>['id', 'parent_id']
     *      ],
     *      'join_table2' => [
     *      'table' => 'teachers',
     *      'fields' => ['id as j_id', 'name as J_name'],
     *      'type' => 'left',
     *      'where' => ['name' => 'Sasha'],
     *      'operand' => ['='],
     *      'condition' => ['OR'],
     *      'on' =>[
     *      'table'=> 'teachers',
     *      'fields'=>['id', 'parent_id']
     *      ]
     * ]
     * ]
     */

    final public function get($table, $set = [])
    {
        $fields = $this->createFields($table, $set);
        $order = $this->createOrder($table, $set);
        $where = $this->createWhere($set, $table);

        if (!$where) $new_where = true;
        else $new_where = false;

        $join_arr = $this->createJoin($table, $set, $new_where);


        $fields .= $join_arr['fields'];
        $join = $join_arr['join'];
        $where .= $join_arr['where'];
        $fields = rtrim($fields, ',');

        $limit = isset($set['limit']) ? 'LIMIT ' . $set['limit'] : '';

        $query = "SELECT $fields FROM $table $join $where $order $limit";
        echo "<pre>";
        print_r($query);
        echo "</pre>";
        return $this->query($query);
    }

    /**
     * @param $table - таблица для вставки данных
     * @param array $set - массив параметров:
     * fields => [поле => значение]; - если не указан, то обрабатывается $_POST[поле => значение]
     * разрешена передача NOW() в качестве MySql функции обычной строкой
     * files => [поле => значение]; - можно подать массив вида [поле => [массив значений]]
     * except => ['исключение 1', 'исключение 2'] - исключает данные элементы массива из       добавленных в запрос
     * return_id => true | false - возвращать или нет идентификатор вставленной записи
     * @return mixed
     */
    final public function add($table, $set = [])
    {
        @$set['fields'] = (is_array($set['fields']) and !empty($set['fields'])) ? $set['fields'] : $_POST;
        @$set['files'] = (is_array($set['files']) and !empty($set['files'])) ? $set['files'] : false;
        if (!$set['fields'] and !$set['files']) return false;

        @$set['return_id'] = (!empty($set['return_id'])) ? true : false;
        @$set['except'] = (is_array($set['except']) and !empty($set['except'])) ? $set['except'] : false;
        $insert_arr = $this->createInsert($set['fields'], $set['files'], $set['except']);
        if (!empty($insert_arr)) {
            $query = "INSERT INTO $table ({$insert_arr['fields']}) VALUES ({$insert_arr['values']})";
            return $this->query($query, 'c', $set['return_id']);
        }
    }

    final public function edit($table, $set = [])
    {
        @$set['fields'] = (is_array($set['fields']) and !empty($set['fields'])) ? $set['fields'] : $_POST;
        @$set['files'] = (is_array($set['files']) and !empty($set['files'])) ? $set['files'] : false;
        if (!$set['fields'] and !$set['files']) return false;
        @$set['except'] = (is_array($set['except']) and !empty($set['except'])) ? $set['except'] : false;
        if (!@$set['all_rows']) {
            if (@$set['where']) {
                $where = $this->createWhere($set);
            } else {
                $columns = $this->showColumns($table);
                if (!$columns) return false;
                if ($columns['id_row'] and $set['fields'][$columns['id_row']]) {
                    $where = 'WHERE ' . $columns['id_row'] . '=' . $set['fields'][$columns['id_row']];
                    unset($set['fields'][$columns['id_row']]);
                }
            }
        }
        $update = $this->createUpdate($set['fields'], $set['files'], $set['except']);
        $query = "UPDATE $table SET $update $where";
        return $this->query($query, 'u');
    }

    final function delete($table, $set)
    {
        $table = trim($table);
        $where = $this->createWhere($set, $table);
        $columns = $this->showColumns($table);
        if (!$columns) return false;
        if (!empty($set['fields']) and is_array($set['fields'])) {
            if ($columns['id_row']) {
                $key = array_search($columns['id_row'], $set['fields']);
                if ($key !== false) {
                    unset($set['fields'][$key]);
                }
            }
            $fields = [];
            foreach ($set['fields'] as $field) {
                $fields[$field] = $columns[$field]['Default'];
            }
            $update = $this->createUpdate($fields, false, false);
            $query = "UPDATE $table SET $update $where";

        } else {
            $join_arr = $this->createJoin($table, $set);
            $join = $join_arr['join'];
            $join_tables = $join_arr['tables'];
            $query = 'DELETE ' . $table . ' ' . $join_tables . ' FROM ' . $table . ' ' . $join .  ' ' . $where;
        }
        return $this->query($query, 'u');
    }


}