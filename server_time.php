<?php
header('Content-Type: application/json');
echo json_encode(['success' => true, 'server_time' => date('c')]);
?>