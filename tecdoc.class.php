<?php

define( 'ERROR_MSGS', '1' ); 		// Вывод сообщений: 					1- включен, 0 - отключен
define( 'SPEED_TEST', '1' ); 		// Замер скорости выполнения скрипта: 	1- включен, 0 - отключен

define( 'HOST', '127.0.0.1' ); 		// Сервер баз данных
define( 'PORT', '3306' ); 			// Порт сервера баз данных
define( 'USER', 'user' ); 			// Пользователь базы данных
define( 'PASS', 'password' ); 		// Пароль пользователя
define( 'NAME', 'dbname' ); 		// Имя базы данных


if( ERROR_MSGS == 1 ) {
	error_reporting( E_ALL );
	ini_set( 'display_errors', 'On' );
} else {
	error_reporting( 0 );
	ini_set( 'display_errors', 'Off' );	
}

$startTime = SPEED_TEST == 1 ? microtime(true) : null;
	
class DB {

    private static $instance;
		
    public static function instance()
    {
        if (!isset(DB::$instance)) {
            $db = array(
                'host' => HOST,
                'user' => USER,
                'pass' => PASS,
                'name' => NAME,
                'port' => PORT,
            );
            self::connect($db);
        }
        return DB::$instance;
    }
 
 //	$asArray - возвращать объект или массив
 
    public static function select($query, $asArray = false)
    {
        $mysqli_result = self::instance()->query($query);
        if ($mysqli_result) {
            $r = array();
            while ($row = $mysqli_result->fetch_object()) {
                $r[] = $asArray ? (array) $row : $row;
            } 
            return $r;
        }
        return array();
    }
 
    public static function selectRow($query, $asArray = false)
    {
        $mysqli_result = self::instance()->query($query);
        if ($mysqli_result) {
            $row = $mysqli_result->fetch_row();
            if ($row) {
                if ($asArray) {
                    return (array) $row;
                }
                else {
                    return $row;
                }
            }
        }
        return array();
    }
 
    public static function selectCol($query)
    {
        $rows = self::select($query,true); 
        return array_map(function ($row) {return array_shift($row);}, $rows);
    }
 
    public static function selectCell($query)
    {
        $mysqli_result = self::instance()->query($query);
        if($mysqli_result) {
            $row = $mysqli_result->fetch_row();
            if ($row) {
                return $row[0];
            }
        }
        return NULL;
    }
 
    public static function query($query)
    {
        return self::instance()->query($query);
    }

    public static function connect(array $db)
    {
        $mysqli = new mysqli($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
 
        if (!$mysqli->connect_error) {
			$mysqli->set_charset("utf8");
            DB::$instance = $mysqli;
        }
        return $mysqli;
    }
}

class Tecdoc extends DB {
	
	
//====================================//
// (1) АВТОМОБИЛИ
//===================================//
	
	
	// (1.1) Марки авто (производители)
	static function getMakes( $type )
		{
			switch ($type) {
			case 'passenger':
				$where = " AND ispassengercar = 'True'";
				break;
			case 'commercial':
				$where = " AND iscommercialvehicle = 'True'";
				break;
			case 'motorbike':
				$where = " AND ismotorbike  = 'True' AND haslink = 'True'";
				break;
			case 'engine':
				$where = " AND isengine = 'True'";
				break;
			case 'axle':
				$where = " AND isaxle = 'True'";
				break;

			}
			
			$order = $type == 'motorbike' ? 'description' : 'matchcode';
			
			return parent::select("
				SELECT id, description name
				FROM manufacturers
				WHERE canbedisplayed = 'True' " . $where . "
				ORDER BY " . $order);
		} 
		
	// (1.2) Модели авто 	
	static function getModels( $make_id, $type, $pattern = null )
		{
		
			switch ($type) {
			case 'passenger':
				$where = " AND ispassengercar = 'True'";
				break;
			case 'commercial':
				$where = " AND iscommercialvehicle = 'True'";
				break;
			case 'motorbike':
				$where = " AND ismotorbike  = 'True'";
				break;
			case 'engine':
				$where = " AND isengine = 'True'";
				break;
			case 'axle':
				$where = " AND isaxle = 'True'";
				break;

			}
			
			if( $pattern != null ) $where .= " AND description LIKE '" . $pattern . "%'";

			return parent::select("
				SELECT id, description name, constructioninterval
				FROM models
				WHERE canbedisplayed = 'True'
				AND manufacturerid = " . (int)$make_id . " " . $where . "
				ORDER BY description");
		}
	
	// (1.3) Модификации авто 
	static function getModifications( $model_id, $type ){
        switch ($type) {
			case 'passenger':
				$data = parent::select("
					SELECT id, fulldescription name, a.attributegroup, a.attributetype, a.displaytitle, a.displayvalue
					FROM passanger_cars pc 
					LEFT JOIN passanger_car_attributes a on pc.id = a.passangercarid
					WHERE canbedisplayed = 'True'
					AND modelid = " . (int)$model_id . " AND ispassengercar = 'True'");
				break;
			case 'commercial':
				$data = parent::select("
					SELECT id, fulldescription name, a.attributegroup, a.attributetype, a.displaytitle, a.displayvalue
					FROM commercial_vehicles cv 
					LEFT JOIN commercial_vehicle_attributes a on cv.id = a.commercialvehicleid
					WHERE canbedisplayed = 'True'
					AND modelid = " . (int)$model_id . " AND iscommercialvehicle = 'True'");
				break;
			case 'motorbike':
				$data = parent::select("
					SELECT id, fulldescription name, a.attributegroup, a.attributetype, a.displaytitle, a.displayvalue
					FROM motorbikes m 
					LEFT JOIN motorbike_attributes a on m.id = a.motorbikeid
					WHERE canbedisplayed = 'True'
					AND modelid = " . (int)$model_id . " AND ismotorbike = 'True'");
				break;
			case 'engine':
				$data = parent::select("
					SELECT id, fulldescription name, salesDescription, a.attributegroup, a.attributetype, a.displaytitle, a.displayvalue
					FROM engines e 
					LEFT JOIN engine_attributes a on e.id= a.engineid
					WHERE canbedisplayed = 'True'
					AND manufacturerId = " . (int)$model_id . " AND isengine = 'True'");
				break;
			case 'axle':
				$data = parent::select("
					SELECT id, fulldescription name, a.attributegroup, a.attributetype, a.displaytitle, a.displayvalue
					FROM axles ax 
					LEFT JOIN axle_attributes a on ax.id= a.axleid
					WHERE canbedisplayed = 'True'
					AND modelid = " . (int)$model_id . " AND isaxle = 'True'");
				break;

			}

		foreach($data as $car){
			$return[ $car->id ]['id'] 			= $car->id;
			$return[ $car->id ]['name'] 		= $car->name;
			$return[ $car->id ]['attributes'][] 	= array( 'attributegroup' => $car->attributegroup, 'attributetype' => $car->attributetype, 'displaytitle' => $car->displaytitle, 'displayvalue' => $car->displayvalue );
		}

		return $return;
    }
	
	// (1.4) Марка по ID
	static function getMake( $id, $type )
		{
			switch ($type) {
			case 'passenger':
				$where = " AND ispassengercar = 'True'";
				break;
			case 'commercial':
				$where = " AND iscommercialvehicle = 'True'";
				break;
			case 'motorbike':
				$where = " AND ismotorbike  = 'True' AND haslink = 'True'";
				break;
			case 'engine':
				$where = " AND isengine = 'True'";
				break;
			case 'axle':
				$where = " AND isaxle = 'True'";
				break;

			}
			return parent::select("
				SELECT id, description name
				FROM manufacturers
				WHERE canbedisplayed = 'True' " . $where . " AND id = " . (int)$id . ";
			");
		} 	
		
	// (1.5) Модель по ID
	static function getModel( $id, $type ){
		
			switch ($type) {
				case 'passenger':
					$where = " AND ispassengercar = 'True'";
					break;
				case 'commercial':
					$where = " AND iscommercialvehicle = 'True'";
					break;
				case 'motorbike':
					$where = " AND ismotorbike  = 'True'";
					break;
				case 'engine':
					$where = " AND isengine = 'True'";
					break;
				case 'axle':
					$where = " AND isaxle = 'True'";
					break;

				}
				
			return parent::select("
				SELECT id, description name, constructioninterval
				FROM models
				WHERE canbedisplayed = 'True' " . $where . " AND id = " . (int)$id . "
			");
    }
	
	// (1.6) Модификация по ID
	static function getType( $id, $type ){
        switch ($type) {
			case 'passenger':
				$data = parent::select("
					SELECT id, fulldescription name, a.attributegroup, a.attributetype, a.displaytitle, a.displayvalue
					FROM passanger_cars pc 
					LEFT JOIN passanger_car_attributes a on pc.id = a.passangercarid
					WHERE canbedisplayed = 'True'
					AND id = " . (int)$id . " AND ispassengercar = 'True'");
				break;
			case 'commercial':
				$data = parent::select("
					SELECT id, fulldescription name, a.attributegroup, a.attributetype, a.displaytitle, a.displayvalue
					FROM commercial_vehicles cv 
					LEFT JOIN commercial_vehicle_attributes a on cv.id = a.commercialvehicleid
					WHERE canbedisplayed = 'True'
					AND id = " . (int)$id . " AND iscommercialvehicle = 'True'");
				break;
			case 'motorbike':
				$data = parent::select("
					SELECT id, fulldescription name, a.attributegroup, a.attributetype, a.displaytitle, a.displayvalue
					FROM motorbikes m 
					LEFT JOIN motorbike_attributes a on m.id = a.motorbikeid
					WHERE canbedisplayed = 'True'
					AND id = " . (int)$id . " AND ismotorbike = 'True'");
				break;
			case 'engine':
				$data = parent::select("
					SELECT id, fulldescription name, salesDescription, a.attributegroup, a.attributetype, a.displaytitle, a.displayvalue
					FROM engines e 
					LEFT JOIN engine_attributes a on e.id = a.engineid
					WHERE canbedisplayed = 'True'
					AND id = " . (int)$id . " AND isengine = 'True'");
				break;
			case 'axle':
				$data = parent::select("
					SELECT id, fulldescription name, a.attributegroup, a.attributetype, a.displaytitle, a.displayvalue
					FROM axles ax 
					LEFT JOIN axle_attributes a on ax.id = a.axleid
					WHERE canbedisplayed = 'True'
					AND id = " . (int)$id . " AND isaxle = 'True'");
				break;

			}

		foreach($data as $car){
			$return[ $car->id ]['id'] 			= $car->id;
			$return[ $car->id ]['name'] 		= $car->name;
			$return[ $car->id ]['attributes'][] 	= array( 'attributegroup' => $car->attributegroup, 'attributetype' => $car->attributetype, 'displaytitle' => $car->displaytitle, 'displayvalue' => $car->displayvalue );
		}

		return $return;
    }
	
//====================================//
// (2) Дерево категорий / разделы
//===================================//

	// (2.1) Построение дерева категорий изделий для заданного типа автомобиля (от родительского)
	/*
	Последовательно устанавливая следующие значения parentid, можно получить ещё 4 уровня дерева
	havechild -  Есть ли у этой категории подкатегории: 1 - Есть, 0 - Нет
	Если есть, то ее parentid ставим на вход метода
	*/
	static function getSections( $modification_id, $type, $parent=0 )
		{
			switch ($type) {
				case 'passenger':
					return parent::select("
						SELECT id, description, 
						IF(EXISTS(SELECT * FROM passanger_car_trees t1 
						INNER JOIN passanger_car_trees t2 ON t1.parentid=t2.id WHERE t2.parentid=" . (int)$parent . " AND t1.passangercarid=" . (int)$modification_id . " LIMIT 1), 1, 0) AS havechild 
						FROM passanger_car_trees WHERE passangercarid=" . (int)$modification_id . " AND parentId=" . (int)$parent . "
						ORDER BY havechild
					");
					break;
				case 'commercial':
					return parent::select("
						SELECT id, description, 
						IF(EXISTS(SELECT * FROM commercial_vehicle_trees t1 
						INNER JOIN commercial_vehicle_trees t2 ON t1.parentid=t2.id WHERE t2.parentid=" . (int)$parent . " AND t1.commercialvehicleid=" . (int)$modification_id . " LIMIT 1), 1, 0) AS havechild 
						FROM commercial_vehicle_trees WHERE commercialvehicleid=" . (int)$modification_id . " AND parentId=" . (int)$parent . "
						ORDER BY havechild
					");
					break;
				case 'motorbike':
					return parent::select("
						SELECT id, description, 
						IF(EXISTS(SELECT * FROM motorbike_trees t1 
						INNER JOIN motorbike_trees t2 ON t1.parentid=t2.id WHERE t2.parentid=" . (int)$parent . " AND t1.motorbikeid=" . (int)$modification_id . " LIMIT 1), 1, 0) AS havechild 
						FROM motorbike_trees WHERE motorbikeid=" . (int)$modification_id . " AND parentId=" . (int)$parent . "
						ORDER BY havechild
					");
					break;
				case 'engine':
					return parent::select("
						SELECT id, description, 
						IF(EXISTS(SELECT * FROM engine_trees t1 
						INNER JOIN engine_trees t2 ON t1.parentid=t2.id WHERE t2.parentid=" . (int)$parent . " AND t1.engineid=" . (int)$modification_id . " LIMIT 1), 1, 0) AS havechild 
						FROM engine_trees WHERE engineid=" . (int)$modification_id . " AND parentId=" . (int)$parent . "
						ORDER BY havechild
					");
					break;
				case 'axle':
					return parent::select("
						SELECT id, description, 
						IF(EXISTS(SELECT * FROM axle_trees t1 
						INNER JOIN axle_trees t2 ON t1.parentid=t2.id WHERE t2.parentid=" . (int)$parent . " AND t1.axleid=" . (int)$modification_id . " LIMIT 1), 1, 0) AS havechild 
						FROM axle_trees WHERE axleid=" . (int)$modification_id . " AND parentId=" . (int)$parent . "
						ORDER BY havechild
					");
					break;

				}
		}
		
	// (2.2) Название раздела по ID - используется в СЕО
	static function getSectionName( $section_id, $type )
		{
			switch ($type) {
				case 'passenger':
					return parent::selectCell("SELECT description FROM passanger_car_trees WHERE id=" . (int)$section_id . " LIMIT 1");
					break;
				case 'commercial':
					return parent::selectCell("SELECT description FROM commercial_vehicle_trees WHERE id=" . (int)$section_id . " LIMIT 1");
					break;
				case 'motorbike':
					return parent::selectCell("SELECT description FROM motorbike_trees WHERE id=" . (int)$section_id . " LIMIT 1");
					break;
				case 'engine':
					return parent::selectCell("SELECT description FROM engine_trees WHERE id=" . (int)$section_id . " LIMIT 1");
					break;
				case 'axle':
					return parent::selectCell("SELECT description FROM axle_trees WHERE id=" . (int)$section_id . " LIMIT 1");
					break;

				}
		}

	// (2.3) Поиск запчастей раздела
	static function getSectionParts( $modification_id, $section_id, $type )
		{
			switch ($type) {
				case 'passenger':
					return parent::select(" SELECT al.datasupplierarticlenumber part_number, s.description supplier_name, prd.description product_name
											FROM article_links al 
											JOIN passanger_car_pds pds on al.supplierid = pds.supplierid
											JOIN suppliers s on s.id = al.supplierid
											JOIN passanger_car_prd prd on prd.id = al.productid
											WHERE al.productid = pds.productid
											AND al.linkageid = pds.passangercarid
											AND al.linkageid = " . (int)$modification_id . "
											AND pds.nodeid = " . (int)$section_id . "
											AND al.linkagetypeid = 2
											ORDER BY s.description, al.datasupplierarticlenumber");
					break;
				case 'commercial':
					return parent::select(" SELECT al.datasupplierarticlenumber part_number, s.description supplier_name, prd.description product_name
											FROM article_links al 
											JOIN commercial_vehicle_pds pds on al.supplierid = pds.supplierid
											JOIN suppliers s on s.id = al.supplierid
											JOIN commercial_vehicle_prd prd on prd.id = al.productid
											WHERE al.productid = pds.productid
											AND al.linkageid = pds.commertialvehicleid
											AND al.linkageid = " . (int)$modification_id . "
											AND pds.nodeid = " . (int)$section_id . "
											AND al.linkagetypeid = 16
											ORDER BY s.description, al.datasupplierarticlenumber");
					break;
				case 'motorbike':
					return parent::select(" SELECT al.datasupplierarticlenumber part_number, s.description supplier_name, prd.description product_name
											FROM article_links al 
											JOIN motorbike_pds pds on al.supplierid = pds.supplierid
											JOIN suppliers s on s.id = al.supplierid
											JOIN motorbike_prd prd on prd.id = al.productid
											WHERE al.productid = pds.productid
											AND al.linkageid = pds.motorbikeid
											AND al.linkageid = " . (int)$modification_id . "
											AND pds.nodeid = " . (int)$section_id . "
											AND al.linkagetypeid = 777
											ORDER BY s.description, al.datasupplierarticlenumber");
					break;
				case 'engine':
					return parent::select(" SELECT pds.engineid, al.datasupplierarticlenumber part_number, prd.description product_name, s.description supplier_name
											FROM article_links al 
											JOIN engine_pds pds on al.supplierid = pds.supplierid
											JOIN suppliers s on s.id = al.supplierid
											JOIN engine_prd prd on prd.id = al.productid
											WHERE al.productid = pds.productid
											AND al.linkageid = pds.engineid
											AND al.linkageid = " . (int)$modification_id . "
											AND pds.nodeid = " . (int)$section_id . "
											AND al.linkagetypeid = 14
											ORDER BY s.description, al.datasupplierarticlenumber");
					break;
				case 'axle':
					return parent::select(" SELECT pds.axleid, al.datasupplierarticlenumber part_number, prd.description product_name, s.description supplier_name
											FROM article_links al 
											JOIN axle_pds pds on al.supplierid = pds.supplierid
											JOIN suppliers s on s.id = al.supplierid
											JOIN axle_prd prd on prd.id = al.productid
											WHERE al.productid = pds.productid
											AND al.linkageid = pds.axleid
											AND al.linkageid = " . (int)$modification_id . "
											AND pds.nodeid = " . (int)$section_id . "
											AND al.linkagetypeid = 19
											ORDER BY s.description, al.datasupplierarticlenumber");
					break;

				}
		}
		
//====================================//
// (3) Информация об изделии
//===================================//	
	
	// (3.1) Оригинальные номера
	static function getOemNumbers( $number, $brand_id )
		{
			return parent::select("
					SELECT m.description, a.OENbr FROM article_oe a 
					JOIN manufacturers m ON m.id=a.manufacturerId 
					WHERE a.datasupplierarticlenumber='" . $number . "' AND a.supplierid='" . $brand_id . "'
				");
		}		
		
	// (3.2) Статус изделия
	static function getArtStatus( $number, $brand_id )
		{
			return parent::select("
					SELECT NormalizedDescription, ArticleStateDisplayValue FROM articles WHERE DataSupplierArticleNumber='" . $number . "' AND supplierId='" . $brand_id . "'
				");
		}	
		
	// (3.3) Характеристики изделия
	static function getArtAttributes( $number, $brand_id )
		{
			return parent::select("
					SELECT attributeinformationtype, displaytitle, displayvalue FROM article_attributes WHERE datasupplierarticlenumber='" . $number . "'  AND a.supplierid='" . $brand_id . "'
				");
		}	
		
	// (3.4) Файлы изделия
	static function getArtFiles( $number, $brand_id )
		{
			return parent::select("
					SELECT Description, PictureName FROM article_images WHERE DataSupplierArticleNumber='" . $number . "'  AND a.supplierId='" . $brand_id . "'
				");
		}
		
	// (3.5) Применимость изделия
	static function getArtVehicles( $number, $brand_id )
		{
			$result = [];
			$rows = parent::select("
					SELECT linkageTypeId, linkageId FROM article_li WHERE DataSupplierArticleNumber='" . $number . "' AND supplierId='" . $brand_id . "'
				");
			foreach ( $rows as &$row ){
				switch ($type) {
				case 'PassengerCar':
					$result[ $row['linkageTypeId'] ][] =  parent::select("SELECT DISTINCT p.id, mm.description make, m.description model, p.constructioninterval, p.description FROM passanger_cars p 
																			JOIN models m ON m.id=p.modelid
																			JOIN manufacturers mm ON mm.id=m.manufacturerid
																			WHERE p.id=" . $row['linkageTypeId'] );
					break;
				case 'CommercialVehicle':
					$result[ $row['linkageTypeId'] ][] = parent::select("SELECT DISTINCT p.id, mm.description make, m.description model, p.constructioninterval, p.description FROM commercial_vehicles p 
																			JOIN models m ON m.id=p.modelid
																			JOIN manufacturers mm ON mm.id=m.manufacturerid
																			WHERE p.id=" . $row['linkageTypeId'] );
					break;
				case 'Motorbike':
					$result[ $row['linkageTypeId'] ][] = parent::select("SELECT DISTINCT p.id, mm.description make, m.description model, p.constructioninterval, p.description FROM motorbikes p 
																			JOIN models m ON m.id=p.modelid
																			JOIN manufacturers mm ON mm.id=m.manufacturerid
																			WHERE p.id=" . $row['linkageTypeId'] );
					break;
				case 'Engine':
					$result[ $row['linkageTypeId'] ][] = parent::select("SELECT DISTINCT p.id, m.description make, '' model, p.constructioninterval, p.description FROM `engines` p 
																			JOIN manufacturers m ON m.id=p.manufacturerid
																			WHERE p.id=" . $row['linkageTypeId'] );
					break;
				case 'Axle':
					$result[ $row['linkageTypeId'] ][] = parent::select("SELECT DISTINCT p.id, mm.description make, m.description model, p.constructioninterval, p.description FROM axles p 
																			JOIN models m ON m.id=p.modelid
																			JOIN manufacturers mm ON mm.id=m.manufacturerid
																			WHERE p.id=" . $row['linkageTypeId'] );
					break;

				}				
			}
			return $result;
		}	
		
	// (3.6) Замены изделия
	static function getArtReplace( $number, $brand_id )
		{
			return parent::select("
					SELECT s.description supplier, a.replacenbr number FROM article_rn a 
					JOIN suppliers s ON s.id=a.replacesupplierid
					WHERE a.datasupplierarticlenumber='" . $number . "' AND a.supplierid='" . $brand_id . "'
				");
		}
		
	// (3.7) Аналоги-заменители
	static function getArtCross( $number, $brand_id )
		{
			return parent::select("
					SELECT DISTINCT s.description, c.PartsDataSupplierArticleNumber FROM article_oe a 
					JOIN manufacturers m ON m.id=a.manufacturerId 
					JOIN article_cross c ON c.OENbr=a.OENbr
					JOIN suppliers s ON s.id=c.SupplierId
					WHERE a.datasupplierarticlenumber='". $number ."' AND a.supplierid='" . $brand_id . "'
				");
		}	
		
	// (3.8) Комплектующие (части) изделия
	static function getArtParts( $number, $brand_id )
		{
			return parent::select("
					SELECT DISTINCT description Brand, Quantity, PartsDataSupplierArticleNumber FROM article_parts 
					JOIN suppliers ON id=PartsSupplierId
					WHERE DataSupplierArticleNumber='". $number ."' AND supplierId='" . $brand_id . "'
				");
		}
}
