<?php

/**
 * @name BaseDbModel
 * @author root
 */
class BasedbModel {

    public $_db;

    public function __construct($db) {
        $this->_db = $db;
    }

    /**
     * 获取列表
     * @param type $clos
     * @param type $table
     * @param type $where
     * @param type $join array(array('table'=>$table,'on'=>$on,'type'=>$type));
     * @param type $sort array('clos'=>'desc');
     * @param type $dataOffset
     * @param type $dataLimit
     * @return type
     */
    public function gets($clos, $table, $where = array(), $join = array(), $sort = array(), $dataOffset = -1, $dataLimit = -1) {
        $clostr = $this->concatcols($clos);
        $wherestr = $this->concatwheres($where);
        $joinstr = $this->concatjoins($join);
        $sortstr = $this->concatsort($sort);

        $sql = "SELECT {$clostr}
FROM {$table}
{$joinstr}
{$wherestr}
{$sortstr}";
//echo $sql;
        $list = $this->_db->getLimit($sql, $dataOffset, $dataLimit);
        return $list;
    }

    /**
     * 获取统计
     * @param type $table
     * @param type $where
     * @param type $join array(array('table'=>$table,'on'=>$on,'type'=>$type));
     * @param type $sort array('clos'=>'desc');
     * @param type $dataOffset
     * @param type $dataLimit
     * @return type
     */
    public function count($table, $where = array(), $join = array(), $sort = array(), $dataOffset = -1, $dataLimit = -1) {
        $wherestr = $this->concatwheres($where);
        $joinstr = $this->concatjoins($join);
        $sortstr = $this->concatsort($sort);

        $sql = "SELECT COUNT(*) AS `count` 
FROM {$table}
{$joinstr}
{$wherestr}
{$sortstr};";
//echo $sql;
        $listC = $this->_db->fetch_first($sql);
        if (is_null($listC) || !$listC || !isset($listC['count'])) {
            return 0;
        } else {
            return $listC['count'];
        }
    }

    /**
     * 获取单个值
     * @param type $clos
     * @param type $table
     * @param type $where
     * @param type $join array(array('table'=>$table,'on'=>$on,'type'=>$type));
     * @return type
     */
    public function get($clos, $table, $where = array(), $join = array()) {
        $clostr = $this->concatcols($clos);
//        print_r($where);
        $wherestr = $this->concatwheres($where);
        $joinstr = $this->concatjoins($join);

        $sql = "SELECT {$clostr}
FROM {$table}
{$joinstr}
{$wherestr};";
//echo $sql;
//die;
        $arow = $this->_db->fetch_first($sql);
        if (is_null($arow) || !$arow) {
            return false;
        } else {
            return $arow;
        }
    }

    public function insert($table, $data, $returnid = FALSE) {
        $out = FALSE;
        $datas = $this->concatkey_vals($data);
        $sql = "INSERT INTO {$table}(" . $datas['k'] . ")VALUES(" . $datas['v'] . ");";
        $query = $this->_db->query($sql);
//        echo $sql;
        if ($returnid) {
            if ($query) {
                $out = $this->_db->insert_id();
            }
        } else {
            $out = $query;
        }
        return $out;
    }

    public function update($table, $data, $where = array()) {
        $datas = $this->concatkey_vals($data);
        $wherestr = $this->concatwheres($where);
        $sql = "UPDATE {$table} SET " . $datas['kv'] . " {$wherestr};";
//        echo $sql;
        return $this->_db->query($sql);
    }

    public function replace($table, $data) {
        $datas = $this->concatkey_vals($data);
        $sql = "REPLACE INTO {$table}(" . $datas['k'] . ")VALUES(" . $datas['v'] . ");";
//        echo $sql;
//        die;
        return $this->_db->query($sql);
    }

    public function delete($table, $where = array()) {
        $wherestr = $this->concatwheres($where);
        $sql = "DELETE FROM {$table} {$wherestr};";
//        echo $sql;
//        die;
        return $this->_db->query($sql);
    }

    private function concatcols($clos) {
        $clostr = '';
        $clostrconcat = '';
        foreach ($clos as $key => $value) {
            $clostr = $clostr . $clostrconcat . $value;
            $clostrconcat = ' , ';
        }
        return $clostr;
    }

    private function concatwheres($where) {
        $wherestr = '';
        $wherestrconcat = '';
        foreach ($where as $key => $value) {
            $wherestr = $wherestr . $wherestrconcat . $value;
            $wherestrconcat = ' AND ';
        }
        $wherestr = $wherestr == '' ? '' : " WHERE {$wherestr}";
        return $wherestr;
    }

    private function concatjoins($join) {
        $joinstr = '';
        foreach ($join as $item) {
//            array('table'=>$table,'on'=>$on,'type'=>$type);
            if (isset($item['table']) && isset($item['on']) && isset($item['type'])) {
                $jointype = " ";
                switch (strtolower($item['type'])) {
                    case 'left':
                        $jointype = " LEFT JOIN ";
                        break;
                    case 'right':
                        $jointype = " RIGHT JOIN ";
                        break;
                    default :
                        $jointype = " JOIN ";
                        break;
                }
                $joinstr = $joinstr . $jointype . $item['table'] . ' ON ' . $item['on'];
            }
        }
        return $joinstr;
    }

    private function concatsort($sort) {
        $sortstr = '';
        $sortstrconcat = '';
        foreach ($sort as $key => $value) {
            $sortstr = $sortstr . $sortstrconcat . addslashes($key) . ' ' . addslashes($value);
            $sortstrconcat = ' , ';
        }
        $sortstr = $sortstr == '' ? '' : " ORDER BY {$sortstr}";
        return $sortstr;
    }

    private function concatkey_vals($data) {
        $keystr = '';
        $valstr = '';
        $kvstr = '';
        $strconcat = '';
        foreach ($data as $key => $value) {
            $keystr = $keystr . $strconcat . addslashes($key);
            $valstr = $valstr . $strconcat . "'" . addslashes($value) . "'";
            $kvstr = $kvstr . $strconcat . addslashes($key) . "='" . addslashes($value) . "'";
            $strconcat = ' , ';
        }
        return array('k' => $keystr, 'v' => $valstr, 'kv' => $kvstr);
    }

}
