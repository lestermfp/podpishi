<?php
//print '<pre>' . print_r($this->output, true) . '</pre>';

/*include('../../../config.php');
	
class db_moscow_transport extends ActiveRecord\Model {
	static $table_name = 'moscow_transport';
}
*/

class CReg_user_ajax {
    public $output = array(); //Создаем массив который будет отослан в виде JSON.

    public function __construct() {
		
		$this->output['error'] = 'false';		
		
        if (isset($_GET['context'])) {
            switch ($_GET['context']) {
				case 'loadRouteByAjax':
                    $this->loadRouteByAjax();
                    break;	
				case 'saveRouteByAjax':
                    $this->saveRouteByAjax();
                    break;	
				case 'getFullnesOfBuildingsV2':
                    $this->getFullnesOfBuildingsV2();
                    break;		
				case 'saveNewRoute':
                    $this->saveNewRoute();
                    break;							
					

            }
        }
		
        echo json_encode( $this->output );		
    }
	
	private function loadRouteByAjax(){
		
		$plan = db_moscow_transport::find_by_id($_GET['selected_plan']);
		
		if (empty($plan)){
			$this->output['error'] = 'ntf';
			$this->output['error_text'] = 'Не найдено';
			return ;
		}
		
		$this->output['plan'] = json_decode($plan->read_attribute('map_points1'));
		$this->output['plan_backward'] = json_decode($plan->read_attribute('map_points2'));
		$this->output['plan_name'] = $plan->read_attribute('name');
		$this->output['plan_id'] = $_GET['selected_plan'];
		
		//print '<pre>' . print_r($_GET, true) . '</pre>';
	}
	
	private function saveRouteByAjax(){
		
		$plan = db_moscow_transport::find_by_id($_POST['selected_plan']);
		
		if (empty($plan)){
			$this->output['error'] = 'ntf';
			$this->output['error_text'] = 'Не найдено';
			return ;
		}
		
		$plan->map_points1 = json_encode($_POST['plan']);
		$plan->segments = implode(';', $_POST['segments']);
		$plan->save();
		
		//$this->output['plan'] = json_decode($plan->read_attribute('map_points1'));
		
		//print '<pre>' . print_r($_GET, true) . '</pre>';
	}
	
	private function saveNewRoute(){
		
		
		
		$segments = array();
		
		foreach ($_POST['plan'] as $_plan){
			if (!isset($_plan['streets']))
				continue ;
			
			$segments = array_merge($segments, $_plan['streets']);
		}

		$type = 'trolley2';
		if (isset($_POST['type']) AND !empty($_POST['type']))
			$type = $_POST['type'];
		
		$_POST['routeName'] = trim($_POST['routeName']);
		
		$newRoute = array(
			'type' => $type,
			'name' => $_POST['routeName'],
			'map_points1' => json_encode($_POST['plan']),
			'segments' => implode(';', $segments),
			'date' => 'now',
		);
		
		
		if ($type == 'street'){
			
			file_put_contents('./streets/' . md5($_POST['routeName']), serialize($newRoute));
			
		}
		else {
			//db_moscow_transport::create($newRoute);
		}
		

		//$this->output['plan'] = json_decode($plan->read_attribute('map_points1'));
		
		//print '<pre>' . print_r($newRoute, true) . '</pre>';
	}
	
}

$CReg_user_ajax = new CReg_user_ajax;
?>