<?
$db = mysqli_connect(
    "127.0.0.1",
    "root",
    "",
    "tierlist"
);
if (!$db) {
    die('connect error');
}
