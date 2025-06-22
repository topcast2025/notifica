<?php

if (!defined("WHMCS")) {
    die("Este arquivo nПлкo pode ser acessado diretamente.");
}

if (!function_exists('format_as_currency')) {
    function format_as_currency($amount) {
        return 'R$ ' . number_format($amount, 2, ',', '.');
    }
}

function get_client_info($userid) {
    $result = select_query('tblclients', '*', ['id' => $userid]);
    return mysql_fetch_array($result);
}

function get_invoice_info($invoiceid) {
    $result = select_query('tblinvoices', '*', ['id' => $invoiceid]);
    return mysql_fetch_array($result);
}

function get_service_info($serviceid) {
    $result = select_query('tblhosting', '*', ['id' => $serviceid]);
    return mysql_fetch_array($result);
}

function get_domain_info($domainid) {
    $result = select_query('tbldomains', '*', ['id' => $domainid]);
    return mysql_fetch_array($result);
}

function get_ticket_info($ticketid) {
    $result = select_query('tbltickets', '*', ['id' => $ticketid]);
    return mysql_fetch_array($result);
}

function get_department_name($department_id) {
    try {
        if (!$department_id) {
            return '';
        }
        
        $result = select_query('tblticketdepartments', 'name', ['id' => $department_id]);
        $department = mysql_fetch_array($result);
        
        return $department ? $department['name'] : '';
    } catch (Exception $e) {
        logActivity("Erro ao buscar nome do departamento: " . $e->getMessage());
        return '';
    }
}
?>