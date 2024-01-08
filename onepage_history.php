<?php
$conn = new mysqli('localhost', 'datadb', 'password', 'datadb');

if (isset($_POST['text'])) {
    $sql = "INSERT INTO pb (data) VALUES ('" . $conn->real_escape_string($_POST['text']) . "')";
    $conn->query($sql);
    die;
}
?>

<div class="container"><textarea id="content"><?php
$sql = "SELECT data FROM pb ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data = $row["data"];
        echo htmlspecialchars($data);
    }
}
?></textarea></div>
<pre id="printable"></pre>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/styles.css">
<script src="/script.js"></script>
