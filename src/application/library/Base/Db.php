<?php

class Base_Db {

    var $link_id;

    /**
     * 单例模式实例化对象
     *
     * @var object
     */
    public static $instance;

    /**
     * 连接数据库
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $port
     * @param string $character
     * @param string $dbname
     */
    private function __construct($params) {
        $this->link_id = mysqli_connect($params['host'], $params['username'], $params['password'], $params['dbname'], $params['port']);
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
        mysqli_query($this->link_id, "set names {$params['char']}");
        mysqli_select_db($this->link_id, $params['dbname']);
    }

    /**
     * 选择数据库
     *
     * @param string $dbname
     * @return bool
     */
    function select_db($dbname) {
        return mysqli_select_db($this->link_id, $dbname);
    }

    /**
     * 执行单条SQL语句
     *
     * @param string $sql
     * @return bool
     */
    function query($sql) {

        $rt = mysqli_query($this->link_id, $sql);

        if ($this->error()) {

            return false;
            echo $this->error();
            exit;
        }
        return $rt;
    }

    /**
     * 执行多条SQL语句
     *
     * @param string $sql
     * @return bool
     */
    function multi_query($sql) {
        return mysqli_multi_query($this->link_id, $sql);
    }

    function store_result() {
        return mysqli_store_result($this->link_id);
    }

    function more_results() {
        return mysqli_more_results($this->link_id);
    }

    function next_result() {
        return mysqli_next_result($this->link_id);
    }

    /**
     * 调用存储过程
     *
     * @param string $sql
     */
    function call($sql) {
        $results = array();
        $this->multi_query($sql);
        do {
            if ($result = mysqli_use_result($this->link_id)) {
                while ($row = $this->fetch_array($result)) {
                    $results[] = $row;
                }
            }
        } while (mysqli_more_results($this->link_id) && mysqli_next_result($this->link_id));
        return $results;
    }

    /**
     * 联合查询单个或多个数据表特定记录
     * @param type $sql                查询SQL
     * @param type $offset             数据偏移量
     * @param type $limit              条数限制
     * @return array			查询结果数组
     */
    function getLimit($sql, $offset = -1, $limit = -1) {
        if($offset<0){$offset=0;};
        if ($limit > 0) {
            $LIMIT = ' LIMIT ' . $offset . ',' . $limit;
        }
//        echo $sql . $LIMIT;
        $rs = $this->query($sql . $LIMIT);
        $rsList = array();
        if ($rs) {
            while ($row = $this->fetch_array($rs)) {
                $rsList[] = $row;
            }
            $this->free_result($rs);
        }
        return $rsList;
    }

    /**
     * 联合查询单个或多个数据表特定记录
     * 用法：getNewLimit($sql, $startpage = -1, $per = -1)
     * @param string  $sql  
     * @param string  $per	       偏移量
     * @param string  $startpage       开始查询页数
     * @return array		       查询结果数组	
     */
    function getNewLimit($sql, $startpage = -1, $per = -1) {
        $start = ($startpage - 1) * $per > 0 ? ($startpage - 1) * $per : 0;
        if ($per > 0) {
            $LIMIT = ' LIMIT ' . $start . ',' . $per;
        }
        $rs = $this->query($sql . $LIMIT);
        $rsList = array();
        if ($rs) {
            while ($row = $this->fetch_array($rs)) {
                $rsList[] = $row;
            }
            $this->free_result($rs);
        }
        return $rsList;
    }

    /**
     * 返回结果集
     *
     * @param string $query
     * @param int $result_type
     * @return array
     */
    function fetch_array($query, $result_type = MYSQL_ASSOC) {
        return mysqli_fetch_array($query, $result_type);
    }

    /**
     * 返回第一行数据
     *
     * @param string $sql
     * @return array
     */
    function fetch_first($sql) {
        return $this->fetch_array($this->query($sql));
    }

    /**
     * 返回数据第N个结果
     *
     * @param string $query
     * @param int $row
     * @return string
     */
    function result($query, $row = 0) {
        $query = mysqli_fetch_row($query);
        return $query[$row];
    }

    /**
     * 返回第一个结果
     *
     * @param string $sql
     * @return string
     */
    function result_first($sql) {
        return $this->result($this->query($sql), 0);
    }

    /**
     * 获取SQL语句影响行数
     *
     * @return int
     */
    function affected_rows() {
        return mysqli_affected_rows($this->link_id);
    }

    /**
     * 返回错误信息
     *
     * @return string
     */
    function error() {
        return (($this->link_id) ? mysqli_error($this->link_id) : mysqli_error());
    }

    /**
     * 返回错误编号
     *
     * @return int
     */
    function errno() {
        return intval(($this->link_id) ? mysqli_errno($this->link_id) : mysqli_errno());
    }

    /**
     * 返回数据行数
     *
     * @param string $query
     * @return int
     */
    function num_rows($query) {
        $query = mysqli_num_rows($query);
        return $query;
    }

    /**
     * 获取最后插入的数据ID
     *
     * @return int
     */
    function insert_id() {
        return ($id = mysqli_insert_id($this->link_id)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
    }

    /**
     * 释放结果集
     *
     * @return bool
     */
    function free_result($resource) {
        return mysqli_free_result($resource);
    }

    /**
     * 关闭MYSQL连接
     *
     */
    function __destruct() {
        mysqli_close($this->link_id);
    }

    /**
     * 单例模式
     *
     * @access public
     * @param array $params 数据库连接参数,如数据库服务器名,用户名,密码等
     * @return object
     */
    public static function getInstance($params) {

        if (!self::$instance) {
            self::$instance = new self($params);
        }

        return self::$instance;
    }

}