<?php
// 连接数据库
$conn = mysqli_connect('mysqlhost', 'username', 'password', 'databasename');

// 如果没有提供注释名称，或名称太长，或名称包含无效字符。
if (!isset($_GET['note']) || strlen($_GET['note']) > 64 || !preg_match('/^[a-zA-Z0-9_-]+$/', $_GET['note'])) {
    // 生成一个包含 5 个随机明确字符的名称。重定向到该名称。
    header("Location: /" . substr(str_shuffle('234579abcdefghjkmnpqrstwxyz'), -5));
    die;
}

// 对应标题的名称
$note_name = $_GET['note'];

if (isset($_POST['text'])) {
    // 更新数据库中的文件内容。
    $text = mysqli_real_escape_string($conn, $_POST['text']);
    $query = "INSERT INTO notes (note_name, note_content) VALUES ('$note_name', '$text') ON DUPLICATE KEY UPDATE note_content='$text'";
    mysqli_query($conn, $query);
    // 如果输入内容为空，则从数据库中删除文件。
    if (!strlen($_POST['text'])) {
        $query = "DELETE FROM notes WHERE note_name='$note_name'";
        mysqli_query($conn, $query);
    }
    die;
}

// 如果明确请求打印原始文件，或者客户端是 curl 或 wget，则打印原始文件
if (isset($_GET['raw']) || strpos($_SERVER['HTTP_USER_AGENT'], 'curl') === 0 || strpos($_SERVER['HTTP_USER_AGENT'], 'Wget') === 0) {
    $query = "SELECT note_content FROM notes WHERE note_name='$note_name'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        header('Content-type: text/plain');
        echo $row['note_content'];
    }
    die;
}
?>

<div class="container">
    <textarea id="content"><?php
        // 从数据库中获取文件内容并输出
        $query = "SELECT note_content FROM notes WHERE note_name='$note_name'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            echo htmlspecialchars($row['note_content'], ENT_QUOTES, 'UTF-8');
        }
        // 关闭数据库连接
        mysqli_close($conn);
    ?></textarea>
</div>
<pre id="printable"></pre>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php print $_GET['note']; ?></title>
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="/styles.css">
<script src="/script.js"></script>
