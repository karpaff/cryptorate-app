<?php
// Удаляем куки
setcookie("username", "", time() - 3600, "/");
setcookie("role", "", time() - 3600, "/");
// Перенаправляем на главную страницу
header("Location: ../index.php");
exit;
?>
