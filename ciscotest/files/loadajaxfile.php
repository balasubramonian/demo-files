<?php
class Ciscocrud
{
 public $connect;
 private $host = 'localhost';
 private $username = 'root';
 private $password = '';
 private $database = 'cisco_test'; 

 function __construct()
 {
  $this->database_connect();
 }

 //Connect Database
 public function database_connect()
 {
  $this->connect = mysqli_connect($this->host, $this->username, $this->password, $this->database);
 }

//Query Execution
 public function execute_query($query)
 {
	return mysqli_query($this->connect, $query);
 }

//List all Items
 public function getallItems(){
	$query = "Select * from items";
	$result = $this->execute_query($query);
	$items_arr = [];
	 while($row = mysqli_fetch_object($result))
	   {
		   $items_arr[$row->id] = $row->item;
	   }
	  return $items_arr;
 }

//List All Types
 public function getAllTypes(){
	$query = "Select * from item_type";
	$result = $this->execute_query($query);
	$items_arr = [];
	 while($row = mysqli_fetch_object($result))
	   {
		   $items_arr[$row->id] = $row->item_type;
	   }
	  return $items_arr;
 }
 
 //Load Data in Table
 public function get_data_in_table($query)
 {
	 $result = $this->execute_query($query);
	 $res_data = [];
	 if(mysqli_num_rows($result) > 0)
	  {
		  $items = $this->getallItems();
		  $item_types = $this->getAllTypes();
		  $i = 0;
		  
		   while($row = mysqli_fetch_object($result))
		   {
				$res_data[$i]['name'] = $row->requested_by;
				$request_item = [];
				foreach(json_decode($row->Items, true) as $k => $val){
					$type = $item_types[$val['type']];
					$request_item[] = $items[$val['Item']];
				}
				$res_data[$i]['items'] = implode(",",$request_item);
				$res_data[$i]['types'] = $type;
				$res_data[$i]['action'] = '<button type="button" class="btn btn-success btn-sm" id="btn_edit" onclick="editData('.$row->id.')">Edit</button>&nbsp;<button type="button" class="btn btn-danger btn-sm" id="btn_delete" onclick="deleteData('.$row->id.',\'' .$row->requested_by. '\')">Delete</button>';
				$i++;
		   }
		   
	  }
     return json_encode($res_data);
 }
 
 //Items List
 public function getItems($query){
	 $output = '';
	 $result = $this->execute_query($query);
	 if(mysqli_num_rows($result) > 0)
	  {
	   while($row = mysqli_fetch_object($result))
	   {
		$output .= '
		<option value='.$row->id.'>'.$row->item.'</option>';
	   }
	  }
	  else
	  {
	   $output .= '<option value="0">No Records</option>';
	  }
	  
	  return $output;
     
 }
 //Types List
 public function getTypes($query){
	 $output = '';
	 $result = $this->execute_query($query);
	 if(mysqli_num_rows($result) > 0)
	  {
	   while($row = mysqli_fetch_object($result))
	   {
		$output .= '
		<option value='.$row->id.'>'.$row->item_type.'</option>';
	   }
	  }
	  else
	  {
	   $output .= '<option value="0">No Records</option>';
	  }
	  
	  return $output;
     
 }
 
 //Based on user name generare json for updation in summary table
 public function getItemrequestbyId($query,$params){
	 
	 $result = $this->execute_query($query);
	 if(mysqli_num_rows($result) > 0)
	  {
		  $summary_data = [];
		   while($row = mysqli_fetch_object($result))
		   {
				foreach(json_decode($row->Items, true) as $k => $val){
					$summary_data[$val['type']][] = $val['Item'];
				}
		   }
		   $this->insertSummary("UPDATE item_summary SET Items = '".json_encode($summary_data)."' where requested_by = '".$params['user']."'");
			 $this->execute_query($query);
	  }
 }
 
 //Check summary data present or not. If present means it will insert
 public function getItemsummary($query,$params){
	
	 $result = $this->execute_query($query);
	 if(mysqli_num_rows($result) > 0)
		{
			$this->getItemrequestbyId('SELECT * FROM item_request where requested_by= "'.$params['user'].'"',$params);
			
		}else{
		  //Generate Items json
		  $summary_data = [];
		  foreach(json_decode($params['itemdata'], true) as $k => $val){
			$summary_data[$val['type']][] = $val['Item'];
		  }
		  
		  $this->insertSummary("INSERT INTO item_summary (req_id, requested_by, ordered_on,Items) 
				VALUES ('".$params['insert_id']."','".$params['user']."', '".date('Y-m-d')."', '".json_encode($summary_data)."')");
		  $this->execute_query($query);
		}
		if($params['action'] == 'add')
			return "Data Inserted";
		else if($params['action'] == 'edit')
			return "Data Updated";
		else
			return "Data Deleted";
 }
 
 //Insert Query Call
 public function insertItems($query){
	 
	 $this->execute_query($query);
  
     $id = mysqli_insert_id($this->connect);
	 
	 return $id;

 }
 
 //Summary Execution
 public function insertSummary($query){
	 
	 $result = $this->execute_query($query);
     return "Data Inserted";
 }
 
 //Edit data result function
 public function getRequestDatabyId($query){
	$result =  $this->execute_query($query);
	$res_data = [];
	$row = mysqli_fetch_row($result);
	 $res_data = [];
	 $res_data['id'] = $row[0];
	 $res_data['name'] = $row[1];
	 foreach(json_decode($row[4], true) as $k => $val){
		$type = $val['type'];
		$request_item[] = $val['Item'];
	}
	 $res_data['items'] = $request_item;
	 $res_data['type'] = $type;
	 
	 $res_data['item_list'] = $this->getItems("SELECT * FROM items where item_type='$type' ORDER BY item asc");
	 return json_encode($res_data);
 }

 
}

//Object Call
$object = new Ciscocrud();
//Action Request
if(isset($_POST["action"]))
{
//List Items
 if($_POST["action"] == "item")
 {
	 $type_id = $_POST['type_id'];
	 echo $object->getItems("SELECT * FROM items where item_type='$type_id' ORDER BY item asc");
 }
 //List Types
 if($_POST["action"] == "type")
 {
	
	echo $object->getTypes("SELECT * FROM item_type ORDER BY item_type asc");
 }
 
 //Add or Edit
 if($_POST["action"] == "save")
 {
	$user = $_POST['user'];
	
	$item_data = $_POST['itemdata'];
	
	$txt_userid = $_POST['txt_userid'];
	
	//New Insertion
	if($txt_userid == 0){
		$params['action'] = 'add';
		$insert_id = $object->insertItems("INSERT INTO item_request (requested_by, requested_on, ordered_on,Items) 
  VALUES ('".$user."', '".date('Y-m-d')."', '".date('Y-m-d')."', '".$_POST['itemdata']."')");
	}else{
		//Update existing
		$insert_id = $txt_userid;
		$query = $object->insertSummary("UPDATE item_request SET requested_by = '".$user."',requested_on = '".date('Y-m-d')."',ordered_on = '".date('Y-m-d')."', Items = '".$_POST['itemdata']."' where id = '".$txt_userid."'");
			 $object->execute_query($query);
		;
		$params['action'] = 'edit';
	}
  
  $params['itemdata'] = $item_data;
  $params['user'] = $user;
  $params['insert_id'] = $insert_id;
  $params['itemdata'] = $_POST['itemdata'];
  echo $object->getItemsummary('SELECT * FROM item_summary where requested_by= "'.$user.'"',$params);
  
 }
 //Load Data Table
 if($_POST['action'] == "loaddata"){
	 echo $object->get_data_in_table('SELECT * FROM item_request order by id desc');
 }
 
 //Edit Request
 if($_POST['action'] == 'edit'){
	 $edit_id = $_POST['editid'];
	  echo $object->getRequestDatabyId('select * from item_request where id = "'.$edit_id.'"');
 }
 
 //Delete Request
 if($_POST['action'] == 'delete'){
	 $id = $_POST['delid'];
	 $del_username = $_POST['del_username'];
	 $query = 'Delete FROM item_request where id = "'.$id.'"';
	 $result = $object->execute_query($query);
	 
	 
	 $params['user'] = $del_username;
	 $params['action'] = 'delete';
	 
	  echo $object->getItemsummary('SELECT * FROM item_summary where requested_by= "'.$params['user'].'"',$params);
     
 }
}
?>