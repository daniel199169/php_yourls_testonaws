<?php
    include "ConexionDB.php";

    function getConexion()
    {
        $user = "root";
        $pass = "holatec";
        $server = "localhost";
        $db = "fmessage_test";
        //conectar base de datos
        $conn = new ConexionDB((php_uname('n') == 'vps.tecnologias-sms.com') ? "localhost" : "$server", "$user", "$pass");
        if (!$conn->__get('_conexion')) {
            echo "   ".date('Y-m-d H:i:s')." No se pudo conectar a la base de datos", "\n";
            exit();
        }

        // conectar tabla
        if (!$conn->selectdatabase("$db")) {
            echo "   ".date('Y-m-d H:i:s')." No se pudo seleccionar la tabla", "\n";
            exit();
        }

        // evitar error de tiempo de ejecución
        set_time_limit(360);

        // define zona horar en mysql
        $conn->setQuery("SET TIME_ZONE = '-06:00'"); // definir zona horaria (-06:00, -6:00, America/Costa_Rica)
        $conn->alter();
        $conn->setQuery("SET NAMES 'utf8'"); // definir tipo utf8
        $conn->alter();
        #$conn = mysqli_connect($server,$user,$pass) or die ("Error al conectar a la base de datos");
        #mysql_select_db($db,$conn);    
        return $conn;
    }

?>