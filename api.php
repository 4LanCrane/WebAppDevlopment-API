<?php


class myApi
{
    private $conn;

    public function __construct()
    {
        $host = 'localhost';
        $user = 'root';
        $password = '';
        $db = 'testingapi';
        $conn = mysqli_connect($host, $user, $password, $db);

        if (!$conn) {
            die('Database Connection Error');
        } else {
            $this->conn = $conn;
        }
    }

    public function handelRequest()
    {

        $request = $_SERVER['REQUEST_METHOD'];

        if (!$this->conn) {
            http_response_code(500);
            return;
        } else {

            switch ($request) {
                case 'GET':
                    if (isset($_GET['oid'])) {
                        $this->getdata();
                    } else {
                        http_response_code(400);//Bad Request
                    }
                    break;
                case 'POST':
                    if (isset($_POST['oid'])) {
                        $this->insertdata();
                    } else {
                        http_response_code(400);//Bad Request
                    }
                    break;

                default:
                    http_response_code(400);//Bad Request
                    break;
            }
        }
    }


    public function getdata()
    {
        try {
            header('Content-Type: application/json');
            $oid = $_GET['oid'];
            $result = $this->conn->prepare("SELECT * FROM myapi WHERE `oid` = ?");
            $result->bind_param("s", $oid);
            $result->execute();
            $result = $result->get_result();
            $data = array();
            if (mysqli_num_rows($result) > 0) {
                while ($row = $result->fetch_assoc()) {
                    array_push($data, $row);
                }
            }
            $result->close();
            $response['oid'] = $oid;
            $response['comments'] = $data;
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
        }
    }

    public function insertdata()
    {
        try {
            header('Content-Type: application/json');
            $oid = $_POST['oid'];
            $name = $_POST['name'];
            $comment = $_POST['comment'];

            // Prepare an SQL statement
            $request = $this->conn->prepare("INSERT INTO myapi (`name`, `comment`, `oid`) VALUES (?, ?, ?)");

            // Bind parameters to the SQL statement
            $request->bind_param("sss", $name, $comment, $oid);

            // Execute the prepared statement
            $request->execute();

            // Close the statement
            $request->close();

            $response['id'] = $this->conn -> insert_id;
            echo json_encode($response);

         
        } catch (Exception $e) {
            http_response_code(500);
        }
    }

}


$api = new myApi();
$api->handelRequest();

?>