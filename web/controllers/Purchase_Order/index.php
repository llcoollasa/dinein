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

$app->match('/Purchase_Order/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
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
		'Order_ID', 
		'Order_Date', 
		'Order_Delivery_date', 
		'order_type', 

    );
    
    $table_columns_type = array(
		'int(10)', 
		'date', 
		'date', 
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
    
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `Purchase_Order`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `Purchase_Order`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
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
$app->match('/Purchase_Order/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . Purchase_Order . " WHERE ".$idfldname." = ?";
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



$app->match('/Purchase_Order', function () use ($app) {
    
	$table_columns = array(
		'Order_ID', 
		'Order_Date', 
		'Order_Delivery_date', 
		'order_type', 

    );

    $primary_key = "Order_ID";	

    return $app['twig']->render('Purchase_Order/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('Purchase_Order_list');



$app->match('/Purchase_Order/create', function () use ($app) {
    
    $initial_data = array(
		'Order_Date' => '', 
		'Order_Delivery_date' => '', 
		'order_type' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);



	$form = $form->add('Order_Date', 'text', array('required' => false));
	$form = $form->add('Order_Delivery_date', 'text', array('required' => false));
	$form = $form->add('order_type', 'text', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "INSERT INTO `Purchase_Order` (`Order_Date`, `Order_Delivery_date`, `order_type`) VALUES (?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['Order_Date'], $data['Order_Delivery_date'], $data['order_type']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'Purchase_Order created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('Purchase_Order_list'));

        }
    }

    return $app['twig']->render('Purchase_Order/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('Purchase_Order_create');



$app->match('/Purchase_Order/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `Purchase_Order` WHERE `Order_ID` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('Purchase_Order_list'));
    }

    
    $initial_data = array(
		'Order_Date' => $row_sql['Order_Date'], 
		'Order_Delivery_date' => $row_sql['Order_Delivery_date'], 
		'order_type' => $row_sql['order_type'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('Order_Date', 'text', array('required' => false));
	$form = $form->add('Order_Delivery_date', 'text', array('required' => false));
	$form = $form->add('order_type', 'text', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "UPDATE `Purchase_Order` SET `Order_Date` = ?, `Order_Delivery_date` = ?, `order_type` = ? WHERE `Order_ID` = ?";
            $app['db']->executeUpdate($update_query, array($data['Order_Date'], $data['Order_Delivery_date'], $data['order_type'], $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'Purchase_Order edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('Purchase_Order_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('Purchase_Order/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('Purchase_Order_edit');



$app->match('/Purchase_Order/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `Purchase_Order` WHERE `Order_ID` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `Purchase_Order` WHERE `Order_ID` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'Purchase_Order deleted!',
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

    return $app->redirect($app['url_generator']->generate('Purchase_Order_list'));

})
->bind('Purchase_Order_delete');






