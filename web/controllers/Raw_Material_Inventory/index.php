<?php

/*
 * This file is part of the CRUD Admin Generator project.
 *
 * Author: Jon Segador <jonseg@gmail.com>
 * Web: http://crud-admin-generator.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../src/app.php';

use Symfony\Component\Validator\Constraints as Assert;

$app->match('/Raw_Material_Inventory/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
    $start = 0;
    $vars = $request->query->all();
    $qsStart = (int)$vars["start"];
    $search = $vars["search"];
    $order = $vars["order"];
    $columns = $vars["columns"];
    $qsLength = (int)$vars["length"];    
    
    if($qsStart) {
        $start = $qsStart;
    }    
	
    $index = $start;   
    $rowsPerPage = $qsLength;
       
    $rows = array();
    
    $searchValue = $search['value'];
    $orderValue = $order[0];
    
    $orderClause = "";
    if($orderValue) {
        $orderClause = " ORDER BY ". $columns[(int)$orderValue['column']]['data'] . " " . $orderValue['dir'];
    }
    
    $table_columns = array(
		'Raw_Material_ID', 
		'Description', 
		'Reorder_level', 
		'Product_category', 
		'Unit_Buying_Price', 
		'Issued_Qunatity', 
		'Raw_Material_Type', 

    );
    
    $table_columns_type = array(
		'int(10)', 
		'varchar(100)', 
		'double(8,2)', 
		'varchar(50)', 
		'double(8,2)', 
		'double(8,2)', 
		'varchar(50)', 

    );    
    
    $whereClause = "";
    
    $i = 0;
    foreach($table_columns as $col){
        
        if ($i == 0) {
           $whereClause = " WHERE";
        }
        
        if ($i > 0) {
            $whereClause =  $whereClause . " OR"; 
        }
        
        $whereClause =  $whereClause . " " . $col . " LIKE '%". $searchValue ."%'";
        
        $i = $i + 1;
    }
    
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `Raw_Material_Inventory`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `Raw_Material_Inventory`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

		if( $table_columns_type[$i] != "blob") {
				$rows[$row_key][$table_columns[$i]] = $row_sql[$table_columns[$i]];
		} else {				if( !$row_sql[$table_columns[$i]] ) {
						$rows[$row_key][$table_columns[$i]] = "0 Kb.";
				} else {
						$rows[$row_key][$table_columns[$i]] = " <a target='__blank' href='menu/download?id=" . $row_sql[$table_columns[0]];
						$rows[$row_key][$table_columns[$i]] .= "&fldname=" . $table_columns[$i];
						$rows[$row_key][$table_columns[$i]] .= "&idfld=" . $table_columns[0];
						$rows[$row_key][$table_columns[$i]] .= "'>";
						$rows[$row_key][$table_columns[$i]] .= number_format(strlen($row_sql[$table_columns[$i]]) / 1024, 2) . " Kb.";
						$rows[$row_key][$table_columns[$i]] .= "</a>";
				}
		}

        }
    }    
    
    $queryData = new queryData();
    $queryData->start = $start;
    $queryData->recordsTotal = $recordsTotal;
    $queryData->recordsFiltered = $recordsTotal;
    $queryData->data = $rows;
    
    return new Symfony\Component\HttpFoundation\Response(json_encode($queryData), 200);
});




/* Download blob img */
$app->match('/Raw_Material_Inventory/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . Raw_Material_Inventory . " WHERE ".$idfldname." = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($rowid));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('menu_list'));
    }

    header('Content-Description: File Transfer');
    header('Content-Type: image/jpeg');
    header("Content-length: ".strlen( $row_sql[$fieldname] ));
    header('Expires: 0');
    header('Cache-Control: public');
    header('Pragma: public');
    ob_clean();    
    echo $row_sql[$fieldname];
    exit();
   
    
});



$app->match('/Raw_Material_Inventory', function () use ($app) {
    
	$table_columns = array(
		'Raw_Material_ID', 
		'Description', 
		'Reorder_level', 
		'Product_category', 
		'Unit_Buying_Price', 
		'Issued_Qunatity', 
		'Raw_Material_Type', 

    );

    $primary_key = "Raw_Material_ID";	

    return $app['twig']->render('Raw_Material_Inventory/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('Raw_Material_Inventory_list');



$app->match('/Raw_Material_Inventory/create', function () use ($app) {
    
    $initial_data = array(
		'Description' => '', 
		'Reorder_level' => '', 
		'Product_category' => '', 
		'Unit_Buying_Price' => '', 
		'Issued_Qunatity' => '', 
		'Raw_Material_Type' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);



	$form = $form->add('Description', 'text', array('required' => false));
	$form = $form->add('Reorder_level', 'text', array('required' => false));
	$form = $form->add('Product_category', 'text', array('required' => false));
	$form = $form->add('Unit_Buying_Price', 'text', array('required' => false));
	$form = $form->add('Issued_Qunatity', 'text', array('required' => false));
	$form = $form->add('Raw_Material_Type', 'text', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "INSERT INTO `Raw_Material_Inventory` (`Description`, `Reorder_level`, `Product_category`, `Unit_Buying_Price`, `Issued_Qunatity`, `Raw_Material_Type`) VALUES (?, ?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['Description'], $data['Reorder_level'], $data['Product_category'], $data['Unit_Buying_Price'], $data['Issued_Qunatity'], $data['Raw_Material_Type']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'Raw_Material_Inventory created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('Raw_Material_Inventory_list'));

        }
    }

    return $app['twig']->render('Raw_Material_Inventory/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('Raw_Material_Inventory_create');



$app->match('/Raw_Material_Inventory/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `Raw_Material_Inventory` WHERE `Raw_Material_ID` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('Raw_Material_Inventory_list'));
    }

    
    $initial_data = array(
		'Description' => $row_sql['Description'], 
		'Reorder_level' => $row_sql['Reorder_level'], 
		'Product_category' => $row_sql['Product_category'], 
		'Unit_Buying_Price' => $row_sql['Unit_Buying_Price'], 
		'Issued_Qunatity' => $row_sql['Issued_Qunatity'], 
		'Raw_Material_Type' => $row_sql['Raw_Material_Type'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('Description', 'text', array('required' => false));
	$form = $form->add('Reorder_level', 'text', array('required' => false));
	$form = $form->add('Product_category', 'text', array('required' => false));
	$form = $form->add('Unit_Buying_Price', 'text', array('required' => false));
	$form = $form->add('Issued_Qunatity', 'text', array('required' => false));
	$form = $form->add('Raw_Material_Type', 'text', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "UPDATE `Raw_Material_Inventory` SET `Description` = ?, `Reorder_level` = ?, `Product_category` = ?, `Unit_Buying_Price` = ?, `Issued_Qunatity` = ?, `Raw_Material_Type` = ? WHERE `Raw_Material_ID` = ?";
            $app['db']->executeUpdate($update_query, array($data['Description'], $data['Reorder_level'], $data['Product_category'], $data['Unit_Buying_Price'], $data['Issued_Qunatity'], $data['Raw_Material_Type'], $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'Raw_Material_Inventory edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('Raw_Material_Inventory_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('Raw_Material_Inventory/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('Raw_Material_Inventory_edit');



$app->match('/Raw_Material_Inventory/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `Raw_Material_Inventory` WHERE `Raw_Material_ID` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `Raw_Material_Inventory` WHERE `Raw_Material_ID` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'Raw_Material_Inventory deleted!',
            )
        );
    }
    else{
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );  
    }

    return $app->redirect($app['url_generator']->generate('Raw_Material_Inventory_list'));

})
->bind('Raw_Material_Inventory_delete');






