<?php
// Lecture du fichier trace physique
$log_content = file_get_contents('../trace_activite.log');
echo "<pre>" . htmlspecialchars($log_content) . "</pre>";
?>
