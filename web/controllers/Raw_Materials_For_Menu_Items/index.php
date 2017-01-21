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
$app->match('/Raw_Materials_For_Menu_Item/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {
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
        'ItemIt_ID', 'Item_Description', 'Raw_Material_ID', 'Description', 'Quantity'

    );
    
    $table_columns_type = array(
		'varchar(6)',
        'varchar(32)',
		'int(10)',
        'varchar(32)',
		'double(8,2)',

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
    
    $recordsTotal = $app['db']->executeQuery("SELECT RM.ItemIt_ID, M.Item_Description, RM.Raw_Material_ID, R.Description, RM.Quantity
                    FROM Raw_Materials_For_Menu_Item RM, Raw_Material_Inventory R, Menu_Item_Inventory M
                    WHERE RM.ItemIt_ID = M.ItemIt_ID AND RM.Raw_Material_ID = R.Raw_Material_ID" )->rowCount();
    
    $find_sql = "SELECT * FROM (SELECT
                        RM.ItemIt_ID,
                        M.Item_Description,
                        RM.Raw_Material_ID,
                        R.Description,
                        RM.Quantity
                    FROM
                        Raw_Materials_For_Menu_Item RM,
                        Raw_Material_Inventory R,
                        Menu_Item_Inventory M
                    WHERE
                        RM.ItemIt_ID = M.ItemIt_ID
                            AND RM.Raw_Material_ID = R.Raw_Material_ID) A". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;

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

$app->match('/Raw_Materials_For_Menu_Item', function () use ($app) {
    
	$table_columns = array(
        'ItemIt_ID', 'Item_Description', 'Raw_Material_ID', 'Description', 'Quantity'

    );

    $primary_key = array("Raw_Material_ID","ItemIt_ID");

    return $app['twig']->render('Raw_Materials_For_Menu_Item/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('Raw_Materials_For_Menu_Item_list');

$app->match('/Raw_Materials_For_Menu_Item/create', function (\Symfony\Component\HttpFoundation\Request $request) use ($app) {


    $find_sql = "SELECT ItemIt_ID,Item_Description FROM Menu_Item_Inventory";
    $row_sql = $app['db']->fetchAll($find_sql);

    $find_raw_sql = "SELECT Raw_Material_ID,Description FROM Raw_Material_Inventory;";
    $row_raw_sql = $app['db']->fetchAll($find_raw_sql);



    $initial_data = array(
		'Quantity' => '1',
		'ItemIt_ID' =>'' ,
		'Raw_Material_ID' =>'' ,
    );

    foreach($row_sql as $key=>$value){
        $initial_data['ItemIt_ID'][$value['ItemIt_ID']] =  $value['Item_Description'];
    }

    foreach($row_raw_sql as $key=>$value){
        $initial_data['Raw_Material_ID'][$value['Raw_Material_ID']] =  $value['Description'];
    }

    $form = $app['form.factory']->createBuilder('form', $initial_data);

	$form = $form->add('Quantity', 'text', array('required' => true));

	$form = $form->add('ItemIt_ID', 'choice', array(
        'choices' => $initial_data['ItemIt_ID'],
        'label'=>'Menu Item',
    ));

    $form = $form->add('Raw_Material_ID', 'choice', array(
        'choices' => $initial_data['Raw_Material_ID'],
        'expanded' => false,
        'label'=>'Raw Item',
    ));

    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "INSERT INTO `Raw_Materials_For_Menu_Item` (`ItemIt_ID`, `Raw_Material_ID`, `Quantity`) VALUES (?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['ItemIt_ID'], $data['Raw_Material_ID'], $data['Quantity']));


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'Raw Material For Menu Item created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('Raw_Materials_For_Menu_Item_list'));

        }
    }

    return $app['twig']->render('Raw_Materials_For_Menu_Item/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('Raw_Materials_For_Menu_Item_create');

$app->match('/Raw_Materials_For_Menu_Item/edit/{ItemIt_ID}/{Raw_Material_ID}', function ($ItemIt_ID,$Raw_Material_ID) use ($app) {

    $find_sql = "SELECT * FROM `Raw_Materials_For_Menu_Item` WHERE `ItemIt_ID` = ? AND `Raw_Material_ID` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($ItemIt_ID,$Raw_Material_ID));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('Raw_Materials_For_Menu_Item_list'));
    }

    
    $initial_data = array(
		'ItemIt_ID' => $row_sql['ItemIt_ID'],
		'Raw_Material_ID' => $row_sql['Raw_Material_ID'],
		'Quantity' => $row_sql['Quantity'],

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('ItemIt_ID', 'text', array('required' => true));
	$form = $form->add('Raw_Material_ID', 'text', array('required' => true));
	$form = $form->add('Quantity', 'text', array('required' => true));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "UPDATE `Raw_Materials_For_Menu_Item` SET `Quantity` = ? WHERE `ItemIt_ID` = ? AND `Raw_Material_ID` = ?";
            $app['db']->executeUpdate($update_query, array($data['Quantity'],$ItemIt_ID, $Raw_Material_ID));


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'Raw_Materials_For_Menu_Item edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('Raw_Materials_For_Menu_Item_edit', array("ItemIt_ID" => $ItemIt_ID,"Raw_Material_ID" => $Raw_Material_ID)));

        }
    }

    return $app['twig']->render('Raw_Materials_For_Menu_Item/edit.html.twig', array(
        "form" => $form->createView(),
        "ItemIt_ID" => $ItemIt_ID,
        "Raw_Material_ID" => $Raw_Material_ID
    ));
        
})
->bind('Raw_Materials_For_Menu_Item_edit');

$app->match('/Raw_Materials_For_Menu_Item/delete/{ItemIt_ID}/{Raw_Material_ID}', function ($ItemIt_ID, $Raw_Material_ID) use ($app) {

    $find_sql = "SELECT * FROM `Raw_Materials_For_Menu_Item` WHERE `Raw_Material_ID` = ? AND `ItemIt_ID` = ? ";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($Raw_Material_ID, $ItemIt_ID));

    if($row_sql){
        $delete_query = "DELETE FROM `Raw_Materials_For_Menu_Item` WHERE `Raw_Material_ID` = ? AND `ItemIt_ID` = ?";
        $app['db']->executeUpdate($delete_query, array($Raw_Material_ID, $ItemIt_ID));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'Raw_Materials_For_Menu_Item deleted!',
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

    return $app->redirect($app['url_generator']->generate('Raw_Materials_For_Menu_Item_list'));

})
->bind('Raw_Materials_For_Menu_Item_delete');






