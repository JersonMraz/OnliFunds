<?php
    include "connection.php";

    if (isset($_GET['id'])) {
        $campaign_id = $_GET['id'];
        $query = "DELETE FROM projects WHERE proj_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $campaign_id);
        
        if ($stmt->execute()) {
            header("Location: my-projects.php?status=success");
            exit();
        } else {
            header("Location: my-projects.php?status=error");
            exit();
        }
    } else {
        header("Location: my-projects.php?status=invalid");
        exit();
    }
    $conn->close();
?>
