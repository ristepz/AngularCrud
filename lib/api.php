<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
require_once '../config.php';
require_once './pdodb.php';

global $db;
$db = pdodb::get_instance();
$pdo = $db->connect(HOST, DATABASE, USER, PASSWORD);

class api
{
    public function __construct()
    {

    }

    /*
     * Get all users
     */
    public function getAllUsers()
    {
        global $db;
        try {
            $db->select(array('id', 'first_name', 'last_name', 'email', 'country', 'ip_address'))
                ->from('users')
                ->prepare()
                ->execute();
            $data = $db->fetch();
            $out = [];
            foreach ($data as $row) {
                $out[] = ['id' => $row->id, 'first_name' => $row->first_name, 'last_name' => $row->last_name, 'email' => $row->email, 'country' => $row->country, 'ip_address' => $row->ip_address];
            }
            echo json_encode($out);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /*
     * Save data
     */
    public function saveData($form_data)
    {
        global $db;
        $db->insert_into("content")
            ->columns(array('title', 'category', 'link', 'description', 'sample_code', 'date'))
            ->values(array(trim($form_data->issue_title), trim($form_data->issue_category), trim($form_data->issue_link), trim($form_data->issue_description), trim($form_data->issue_code), strtotime('now')))
            ->execute();
        global $pdo;
        echo $pdo->lastInsertId('id');
    }

    /*
     * Get data for autocomplete field
     */
    function get_autocomplete_data($term)
    {
        $term = trim($term);
        global $db;
        $db->select(array('id', 'title', 'category', 'link', 'description', 'sample_code', 'date'))
            ->from('content')
            ->where('title LIKE :title_placeholder OR category LIKE :title_placeholder OR link LIKE :title_placeholder OR description LIKE :title_placeholder OR sample_code LIKE :title_placeholder')
            ->prepare()
            ->bind(array(
                array('name' => ':title_placeholder', 'value' => '%' . $term . '%')
            ))
            ->execute();

        $auto_data = $db->fetch();
        $out = [];
        foreach ($auto_data as $data) {
            $out[] = ['id' => $data->id, 'title' => $data->title, 'category' => $data->category, 'link' => $data->link, 'description' => $data->description, 'sample_code' => $data->sample_code, 'date' => date("d-m-Y", $data->date)];
        }
        echo json_encode($out);
    }
}

/*
 * Get all users
 */
$api = new api();
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);

if (isset($_GET['action']) && $_GET['action'] == 'get_users') {
    $api->getAllUsers();
}

/*
 * Save data
 */
if (isset($request->action) && $request->action == 'save_data') {
    $api->saveData($request->form_data);
}
/*
 * Search autocomplete
 */
if (isset($_GET['action']) && $_GET['action'] == 'get_autocomplete_data') {
    $api->get_autocomplete_data($_GET['term']);
}