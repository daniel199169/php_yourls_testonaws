<?php
    include "conexion/getConexion.php";
    $connect = getConexion();

    try 
    {
            set_time_limit(720);
            $sqlBitacora = 'INSERT INTO fmessage_bitacora (bitacora_envio_id,bitacora_usuario_admin, bitacora_usuario_id, bitacora_pais_id,bitacora_telefono,bitacora_telefono_mascara,bitacora_mensaje,bitacora_estado_id,bitacora_fecha_envio) VALUES';
            $sqlEnvios = 'INSERT INTO fmessage_EnviosEnProceso (envioproceso_id, envioproceso_fecha, envioproceso_id_usuario,envioproceso_id_cliente,envioproceso_mensajes_enviados,envioproceso_total_mensajes,envioproceso_id_pais) VALUES';
            $Pendientes = BuscarEnviosPendientes(); //Find Pending Shipping
           
            if(!empty($Pendientes)){  // pending
                
                foreach($Pendientes as $row)  
                {   
                    $lista = ListaNumeros($row->id); // List of Numbers
                    $CantidadMensajes = COUNT($lista);// quantity of message
                    $EnviosEnProceso[] = array((int)$row->id,$row->fecha,(int)$row->usuario,(int)$row->admin,0,$CantidadMensajes,$row->pais); 
                    
                    foreach($lista as $numero)
                    {
                        $Mensaje = str_replace('"',"\\\"",$row->texto);
                        $Mensaje = str_replace("'",'\\\'',$Mensaje);
                        
                        $bitacora[] = array((int)$row->id,(int)$row->admin,(int)$row->usuario,(int)$row->pais,$numero,hiddenString($numero,5,2),(string)$Mensaje,2,(string)$row->fecha);
                    }
                    
                    $connect->setQuery("UPDATE fmessage_envio set envio_estado = 2 WHERE envio_id = '".$row->id."';");
                    
                    if($connect->alter()){
                       
                        echo PHP_EOL."todo se modifico correctamente la tabla de envio.".PHP_EOL;
                        //everything was modified correctly the shipping table
                    }  
                } 
            
                $i = 0;            
                foreach($EnviosEnProceso as $key)
                {
                        $sqlEnvios = $sqlEnvios."(".$key['0'].",'".$key['1']."',".$key['2'].",".$key['3'].",".$key['4'].",".$key['5'].",".$key['6'].")";
                        
                        if($i < count($EnviosEnProceso) - 1)
                        {
                           
                            $sqlEnvios = $sqlEnvios.",";
                        
                        }	
                        $i++; 
                }        
                $sqlEnvios = $sqlEnvios.";";  
                
                $connect->setQuery($sqlEnvios); 
                if($connect->alter()){
                    
                    echo PHP_EOL."todo se inserto correctamente EnviosEnProceso.".PHP_EOL;
                    // everything was inserted correctly Shipping In Process    
                }  
                
                $x = 0;  
                        
                foreach ($bitacora as $key)
                {
                    $sqlBitacora = $sqlBitacora.'('.$key["0"].','.$key["1"].','.$key["2"].','.$key["3"].',"'.$key["4"].'","'.$key["5"].'","'.$key["6"].'",'.$key["7"].',"'.$key["8"].'")';
                 
                    if($x < count($bitacora) - 1)
                    {
                       
                        $sqlBitacora = $sqlBitacora.",";
                   
                    }	
                    $x++; 
                }
                
                $sqlBitacora = $sqlBitacora.";";
              
                $connect->setQuery($sqlBitacora);
                if($connect->alter()){
                   
                    echo PHP_EOL."todo se inserto correctamente bitacora.".PHP_EOL;
                    // everything was inserted correctly log
                } 
            }
            else{
               
                echo PHP_EOL."No existen datos para insertar en bitacora.".PHP_EOL;
                //There are no data to insert in the log
            }
    }
    
    catch (\Throwable $th) {
        //throw $th;
    }

    /**
     * Desglosa los numeros de una determinada campaña.
     * 
     * @param $id contiene el id de la campaña.
     * 
     * @author Jafet Barquero.
     */
    function ListaNumeros($id) //List of Numbers
    {

        $connect = getConexion();  
        
        $connect->setQuery("SELECT envio_id as 'id', envio_contactos as 'contacto', envio_listas as 'lista', envio_telefonos as 'telefono'
                            FROM fmessage_envio
                            WHERE envio_estado = 1 AND envio_id = $id ;");
    
        $result = $connect->loadObjectList();          
        $arrayLista  = []; 
        $arrayContacto  = []; 
        $final = array();          
    
        foreach($result as $row)
        {
            $arrayLista = explode(',', str_replace(' ','',$row->lista));
        }        
    
        foreach($result as $row)
        {
            $arrayContacto = explode(',', str_replace(' ','',$row->contacto));
        }

        foreach($result as $row)
        {
            $telefono = explode(',', str_replace(' ','',$row->telefono));
            $final = array_merge($final, $telefono);
        }
 
        foreach($arrayLista as $row)
        {                
            $connect->setQuery("SELECT lista_telefonos_procesar as 'telefono'
                                FROM fmessage_lista
                                WHERE lista_id = '". $row ."';");
            $resultadoLista[] = $connect->loadObject();
        }    
      
        if($resultadoLista[0] != ""){
            foreach ($resultadoLista as $row )
            {
                $arrayExploteList = explode(',', str_replace(' ','',$row->telefono));
                $final = array_merge($final, $arrayExploteList);
            }
        }
       
        foreach($arrayContacto as $row)
        {
            $connect->setQuery("SELECT contacto_telefono as 'telefono'
                                FROM fmessage_contacto
                                WHERE contacto_id = '". $row ."';");
            $resultadoContacto[] = $connect->loadObject();
        }
        
        if($resultadoContacto[0] != "")
        {
            foreach ($resultadoContacto as $row )
            {
                $arrayExploteContact = explode(',', str_replace(' ','',$row->telefono));
                $final = array_merge($final, $arrayExploteContact);
            }
        }
        return($final);
    }

    function BuscarEnviosPendientes() //Find Pending Shipping
    {
        date_default_timezone_set('America/Costa_Rica');
        $fecha = date("Y-m-d H:i:s");
        try 
        {
                $connect = getConexion();
                $sql = "SELECT fe.envio_id as 'id', fe.envio_usuario_id as 'usuario', IFNULL(usu.usuario_admin_id,usu.usuario_id) as 'admin',	fe.envio_pais_id as 'pais', fe.envio_texto as 'texto', 
                            fe.envio_estado as 'estado', fe.envio_fecha as 'fecha'
                        FROM fmessage_envio fe
                        INNER JOIN fmessage_usuario usu 
                        ON fe.envio_usuario_id = usu.usuario_id
                        INNER JOIN fmessage_util_pais_destinos fpd 
                        ON fe.envio_pais_id = fpd.pais_id
                        WHERE CONVERT_TZ(fe.envio_fecha, fpd.pais_zona_horaria, '-06:00')  <= DATE_ADD('$fecha', INTERVAL 3 hour) AND fe.envio_estado = 1;";
                       
                        $connect->setQuery($sql);

                $result = $connect->loadObjectList();
                return $result;
        } 
        catch(excepcion $ex)
        {
            //aqui va algo
        }
    }

    function hiddenString($str, $start = 1, $end = 1)
    {
        $len = strlen($str);
        return substr($str, 0, $start) . str_repeat('X', $len - ($start + $end)) . substr($str, $len - $end, $end);
    }   

?>

