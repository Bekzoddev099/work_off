<?php
class Database
{
    private $host = 'localhost';
    private $db_name = 'work_off_tracker';
    private $username = 'beko';
    private $password = '9999';
    private $conn;

    public function connect()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }

        return $this->conn;
    }
}
?>
<?php
class WorkOffEntry
{
    private $conn;
    private $table = 'daily';

    public $id;
    public $arrived_at;
    public $leaved_at;
    public $required_work_off;
    public $worked_off;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = 'INSERT INTO ' . $this->table . ' (arrived_at, leaved_at, required_work_off, worked_off) VALUES(:arrived_at, :leaved_at, :required_work_off, :worked_off)';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':arrived_at', $this->arrived_at);
        $stmt->bindParam(':leaved_at', $this->leaved_at);
        $stmt->bindParam(':required_work_off', $this->required_work_off);
        $stmt->bindParam(':worked_off', $this->worked_off);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function read()
    {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY id DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWOT - Personal Work Off Tracker</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }

        .worked-off {
            background-color: #e6ffe6;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <h2>PWOT - Personal Work Off Tracker</h2>
        <form action="index.php" method="post" class="form-inline mb-4">
            <div class="form-group mr-2">
                <label for="arrived" class="mr-2">Arrived at:</label>
                <input type="datetime-local" id="arrived" name="arrived" class="form-control">
            </div>
            <div class="form-group mr-2">
                <label for="left" class="mr-2">Leaved at:</label>
                <input type="datetime-local" id="left" name="left" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Arrived at</th>
                    <th>Leaved at</th>
                    <th>Required work off</th>
                    <th>Worked off</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include_once 'database.php';
                include_once 'workoffentry.php';

                $database = new Database();
                $db = $database->connect();

                $workOffEntry = new WorkOffEntry($db);

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $workOffEntry->arrived_at = $_POST['arrived'];
                    $workOffEntry->leaved_at = $_POST['left'];
                    $workOffEntry->required_work_off = "1 min"; // Bu yerda kerakli ish vaqtini hisoblash kerak
                    $workOffEntry->worked_off = 0; // Yangi yozuv qo'shilganda ishlangan deb belgilash

                    if ($workOffEntry->create()) {
                        echo "<div class='alert alert-success'>Yozuv muvaffaqiyatli qo'shildi.</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Yozuv qo'shib bo'lmadi.</div>";
                    }
                }

                $result = $workOffEntry->read();
                $num = $result->rowCount();

                if ($num > 0) {
                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);
                        echo "<tr" . ($worked_off ? " class='worked-off'" : "") . ">";
                        echo "<td>{$id}</td>";
                        echo "<td>{$arrived_at}</td>";
                        echo "<td>{$leaved_at}</td>";
                        echo "<td>{$required_work_off}</td>";
                        echo "<td><input type='checkbox'" . ($worked_off ? " checked disabled" : "") . "> Worked off</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Hech qanday yozuv topilmadi.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <p>Total work off hours: 36 hours and 56 min.</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>