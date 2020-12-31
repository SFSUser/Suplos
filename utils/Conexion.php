<?php

	class Conexion {
	    var $conexion = null;
	    var $last_error = [];
	    var $last_id = 0;
	    
	    public function __construct($host, $database, $user, $password) {
	        $dbh = null;
	        try {
	            $dsn = "mysql:host=$host;dbname=$database";
	            $dbh = new PDO($dsn, $user, $password, array('charset'=>'utf8'));
	        } catch (PDOException $e){
	            echo $e->getMessage();
	        }
	        $this->conexion = $dbh;
	        return $dbh;
	    }
	    
	    public function getLastError(){
	        return $this->last_error;
	    }
	    
	    public function getLastId(){
	        return $this->last_id;
	    }
	    
	    public function getConexion(){
	        return $this->conexion;
	    }

	    /**
	     * CREA UNA NUEVA INSTANCIA DE CONEXION CONFIGURADA CON LOS DATOS PREDETERMINADOS!
	     * 
	     * @return \Conexion
	     */
	    public static function crear(){
	        return new Conexion("sena.fisiopraxis.com", "fisiopra_sena", "fisiopra_sena", "Sena123456789*");
	    }

	    /**
	     * EJECUTA UNA SENTENCIA INSERT O DELETE EN LA BASE DE DATOS, REGRESA INT
	     * SI EL RESULTADO FUE SATISFACTORIO
	     * 
	     * @param type $sql
	     * @param type $args
	     * @return type
	     */
	    public function ejecutar($sql, $args = []){
	        $sql_st = $this->conexion->prepare($sql);
	        $final_p = [];
	        foreach ($args as $key => $value) {
	            //$sql_st->bindColumn(":" . $key, $value);
	            $final_p[":$key"] = $value;
	        }
	        $result = $sql_st->execute($final_p);
	        
	        $this->last_error = $this->conexion->errorInfo();
	        $this->last_id = $this->conexion->lastInsertId();
	        return $result;
	    }
	    
	    /**
	     * EJECTURA UNA SENTENCIA SELECT EN LA BASE DE DATOS Y DEVUELVE UN ARREGLO
	     * ASOCIATIVO (NOMBRE_COLUMNA->VALOR)
	     * 
	     * @param type $sql
	     * @param type $args
	     * @return type
	     */
	    public function consultar($sql, $args = []){
	        $sql_st = $this->conexion->prepare($sql);
	        
	        $final_p = [];
	        foreach ($args as $key => $value) {
	            //$sql_st->bindColumn(":" . $key, $value);
	            $final_p[":$key"] = $value;
	        }
	        
	        //$sql_st->execute($args);
	        $sql_st->execute($final_p);
	        $data = $sql_st->fetchAll(PDO::FETCH_ASSOC);
	        $rows = [];
	        foreach($data as $row){
	            $rows[] = array_map('utf8_encode', $row);
	        }
	        $this->last_error = $this->conexion->errorInfo();
	        $this->last_id = $this->conexion->lastInsertId();
	        return $rows;
	    }
	}

?>