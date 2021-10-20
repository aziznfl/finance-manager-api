<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction extends MY_Controller {

    function __construct() {
        parent::__construct();
	$this->load->model('M_Transaction');
	
	if($_SERVER["REQUEST_METHOD"] == "OPTIONS"){
		exit();
	}
    }

	// ---------- Manage --------- //

	function manageTransactionNewFlow() {
		$response = $this->getResponseUrl();
		$isUpdateTransaction = false;
		$oldListIdTransaction = array();
		$updatedListIdTransaction = array();
		
		$data['category_id'] = $response->categoryId;
		$data['transaction_date'] = $response->date;
		$data['amount'] = $response->amount;
		$data['description'] = $response->description;
		$data['tag'] = $response->tag;
		$data['location'] = $response->location->name;
		$data['coordinate'] = $response->location->coordinate;
		if (isset($response->picture)) {
			$data['picture'] = $response->picture;
		}

		// get from headers
		$data['account_key'] = $this->input->get_request_header('currentUser', true);

		// set date time
		$addedDate = $this->input->post('addedDate');
		if ($addedDate != "" || $addedDate != null) {
			$data['added_date'] = $addedDate;
		}

		$status = "";
		$transactionId = $response->transactionId;
		if ($transactionId == null) {
			// insert transaction to database
			$status .= "add";
			$timestamp = time();
			$data["transaction_identify"] = "FMTR".$timestamp;
			$transactionId = $this->M_Transaction->addData("transaction", $data);
		} else {
			// update transaction to database
			$status .= "update";
			$isUpdateTransaction = true;
			$this->M_Transaction->updateData("transaction", $data, 'transaction_id = "'.$transactionId.'"');
		}

		// get old transaction if status transaction is update
		if ($isUpdateTransaction) {
			foreach ($this->M_Transaction->getTransactionListItems($transactionId)->result() as $item) {
				array_push($oldListIdTransaction, $item->transaction_list_id);
			}
		}

		// set transaction list
		if (isset($response->items)) {
			foreach($response->items as $item) {
				$arr["name"] = $item->name;
				$arr["price"] = $item->price;
				$arr["quantity"] = $item->qty;

				// insert transaction list to database
				$itemId = $item->itemId;
				if ($itemId == null) {
					$arr["transaction_id"] = $transactionId;
					$this->M_Transaction->addData("transaction_list", $arr);
				} else {
					$this->M_Transaction->updateData("transaction_list", $arr, "transaction_list_id = ". $itemId);

					// append id updated list to ignored from deleted
					array_push($updatedListIdTransaction, $item->itemId);
				}
			}

			// remove deleted item
			foreach (array_diff($oldListIdTransaction, $updatedListIdTransaction) as $removedItemId) {
				$this->M_Transaction->deleteData("transaction_list", "transaction_list_id = ".$removedItemId);
			}
		}

		$result = array("statusCode" => $response, "statusText" => $status);

		// return JSON
		echo json_encode($result);
	}

	// ---------- FETCH TRANSACTION ----------- //

	function getOneTransaction() {
		$accountKey = $this->getHeaderFromUrl('currentUser');
		$transactionIdentify = $this->input->get('transactionIdentify');
		$data = $this->M_Transaction->getTransactionById($transactionIdentify, $accountKey)->row();
		
		// get transaction
		$response['transactionId'] = (int)$data->transaction_id;
		$response['transactionIdentify'] = $data->transaction_identify;
		$response['transactionDate'] = $data->transaction_date;
		$response['addedDate'] = $data->added_date;
		$response['categoryId'] = (int)$data->category_id;
		$response['amount'] = (int)$data->amount;
		$response['type'] = $data->type;
		$response['description'] = $data->description;
		$response['location']['name'] = $data->location;
		$response['location']['coordinate'] = $data->coordinate;
		$response['picture'] = $data->picture;
		$response['isDeleted'] = $this->getBoolean($data->is_deleted);
		$response['child'] = array();

		// get list item transaction
		$childDatas = $this->M_Transaction->getListItemTransactionById($data->transaction_id, $accountKey)->result();
		foreach ($childDatas as $childData) {
			$child['itemId'] = $childData->transaction_list_id;
			$child['item'] = $childData->name;
			$child['price'] = $childData->price;
			$child['qty'] = $childData->quantity;
			array_push($response['child'], $child);
		}
		
		$result = array('data' => $response);
		echo json_encode($result);
	}

	function lastTransaction() {
		$accountKey = $this->getHeaderFromUrl('currentUser');
		$limit = $this->input->get('limit');
		if ($limit == "") $limit = 10;

		$arr = array();
		$result = $this->M_Transaction->getLastTransaction(10, $accountKey)->result_array();
		foreach ($result as $transaction) {
			$transaction["transaction_id"] = (int)$transaction["transaction_id"];
			$transaction["amount"] = (int)$transaction["amount"];
			$transaction["amount_text"] = number_format($transaction["amount"]);
			$transaction["category_name"] = ucwords($transaction["category_name"]);
			$transaction["category"] = Array("category_id" => (int)$transaction["category_id"], "category_name" => $transaction["category_name"], "icon" => $transaction["icon"], "position" => (int)$transaction["position"], "parent_id" => (int)$transaction["parent_id"]);
			unset($transaction["category_id"]);
			unset($transaction["category_name"]);
			unset($transaction["icon"]);
			unset($transaction["position"]);
			unset($transaction["parent_id"]);
			array_push($arr, $transaction);
		}
		echo json_encode(array("data" => $arr));
	}

	function fetchMonthSummaryTransaction() {
		$month = $this->input->get('month');
   		$year = $this->input->get('year');
		$accountKey = $this->getHeaderFromUrl('currentUser');

		$result = $this->M_Transaction->getTopTransaction($month, $year, $accountKey)->result_array();
		$all = array("data" => array(), "total" => 0);
		foreach ($result as $transaction) {
			$transaction["category_id"] = (int)$transaction["category_id"];
			$transaction["category_name"] = ucwords($transaction["category_name"]);
			$transaction["total"] = (int)$transaction["total"];
			$transaction["total_text"] = number_format($transaction["total"]);
			$transaction["percentage"] = number_format($transaction["percentage"], 2)."%";
			array_push($all["data"], $transaction);
			$all["total"] += $transaction["total"];
		}

		$all["total_text"] = number_format($all["total"]);
		echo json_encode($all);
	}

	function fetchMonthTransaction() {
		$month = $this->input->get('month');
   		$year = $this->input->get('year');
   		$category_id = $this->input->get('category_id');
   		if (!isset($category_id)) $category_id = 0;

		$accountKey = $this->getHeaderFromUrl('currentUser');
		
		$result = $this->M_Transaction->getMonthTransaction($month, $year, $category_id, $accountKey)->result_array();
		$all = array();
		foreach ($result as $transaction) {
			$response["transactionId"] = (int)$transaction["transaction_id"];
			$response["transactionIdentify"] = $transaction["transaction_identify"];
			$response["transactionDate"] = $transaction["transaction_date"];
			$response["addedDate"] = $transaction["added_date"];
			$response["description"] = $transaction["description"];
			$response["tag"] = $transaction["tag"];
			$response["type"] = $transaction["type"];
			$response["place"]["name"] = $transaction["location"];
			$response["place"]["coordinate"] = $transaction["coordinate"];
			$response["picture"] = $transaction["picture"];
			$response["total"]["value"] = (int)$transaction["amount"];
			$response["total"]["text"] = number_format($transaction["amount"]);
			$response["category"]["id"] = (int)$transaction["category_id"];
			$response["category"]["name"] = ucwords($transaction["category_name"]);
			$response["category"]["icon"] = $transaction["icon"];
			$response["category"]["parentId"] = $transaction["parent_id"];
			$response["isDeleted"] = $transaction["is_deleted"];
			$response["item"]["count"] = (int)$transaction["count_list"];
			$response["item"]["list"] = array();

			// get transaction list
			$resultLists = $this->M_Transaction->getTransactionListItems($transaction["transaction_id"])->result_array();
			$total = 0;
			foreach ($resultLists as $resultList) {
				$item["name"] = $resultList["name"];
				$item["price"]["value"] = (int)$resultList["price"];
				$item["price"]["text"] = number_format($resultList["price"]);
				$item["qty"] = $resultList["quantity"];
				$item["total"]["value"] = (int)$resultList["quantity"] * $resultList["price"];
				$item["total"]["text"] = number_format($resultList["quantity"] * $resultList["price"]);
				$item["isDeleted"] = $resultList["is_deleted"];

				$total += $item["total"]["value"];
				array_push($response["item"]["list"], $item);
			}
			$response["item"]["total"]["value"] = (int)$total;
			$response["item"]["total"]["text"] = number_format($total);

			array_push($all, $response);
		}

		echo json_encode(array("data" => $all));
	}

	function fetchRestOfTransaction() {
		$accountKey = $this->getHeaderFromUrl('currentUser');
		$date = $this->input->get('lastdate');

		$result = $this->M_Transaction->getRestOfTransaction($accountKey, $date)->result_array();
		$all = array();
		foreach($result as $transaction) {
			$response["transactionId"] = (int)$transaction["transaction_id"];
			$response["transactionIdentify"] = $transaction["transaction_identify"];
			$response["transactionDate"] = $transaction["transaction_date"];
			$response["addedDate"] = $transaction["added_date"];
			$response["description"] = $transaction["description"];
			$response["tag"] = $transaction["tag"];
			$response["type"] = $transaction["type"];
			$response["location"] = $transaction["location"];
			$response["coordinate"] = $transaction["coordinate"];
			$response["picture"] = $transaction["picture"];
			$response["amount"] = (int)$transaction["amount"];
			$response["categoryId"] = (int)$transaction["category_id"];
			$response["isDeleted"] = $transaction["is_deleted"] == 0 ? false : true;
			$response["list"] = array();

			// get transaction list
			$resultLists = $this->M_Transaction->getTransactionListItems($transaction["transaction_id"])->result_array();
			$total = 0;
			foreach ($resultLists as $resultList) {
				$item["name"] = $resultList["name"];
				$item["price"] = (int)$resultList["price"];
				$item["quantity"] = (int)$resultList["quantity"];
				$item["isDeleted"] = $resultList["is_deleted"] == 0 ? false : true;

				array_push($response["list"], $item);
			}
			
			array_push($all, $response);
		}

		echo json_encode(array("data" => $all));
	}

	//---------- REMOVE Transaction --------------//

	function removeTransaction() {
		$accountKey = $this->getHeaderFromUrl('currentUser');
		$transactionIdentify = $this->input->get('transactionId');

		$result = 0;
		if (isset($transactionIdentify)) {
			// check valid transaction
			if ($this->isValidTransactionId($transactionIdentify, $accountKey)) {
				$result = $this->M_Transaction->removeTransaction($transactionIdentify, $accountKey);
			}
		} else {
			$result = null;
		}

		echo json_encode(array("data" => $result));
	}
}
?>
