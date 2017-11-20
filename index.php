
<?php


        ini_set('display_errors', 'On');
        error_reporting(E_ALL);
        define('DATABASE', 'sm2542');
        define('USERNAME', 'sm2542');
        define('PASSWORD', 'RQeS5oUJT');
        define('CONNECTION', 'sql1.njit.edu');
    class dbConn{
    //variable to hold connection object.
    protected static $db;
    //private construct - class cannot be instatiated externally.
    private function __construct() {
        try {
            // assign PDO object to db variable
            self::$db = new PDO( 'mysql:host=' . CONNECTION .';dbname=' . DATABASE, USERNAME, PASSWORD );
            self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch (PDOException $e) {
            //Output error - would normally log this to error file rather than output to user.
            echo "Connection Error: " . $e->getMessage();
        }
    }
    // get connection function. Static method - accessible without instantiation
    public static function getConnection() {
        //Guarantees single instance, if no connection object exists then create one.
        if (!self::$db) {
            //new connection object.
            new dbConn();
        }
        //return connection.
        return self::$db;
    }
}
    class collection {
    static public function create() {
        $model = new static::$modelName;
        return $model;
    }

    static public function findAll() {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet;
    }
    static public function findOne($id) {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id =' . $id;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet[0];
    }
}
    class accounts extends collection {
    protected static $modelName = 'account';
}

    class todos extends collection {
    protected static $modelName = 'todo';
}

    class model {
        protected $tableName;
        public function store_records()
        {

        if ($this->id == ''){
            $sql = $this->insert();
        } else {
            $sql = $this->update();
        }

        $db = dbConn::getConnection();
        $statement = $db->prepare($sql);


        $array = get_object_vars($this);
        foreach (array_flip($array) as $key=>$value){
            $statement->bindParam(":$value", $this->$value);
        }
        $statement->execute();
        $id = $db->lastInsertId();
        return $id;
    }

    private function insert() {

        $tableName = $this->getTablename();
        $array = get_object_vars($this);
        $columnString = implode(',', array_flip($array));
        $valueString = ':'.implode(',:', array_flip($array));
        $sql =  'INSERT INTO '.$tableName.' ('.$columnString.') VALUES ('.$valueString.')';
        return $sql;
    }
    private function update() {


        $tableName = $this->getTablename();
        $array = get_object_vars($this);
        $comma = " ";
        $sql = 'UPDATE '.$tableName.' SET ';
        foreach ($array as $key=>$value){
            if( $value != null) {
                $sql .= $comma . $key . ' = "'. $value .'"';
                $comma = ", ";
            }
        }
        $sql .= ' WHERE id='.$this->id;
        echo $sql;
        return $sql;
    }

    public function delete() {

        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'DELETE FROM ' . $tableName .'s'. ' WHERE id ='. $this->id;
        $statement = $db->prepare($sql);
        $statement->execute();
    }
}
    class account extends model {
    }
    class todo extends model {
        public $id;
        public $owneremail;
        public $ownerid;
        public $createddate;
        public $duedate;
        public $message;
        public $isdone;

        public function getTablename(){
            $tableName='todos';
        return $tableName;
        }
    }
  //this would be the method to put in the index page for accounts
    $records = todos::findAll();
    echo '<h1>Select all Records</h1>';
    echo "<table  border=\"3\">";
    foreach($records as $key=>$row) {
        echo "<tr>";
        foreach($row as $key2=>$row2){
            echo "<td>" . $row2 . "</td>";
        }
        echo "</tr>";
}
echo "</table>";

// this would be the method to put in the index page for todos
    //$records = todos::findAll();



//this code is used to get one record and is used for showing one record or updating one record
    $oneRecord = todos::findOne(1);


    $record = new todo();
    $record -> id = 5;
    $record -> delete();


    echo "<h1>Insert One Record</h1>";
    $record = new todo();
    $record->owneremail="activerec@gmail.com";
    $record->ownerid= "9";
    $record->createddate="2017-09-09 00:00:00";
    $record->duedate="2017-10-12 00:00:00";
    $record->message="inserted new data";
    $record->isdone= "0";
    $insertID = $record->store_records();




    echo "<h2>Update Records</h2>";
    $record = new todo();
    $record->id=4;
    $record->message="Test for Update Record";
    $record->isdone= "0";
    $updateID = $record -> store_records();

//echo '<h1>Updated Record</h1>';



    //$records = todos::findAll();
?>