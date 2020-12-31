<?php
	require '../utils/Conexion.php';

	class MainController {
		var $con = null;
		public function __construct(){
			$this->con = new Conexion('localhost', 'intelcost_bienes', 'root', '0000');
		}

		public static function getParam($param, $default = null){
			$value = $default;
			if(isset($_REQUEST[$param])){
				$value = $_REQUEST[$param];
			}
			return $value;
		}
		private function singleData($array, $column){
			$data = [];
			foreach($array as $d){
				$data[] = $d[$column];
			}
			return $data;
		}
		public function getCityAction(){
			$data = $this->con->consultar("SELECT b.Ciudad FROM bienes AS b GROUP BY Ciudad");
			$data = $this->singleData($data, "Ciudad");
			header('Content-Type: application/json');
			echo json_encode($data);
		}

		public function getTypeAction(){
			$data = $this->con->consultar("SELECT b.Tipo FROM bienes AS b GROUP BY Tipo");
			$data = $this->singleData($data, "Tipo");
			header('Content-Type: application/json');
			echo json_encode($data);
		}
		public function saveAction(){
			$id = $this->getParam("id");
			$data = $this->con->consultar("SELECT * FROM bienes_guardados WHERE Bien = :id", ["id" => $id]);

			header('Content-Type: application/json');
			if(count($data) > 0){
				echo json_encode(["result" => 0, "message" => "Ya está guardado"]);
				exit();
			}

			$query = "INSERT INTO bienes_guardados (Bien, Usuario) VALUES (:bien, :usuario) ";
			$result = $this->con->ejecutar($query, ["bien" => $id, "usuario" => md5($id) ]);

			echo json_encode(["result" => $result ? 1 : 0]);
		}
		public function searchAction(){
			$ciudad = $this->getParam("ciudad");
			$tipo = $this->getParam("tipo");
			$precio = $this->getParam("precio", "");
			$params = [];
			$query = "SELECT * FROM bienes AS b WHERE 1=1 ";

			//Filtrar por ciudad
			if($ciudad != null){
				$query.= " AND b.ciudad = :ciudad ";
				$params["ciudad"] = $ciudad;
			}

			//Filtrar por tipo
			if($tipo != null){
				$query.= " AND b.tipo = :tipo ";
				$params["tipo"] = $tipo;
			}

			//Filtrar por rango de precio
			$precio = explode(";", $precio);
			if(count($precio) > 1){
				$query.= " AND b.precio BETWEEN :precio1 AND :precio2 ";
				$params["precio1"] = $precio[0];
				$params["precio2"] = $precio[1];
			}

			$data = $this->con->consultar($query, $params);
			foreach($data as &$d){
				$d["Precio"] = "$" . number_format($d["Precio"]);
			}
			header('Content-Type: application/json');
			echo json_encode($data);
		}

		public function searchSavedAction(){
			$query = "SELECT b.* FROM bienes_guardados AS b_g LEFT JOIN bienes AS b ON b.Id = b_g.Bien ORDER BY b.Id DESC ";

			//Consultar guardados
			$data = $this->con->consultar($query);
			foreach($data as &$d){
				$d["Precio"] = "$" . number_format($d["Precio"]);
			}
			header('Content-Type: application/json');
			echo json_encode($data);
		}
	}

	$controller = new MainController();
	$action = $controller->getParam("action", null);

	if($action != null){
		if(method_exists($controller, $action)){
			$controller->$action();
		}
	}

?>