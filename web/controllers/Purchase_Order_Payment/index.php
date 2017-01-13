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

$app->match('/Purchase_Order_Payment/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
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
		'Payment_Id', 
		'Order_ID', 
		'Amount', 
		'Payment_Date', 
		'Customer_Contact', 
		'Payment_Type', 

    );
    
    $table_columns_type = array(
		'int(10)', 
		'int(10)', 
		'double(8,2)', 
		'varchar(50)', 
		'varchar(10)', 
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
    
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `Purchase_Order_Payment`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `Purchase_Order_Payment`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
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
$app->match('/Purchase_Order_Payment/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . Purchase_Order_Payment . " WHERE ".$idfldname." = ?";
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



$app->match('/Purchase_Order_Payment', function () use ($app) {
    
	$table_columns = array(
		'Payment_Id', 
		'Order_ID', 
		'Amount', 
		'Payment_Date', 
		'Customer_Contact', 
		'Payment_Type', 

    );

    $primary_key = "Payment_Id";	

    return $app['twig']->render('Purchase_Order_Payment/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('Purchase_Order_Payment_list');



$app->match('/Purchase_Order_Payment/create', function () use ($app) {
    
    $initial_data = array(
		'Order_ID' => '', 
		'Amount' => '', 
		'Payment_Date' => '', 
		'Customer_Contact' => '', 
		'Payment_Type' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);



	$form = $form->add('Order_ID', 'text', array('required' => false));
	$form = $form->add('Amount', 'text', array('required' => false));
	$form = $form->add('Payment_Date', 'text', array('required' => false));
	$form = $form->add('Customer_Contact', 'text', array('required' => false));
	$form = $form->add('Payment_Type', 'text', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "INSERT INTO `Purchase_Order_Payment` (`Order_ID`, `Amount`, `Payment_Date`, `Customer_Contact`, `Payment_Type`) VALUES (?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['Order_ID'], $data['Amount'], $data['Payment_Date'], $data['Customer_Contact'], $data['Payment_Type']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'Purchase_Order_Payment created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('Purchase_Order_Payment_list'));

        }
    }

    return $app['twig']->render('Purchase_Order_Payment/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('Purchase_Order_Payment_create');



$app->match('/Purchase_Order_Payment/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `Purchase_Order_Payment` WHERE `Payment_Id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('Purchase_Order_Payment_list'));
    }

    
    $initial_data = array(
		'Order_ID' => $row_sql['Order_ID'], 
		'Amount' => $row_sql['Amount'], 
		'Payment_Date' => $row_sql['Payment_Date'], 
		'Customer_Contact' => $row_sql['Customer_Contact'], 
		'Payment_Type' => $row_sql['Payment_Type'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('Order_ID', 'text', array('required' => false));
	$form = $form->add('Amount', 'text', array('required' => false));
	$form = $form->add('Payment_Date', 'text', array('required' => false));
	$form = $form->add('Customer_Contact', 'text', array('required' => false));
	$form = $form->add('Payment_Type', 'text', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "UPDATE `Purchase_Order_Payment` SET `Order_ID` = ?, `Amount` = ?, `Payment_Date` = ?, `Customer_Contact` = ?, `Payment_Type` = ? WHERE `Payment_Id` = ?";
            $app['db']->executeUpdate($update_query, array($data['Order_ID'], $data['Amount'], $data['Payment_Date'], $data['Customer_Contact'], $data['Payment_Type'], $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'Purchase_Order_Payment edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('Purchase_Order_Payment_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('Purchase_Order_Payment/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('Purchase_Order_Payment_edit');



$app->match('/Purchase_Order_Payment/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `Purchase_Order_Payment` WHERE `Payment_Id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `Purchase_Order_Payment` WHERE `Payment_Id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'Purchase_Order_Payment deleted!',
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

    return $app->redirect($app['url_generator']->generate('Purchase_Order_Payment_list'));

})
->bind('Purchase_Order_Payment_delete');






