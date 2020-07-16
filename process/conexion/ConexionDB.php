<?php

class ConexionDB
{
    private $_conexion;
    private $_resource;
    private $_sql;

    public function __construct($server, $user, $pass)
    {
        $this->_conexion = mysqli_connect($server, $user, $pass);
        $this->_resource = null;
    }
   
    /**
     * @param $database
     * @return bool|null
     */
    public function selectdatabase($database)
    {
        if (!mysqli_select_db($this->_conexion,$database)) {
            return;
        }

        return true;
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        return;
    }

    /**
     * @return bool
     */
    public function alter()
    {
        if (!($this->_resource = mysqli_query($this->_conexion,$this->_sql))) {
            return false;
        }

		//printf("Last inserted record has id %d\n", mysqli_insert_id());
		
        return true;
    }

    /**
     * @return array|null
     */
    public function loadObjectList()
    {
        if (!($cur = $this->execute())) {
            return;
        }
        $array = array();
        while ($row = @mysqli_fetch_object($cur)) {
            $array[] = $row;
        }

        return $array;
    }

    /**
     * @return null|resource
     */
    public function execute()
    {
        if (!($this->_resource = mysqli_query($this->_conexion,$this->_sql))) {
            return;
        }

        return $this->_resource;
    }

    /**
     * @param $sql
     * @return bool
     */
    public function setQuery($sql)
    {
        if (empty($sql)) {
            return false;
        }
        $this->_sql = $sql;

        return true;
    }

    /**
     * @return bool
     */
    public function freeResults()
    {
        @mysqli_free_result($this->_resource);

        return true;
    }

    /**
     * @return bool|null|object|stdClass
     */
    public function loadObject()
    {
        //if ($cur = $this->execute()){
        $cur = $this->execute();
        if ($cur) {
            if ($object = mysqli_fetch_object($cur)) {
                @mysqli_free_result($cur);

                return $object;
            } else {
                return;
            }
        } else {
            return false;
        }
    }

    /**
     * @return int
     */
    public function num_rows()
    {
        if (!$this->execute()) {
            return 0;
        }

        return mysqli_num_rows($this->execute());
    }

    public function __destruct()
    {
        @mysqli_free_result($this->_resource);
        @mysqli_close($this->_conexion);
    }
    
}


    


    
    // public function Conexion()
    // {
    //     $user = "fastmess_tecsms";
    //     $pass = "Fs3f52ncEy";
    //     $server = "fmessage.com";
    //     $db = "fastmess_fmessageNew";
    //     $conn = mysqlii_connect($server,$user,$pass, $db);
    //     #$conn = mysqlii_connect($server,$user,$pass) or die ("Error al conectar a la base de datos");
    //     #mysqli_select_db($db,$conn);    
    //     return $conn;
    // }
?>
