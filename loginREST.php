<?php
include ('includes/config.php');
require "./vendor/autoload.php";
use \Firebase\JWT\JWT;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
error_reporting(0);

$email = '';
$password = '';

$input = json_decode(file_get_contents("php://input"));

$email = $input->email;
$password = $input->password;

$table_name = 'tblusers ';

$query = "SELECT id,EmailId,Password,FullName FROM " . $table_name . "  WHERE EmailId=:email";
$query = $dbh->prepare($query);
$query->bindParam(':email', $email, PDO::PARAM_STR);
$query->execute();
$num = $query->rowCount();

if ($num > 0)
{
    $row = $query->fetch(PDO::FETCH_ASSOC);
    $id = $row['id'];
    $FullName = $row['FullName'];
    $EmailId = $row['EmailId'];
    $password2 = $row['Password'];

    if (password_verify($password, $password2))
    {
        // set your default time-zone
        date_default_timezone_set('Asia/Kolkata');
        $secret_key = "CARRENTAL";
        $issuer_claim = "THE_ISSUER"; // this can be the servername
        $audience_claim = "THE_AUDIENCE";
        $issuedat_claim = time(); // issued at
        $notbefore_claim = $issuedat_claim + 10; //not before in seconds
        $expire_claim = $issuedat_claim + 3600; // expire time in seconds
        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => array(
                "fullName" => $FullName,
                "email" => $EmailId
            )
        );

        //        http_response_code(200);
        http_response_code(200);

        $jwt = JWT::encode($token, $secret_key);
        echo json_encode(array(
            "status" => true,
            "message" => "Successful login.",
            "accessToken" => $jwt,
            "email" => $EmailId,
            "expireAt" => $expire_claim,
            "user" => array(
                "fullName" => $FullName,
                "email" => $EmailId
            )
        ));
    }
    else
    {
        // http_response_code(401);
        echo json_encode(array(
            "status" => false,
            "message" => "Login failed."
        ));
    }
}
else
{
    // http_response_code(401);
    echo json_encode(array(
        "status" => false,
        "message" => "Login failed."
    ));
}
?>
