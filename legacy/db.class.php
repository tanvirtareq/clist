<?php
    $is_debug = true;
    class db
    {
        private $host = 'localhost';
        private $name = 'database';
        private $port = '5432';
        private $user = '';
        private $password = '';

        function __construct()
        {
            $conf_file = dirname(__FILE__) . '/pyclist.conf';

            foreach (explode("\n", trim(file_get_contents($conf_file))) as $line) {
                if (substr($line, 0, 3) == "DB_") {
                    list($key, $value) = explode(" = ", $line);
                    $key = strtolower(substr($key, 3));
                    $this->$key = $value;
                }
            }

            //$this->link = mysqli_connect($this->host, $this->user, $this->password, $this->name);
            $this->link = pg_connect("host={$this->host} port={$this->port} user={$this->user} password={$this->password} dbname={$this->name} options='--client_encoding=UTF8'");

            //mysqli_connect_errno() && die('Ошибка соединения: ' . mysqli_connect_error());
            $this->link || die('Connection error');

            //mysqli_set_charset($this->link, 'utf8');
            pg_client_encoding($this->link) == "UTF8" || die('Encoding is not UTF8');
            $this->query("SET TIME ZONE 'UTC';");
        }

        function query($sql, $ignore_error = false)
        {
            global $is_debug;
            $this->result = pg_query($this->link, $sql);

            if (!$this->result && $is_debug && !$ignore_error)
            {
                print "
                    <span style=\"color:#555555;font-size:12pt;font-family:'Arial';font-weight:bold;\">SQL query error:<br>&nbsp;&nbsp;&nbsp;Query: </span>
                    <span style=\"color:#000000;font-size:12pt;font-family:'Arial';\">" . $sql . "<br></span>
                ";
                exit(0);
            }
            return $this->result;
        }

        function getRow($sql)
        {
            return pg_fetch_assoc($this->query($sql));
        }

        function getValue($sql)
        {
            $arr = pg_fetch_array($this->query($sql));
            return $arr[0];
        }

        function getArray($sql)
        {
            $arr = array();
            $this->result = $this->query($sql);
            while ($this->result && $x = pg_fetch_assoc($this->result)) $arr[] = $x;
            return $arr;
        }

        function select($table, $fields = '*', $where = '1 = 1')
        {
            $sql = "select " . $fields . " from " . $table . " where $where";
            return $this->getArray($sql);
        }

        function escapeString($data)
        {
            //return mysqli_real_escape_string($this->link, $data);
            return pg_escape_string($this->link, $data);
        }

        function escapeArray($a)
        {
            $res = array();
            foreach ($a as $k => $v) {
                $res[$k] = $this->escapeString($v);
            }
            return $res;
        }

        function getFirstRow($table, $fields, $where = false)
        {
            $sql = "select " . $fields . " from " . $table . "";
            if ($where) $sql  .= " where " . $where;
            return $this->getRow($sql);
        }

        function insert($table, $fields, $values)
        {
            $sql = "insert into " . $table . "(" . $fields . ") values (" . $values . ")";
            return $this->query($sql);
        }

        function delete($table, $where = false)
        {
            $sql = "delete from " . $table . "";
            if ($where) $sql  .= " where " . $where;
            return $this->query($sql);
        }

        function update($table, $values, $where = '1 = 1')
        {
            $sql = "update " . $table . " set " . $values . " where " . $where;
            return $this->query($sql);
        }

        function affected_rows()
        {
            return pg_affected_rows($this->result);
        }

        function close()
        {
            if ($this->link) {
                pg_close($this->link);
            }
        }

        function __destruct()
        {
            $this->close();
        }
    }
    $db = new db();
?>
