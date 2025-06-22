<?php

if (!defined("WHMCS")) {
    die("Este arquivo não pode ser acessado diretamente.");
}

require_once __DIR__ . '/logs.php';

function whatsapp_test_connection($appkey, $authkey) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://botconect.site/api/create-message',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'appkey' => $appkey,
            'authkey' => $authkey,
            'sandbox' => 'false'
        ),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ));

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    
    if ($error) {
        logActivity("Erro na conexão WhatsApp: " . $error);
        return [
            'success' => false,
            'message' => 'Erro ao conectar com a API: ' . $error
        ];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode == 200) {
        return [
            'success' => true,
            'message' => 'Conexão estabelecida com sucesso!'
        ];
    } else {
        $errorMessage = isset($responseData['message']) ? $responseData['message'] : 'Erro desconhecido';
        logActivity("Erro na API WhatsApp: " . $errorMessage . " (HTTP: " . $httpCode . ")");
        return [
            'success' => false,
            'message' => 'Erro ao conectar com a API: ' . $errorMessage
        ];
    }
}

function whatsapp_send_message($to, $message, $attachment = null) {
    try {
        logActivity("Iniciando envio de mensagem WhatsApp para: " . $to);
        
        $moduleParams = getModuleConfigParams();
        
        if ($moduleParams['disabled'] == 'on') {
            logActivity("Módulo WhatsApp está desativado");
            return ['success' => false, 'message' => 'Módulo está temporariamente desativado.'];
        }
        
        if (empty($moduleParams['appkey']) || empty($moduleParams['authkey'])) {
            logActivity("Chaves da API não configuradas");
            return ['success' => false, 'message' => 'Chaves da API não configuradas.'];
        }
        
        $curl = curl_init();
        
        $postFields = array(
            'appkey' => $moduleParams['appkey'],
            'authkey' => $moduleParams['authkey'],
            'to' => preg_replace('/[^0-9]/', '', $to),
            'message' => $message,
            'sandbox' => 'false'
        );
        
        logActivity("Dados do envio: " . print_r($postFields, true));
        
        if ($attachment) {
            if (filter_var($attachment, FILTER_VALIDATE_URL)) {
                $postFields['file'] = $attachment;
                logActivity("Anexo URL: " . $attachment);
            } else {
                if (file_exists($attachment)) {
                    $postFields['file'] = new CURLFile($attachment);
                    logActivity("Anexo arquivo local: " . $attachment);
                } else {
                    logActivity("Erro ao enviar mensagem WhatsApp: Arquivo não encontrado - " . $attachment);
                    return ['success' => false, 'message' => 'Arquivo não encontrado'];
                }
            }
        }

        $curlOptions = array(
            CURLOPT_URL => 'https://botconect.site/api/create-message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_VERBOSE => true
        );

        if ($attachment && !filter_var($attachment, FILTER_VALIDATE_URL)) {
            $curlOptions[CURLOPT_HTTPHEADER] = array('Content-Type: multipart/form-data');
        }

        curl_setopt_array($curl, $curlOptions);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        logActivity("Resposta da API: " . $response);
        logActivity("HTTP Code: " . $httpCode);
        
        if ($error) {
            logActivity("Erro CURL: " . $error);
        }
        
        curl_close($curl);
        
        if ($error) {
            logActivity("Erro ao enviar mensagem WhatsApp: " . $error);
            whatsapp_log_message([
                'to' => $to,
                'message' => $message,
                'status' => 'erro',
                'response_code' => 0,
                'response' => $error
            ]);
            return ['success' => false, 'message' => 'Erro ao enviar mensagem: ' . $error];
        }
        
        $success = $httpCode == 200;
        
        whatsapp_log_message([
            'to' => $to,
            'message' => $message,
            'status' => $success ? 'enviado' : 'erro',
            'response_code' => $httpCode,
            'response' => $response
        ]);
        
        logActivity("Mensagem " . ($success ? "enviada" : "não enviada") . " para " . $to);
        
        return [
            'success' => $success,
            'message' => $success ? 'Mensagem enviada com sucesso!' : 'Erro ao enviar mensagem.'
        ];
    } catch (Exception $e) {
        logActivity("Exceção ao enviar mensagem WhatsApp: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro ao enviar mensagem: ' . $e->getMessage()];
    }
}

function getModuleConfigParams() {
    $result = select_query('tbladdonmodules', 'setting,value', [
        'module' => 'whatsapp_notification'
    ]);
    
    $params = [];
    while ($row = mysql_fetch_array($result)) {
        $params[$row['setting']] = $row['value'];
    }
    
    return $params;
}

function generateInvoicePDF($invoiceid) {
    try {
        require_once ROOTDIR . '/includes/invoicefunctions.php';
        
        // Verifica se a fatura existe
        $result = select_query('tblinvoices', 'id', ['id' => $invoiceid]);
        $invoice = mysql_fetch_array($result);
        
        if (!$invoice) {
            throw new Exception('Fatura não encontrada');
        }

        // Gera um nome temporário para o arquivo PDF
        $tempFile = tempnam(sys_get_temp_dir(), 'invoice_');
        
        // Gera o PDF usando a função nativa do WHMCS
        $pdfData = pdfInvoice($invoiceid);
        
        // Salva o PDF no arquivo temporário
        if (file_put_contents($tempFile, $pdfData)) {
            return $tempFile;
        } else {
            throw new Exception('Erro ao salvar arquivo PDF');
        }
    } catch (Exception $e) {
        logActivity('Erro ao gerar PDF da fatura: ' . $e->getMessage());
        return false;
    }
}
?>