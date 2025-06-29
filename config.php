<?php 
class db {
    private $host = 'switchback.proxy.rlwy.net'; 
    private $user = 'root';                      
    private $password = 'UwkjhCpxVOnCYgmQtPsTQujZCKYHgeZB'; 
    private $dbname = 'railway';                
    private $port = 30490;                      

    function connect() {
        $mysql_connect_str = "mysql:host=$this->host;port=$this->port;dbname=$this->dbname";
        $dbConnection = new PDO($mysql_connect_str, $this->user, $this->password);
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbConnection;
    }
}
?>
