<?php

if (!defined("WHMCS")) {
    die("Este arquivo não pode ser acessado diretamente.");
}

function whatsapp_log_message($data) {
    // Garantir que todos os campos necessários estejam presentes
    $insert = [
        'date' => date('Y-m-d H:i:s'),
        'to' => $data['to'],
        'message' => $data['message'],
        'status' => $data['status'],
        'response_code' => isset($data['response_code']) ? $data['response_code'] : 0,
        'response' => isset($data['response']) ? $data['response'] : ''
    ];
    
    // Usar full_query para debug
    $query = "INSERT INTO mod_whatsapp_logs 
        (date, `to`, message, status, response_code, response) 
        VALUES (
            '" . mysql_real_escape_string($insert['date']) . "',
            '" . mysql_real_escape_string($insert['to']) . "',
            '" . mysql_real_escape_string($insert['message']) . "',
            '" . mysql_real_escape_string($insert['status']) . "',
            '" . (int)$insert['response_code'] . "',
            '" . mysql_real_escape_string($insert['response']) . "'
        )";
    
    $result = full_query($query);
    
    if (!$result) {
        logActivity("Erro ao salvar log do WhatsApp: " . mysql_error());
    }
    
    return $result;
}

function get_whatsapp_logs($filters = [], $page = 1, $limit = 50) {
    $where = [];
    $params = [];
    
    if (!empty($filters['status'])) {
        $where[] = "status = '" . mysql_real_escape_string($filters['status']) . "'";
    }
    
    if (!empty($filters['date_from'])) {
        $where[] = "date >= '" . mysql_real_escape_string($filters['date_from']) . "'";
    }
    
    if (!empty($filters['date_to'])) {
        $where[] = "date <= '" . mysql_real_escape_string($filters['date_to']) . "'";
    }
    
    $whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
    
    $offset = ($page - 1) * $limit;
    
    $query = "SELECT * FROM mod_whatsapp_logs 
        $whereClause 
        ORDER BY date DESC 
        LIMIT $offset, $limit";
    
    $result = full_query($query);
    
    $logs = [];
    while ($row = mysql_fetch_array($result)) {
        $logs[] = [
            'id' => $row['id'],
            'date' => $row['date'],
            'to' => $row['to'],
            'message' => $row['message'],
            'status' => $row['status'],
            'response_code' => $row['response_code'],
            'response' => $row['response']
        ];
    }
    
    return $logs;
}

function get_whatsapp_logs_count($filters = []) {
    $where = [];
    
    if (!empty($filters['status'])) {
        $where[] = "status = '" . mysql_real_escape_string($filters['status']) . "'";
    }
    
    if (!empty($filters['date_from'])) {
        $where[] = "date >= '" . mysql_real_escape_string($filters['date_from']) . "'";
    }
    
    if (!empty($filters['date_to'])) {
        $where[] = "date <= '" . mysql_real_escape_string($filters['date_to']) . "'";
    }
    
    $whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
    
    $query = "SELECT COUNT(*) as total FROM mod_whatsapp_logs $whereClause";
    $result = full_query($query);
    $data = mysql_fetch_array($result);
    
    return $data['total'];
}