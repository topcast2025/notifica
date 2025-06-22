<?php

if (!defined("WHMCS")) {
    die("Este arquivo não pode ser acessado diretamente.");
}

require_once __DIR__ . '/utils.php';

function get_default_templates() {
    return [
        'client_login' => [
            'name' => 'Login do Cliente',
            'message' => "👋 Olá {firstname}!\n\nDetectamos um novo acesso à sua conta em {date}.\n\nSe não foi você, entre em contato conosco imediatamente.\n\n*IP:* {ip_address}\n*Navegador:* {browser}"
        ],
        'client_register' => [
            'name' => 'Registro de Cliente',
            'message' => "✨ Bem-vindo(a) {firstname}!\n\nSua conta foi criada com sucesso.\n\n*Dados de acesso:*\nEmail: {email}\nÁrea do cliente: {system_url}"
        ],
        'invoice_created' => [
            'name' => 'Fatura Criada',
            'message' => "📋 *Nova Fatura*\n\nOlá {firstname},\n\nUma nova fatura foi gerada para você:\n\n*Fatura:* #{invoiceid}\n*Valor:* {amount}\n*Vencimento:* {duedate}\n\nPara visualizar e pagar sua fatura, acesse:\n{invoice_url}"
        ],
        'invoice_paid' => [
            'name' => 'Fatura Paga',
            'message' => "✅ *Pagamento Confirmado*\n\nOlá {firstname},\n\nO pagamento da fatura #{invoiceid} foi confirmado.\n\n*Valor:* {amount}\n*Data:* {date}\n\nObrigado pela preferência! 🙏"
        ],
        'invoice_payment_reminder' => [
            'name' => 'Lembrete de Pagamento de Fatura',
            'message' => "⚠️ *Lembrete de Pagamento*\n\nOlá {firstname},\n\nA fatura #{invoiceid} está próxima do vencimento.\n\n*Valor:* {amount}\n*Vencimento:* {duedate}\n\nEvite a suspensão dos serviços, pague agora:\n{invoice_url}"
        ],
        'invoice_payment_reminder_second' => [
            'name' => 'Lembrete de Pagamento - Segundo Aviso',
            'message' => "🚨 *Segundo Aviso de Pagamento*\n\nOlá {firstname},\n\nA fatura #{invoiceid} está vencida!\n\n*Valor:* {amount}\n*Vencimento:* {duedate}\n\nRegularize agora para evitar a suspensão:\n{invoice_url}"
        ],
        'invoice_payment_reminder_final' => [
            'name' => 'Lembrete de Pagamento - Último Aviso',
            'message' => "⛔ *ÚLTIMO AVISO DE PAGAMENTO*\n\nOlá {firstname},\n\nSua fatura #{invoiceid} está em atraso e seus serviços serão suspensos em 24 horas!\n\n*Valor:* {amount}\n*Vencimento:* {duedate}\n\nRegularize URGENTE:\n{invoice_url}"
        ],
        'hosting_created' => [
            'name' => 'Hospedagem Criada',
            'message' => "🌟 *Hospedagem Ativada*\n\nOlá {firstname},\n\nSua hospedagem foi ativada com sucesso!\n\n*Produto:* {service}\n*Domínio:* {domain}\n*Valor:* {recurringamount}/mês\n\nAcesse o painel de controle:\n{system_url}"
        ],
        'hosting_suspended' => [
            'name' => 'Após Módulo Suspender',
            'message' => "🔒 *Serviço Suspenso*\n\nOlá {firstname},\n\nSeu serviço de hospedagem foi suspenso por falta de pagamento.\n\n*Produto:* {service}\n*Domínio:* {domain}\n\nPara reativar, regularize os pagamentos em:\n{system_url}"
        ],
        'order_accepted' => [
            'name' => 'AceitarPedido_whatsapp',
            'message' => "🎉 *Pedido Aprovado*\n\nOlá {firstname},\n\nSeu pedido foi aprovado e está em processamento!\n\n*Produtos:*\n{service}\n\nAcompanhe o status em sua área do cliente:\n{system_url}"
        ],
        'service_created' => [
            'name' => 'Serviço Criado',
            'message' => "🚀 *Serviço Ativado*\n\nOlá {firstname},\n\nSeu serviço foi ativado com sucesso!\n\n*Produto:* {service}\n*Valor:* {amount}/mês\n\nAcesse sua área do cliente para mais detalhes:\n{system_url}"
        ],
        'service_suspended' => [
            'name' => 'Serviço Suspenso',
            'message' => "⚠️ *Serviço Suspenso*\n\nOlá {firstname},\n\nSeu serviço {service} foi suspenso.\n\nPara reativar, regularize os pagamentos pendentes em sua área do cliente:\n{system_url}"
        ],
        'domain_registered' => [
            'name' => 'Domínio Registrado',
            'message' => "🌐 *Domínio Registrado*\n\nOlá {firstname},\n\nSeu domínio foi registrado com sucesso!\n\n*Domínio:* {domain}\n*Validade:* {domain_nextduedate}\n\nLembre-se de configurar as DNS em sua área do cliente."
        ],
        'domain_renewal' => [
            'name' => 'Renovação de Domínio',
            'message' => "📅 *Renovação de Domínio*\n\nOlá {firstname},\n\nSeu domínio {domain} vence em {days} dias.\n\nRenove agora para evitar a suspensão do serviço:\n{domain_url}"
        ],
        'ticket_reply' => [
            'name' => 'Resposta de Ticket',
            'message' => "📬 *Nova Resposta*\n\nOlá {firstname},\n\nHá uma nova resposta no seu ticket #{ticketid}.\n\n*Assunto:* {ticket_subject}\n\nAcesse sua área do cliente para visualizar:\n{ticket_url}"
        ],
        'admin_new_order' => [
            'name' => 'Novo Pedido',
            'message' => "🛍️ *Novo Pedido Recebido*\n\nDetalhes do Cliente:\nNome: {firstname} {lastname}\nEmail: {email}\nTelefone: {phonenumber}\n\nValor: {amount}\nProdutos: {service}\n\nAcesse o painel admin para processar."
        ],
        'admin_new_ticket' => [
            'name' => 'Novo Ticket',
            'message' => "🎫 *Novo Ticket*\n\nTicket #{ticketid}\nCliente: {firstname} {lastname}\nAssunto: {ticket_subject}\nPrioridade: {ticket_priority}\n\nAcesse o painel para responder."
        ]
    ];
}

function get_template($template_key) {
    try {
        logActivity("Buscando template: " . $template_key);
        
        $result = select_query('mod_whatsapp_templates', 'message', [
            'template_key' => $template_key
        ]);
        $template = mysql_fetch_array($result);
        
        if ($template && !empty($template['message'])) {
            logActivity("Template encontrado no banco: " . $template['message']);
            return $template['message'];
        }
        
        $default_templates = get_default_templates();
        if (isset($default_templates[$template_key])) {
            $insert = [
                'template_key' => $template_key,
                'name' => $default_templates[$template_key]['name'],
                'message' => $default_templates[$template_key]['message'],
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            insert_query('mod_whatsapp_templates', $insert);
            logActivity("Template padrão inserido: " . $default_templates[$template_key]['message']);
            return $default_templates[$template_key]['message'];
        }
        
        logActivity("Template não encontrado: " . $template_key);
        return '';
    } catch (Exception $e) {
        logActivity("Erro ao buscar template: " . $e->getMessage());
        return '';
    }
}

function replace_variables($message, $vars) {
    try {
        logActivity("Iniciando substituição de variáveis para mensagem");
        
        // Informações do sistema
        $replacements = [
            '{system_url}' => rtrim(WHMCS\Config\Setting::getValue('SystemURL'), '/'),
            '{company_name}' => WHMCS\Config\Setting::getValue('CompanyName'),
            '{email_support}' => WHMCS\Config\Setting::getValue('Email'),
            '{date}' => date('d/m/Y'),
            '{time}' => date('H:i'),
            '{datetime}' => date('d/m/Y H:i'),
            '{ip_address}' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            '{browser}' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''
        ];

        // Dados do cliente
        if (isset($vars['userid'])) {
            $result = select_query('tblclients', '*', ['id' => $vars['userid']]);
            $client = mysql_fetch_array($result);
            
            if ($client) {
                $replacements = array_merge($replacements, [
                    '{firstname}' => $client['firstname'],
                    '{lastname}' => $client['lastname'],
                    '{email}' => $client['email'],
                    '{phonenumber}' => $client['phonenumber'],
                    '{companyname}' => $client['companyname'],
                    '{address1}' => $client['address1'],
                    '{address2}' => $client['address2'],
                    '{city}' => $client['city'],
                    '{state}' => $client['state'],
                    '{postcode}' => $client['postcode'],
                    '{country}' => $client['country'],
                    '{credit}' => format_as_currency($client['credit'])
                ]);
            }
        }

        // Dados da fatura
        if (isset($vars['invoiceid'])) {
            $result = select_query('tblinvoices', '*', ['id' => $vars['invoiceid']]);
            $invoice = mysql_fetch_array($result);
            
            if ($invoice) {
                $invoice_url = $replacements['{system_url}'] . '/viewinvoice.php?id=' . $invoice['id'];
                
                $replacements = array_merge($replacements, [
                    '{invoiceid}' => $invoice['id'],
                    '{amount}' => format_as_currency($invoice['total']),
                    '{subtotal}' => format_as_currency($invoice['subtotal']),
                    '{tax}' => format_as_currency($invoice['tax']),
                    '{tax2}' => format_as_currency($invoice['tax2']),
                    '{credit}' => format_as_currency($invoice['credit']),
                    '{total}' => format_as_currency($invoice['total']),
                    '{balance}' => format_as_currency($invoice['total'] - $invoice['credit']),
                    '{duedate}' => date('d/m/Y', strtotime($invoice['duedate'])),
                    '{datepaid}' => $invoice['datepaid'] ? date('d/m/Y', strtotime($invoice['datepaid'])) : '',
                    '{invoice_url}' => $invoice_url,
                    '{payment_method}' => $invoice['paymentmethod'],
                    '{status}' => $invoice['status']
                ]);
            }
        }

        // Dados do serviço
        if (isset($vars['serviceid'])) {
            $result = select_query('tblhosting', '*', ['id' => $vars['serviceid']]);
            $service = mysql_fetch_array($result);
            
            if ($service) {
                // Busca nome do produto
                $result = select_query('tblproducts', 'name', ['id' => $service['packageid']]);
                $product = mysql_fetch_array($result);
                
                $replacements = array_merge($replacements, [
                    '{service}' => $product ? $product['name'] : '',
                    '{domain}' => $service['domain'],
                    '{username}' => $service['username'],
                    '{password}' => $service['password'],
                    '{serverip}' => $service['serverip'],
                    '{serverhost}' => $service['serverhostname'],
                    '{dedicatedip}' => $service['dedicatedip'],
                    '{firstpaymentamount}' => format_as_currency($service['firstpaymentamount']),
                    '{recurringamount}' => format_as_currency($service['amount']),
                    '{billingcycle}' => $service['billingcycle'],
                    '{nextduedate}' => date('d/m/Y', strtotime($service['nextduedate'])),
                    '{status}' => $service['domainstatus']
                ]);
            }
        }

        // Dados do ticket
        if (isset($vars['ticketid'])) {
            $result = select_query('tbltickets', '*', ['id' => $vars['ticketid']]);
            $ticket = mysql_fetch_array($result);
            
            if ($ticket) {
                $ticket_url = $replacements['{system_url}'] . '/viewticket.php?tid=' . $ticket['tid'];
                
                $replacements = array_merge($replacements, [
                    '{ticketid}' => $ticket['tid'],
                    '{ticket_subject}' => $ticket['title'],
                    '{ticket_message}' => $ticket['message'],
                    '{ticket_status}' => $ticket['status'],
                    '{ticket_priority}' => $ticket['urgency'],
                    '{ticket_department}' => get_department_name($ticket['did']),
                    '{ticket_url}' => $ticket_url
                ]);
            }
        }

        // Adiciona todas as variáveis do array $vars que ainda não foram definidas
        foreach ($vars as $key => $value) {
            if (!isset($replacements['{' . $key . '}'])) {
                $replacements['{' . $key . '}'] = $value;
            }
        }

        // Remove variáveis vazias
        foreach ($replacements as $key => $value) {
            if (empty($value) && $value !== '0') {
                $replacements[$key] = '';
            }
        }

        // Aplica as substituições
        $final_message = str_replace(array_keys($replacements), array_values($replacements), $message);
        
        logActivity("Mensagem final após substituição de variáveis: " . $final_message);
        
        return $final_message;
    } catch (Exception $e) {
        logActivity("Erro ao substituir variáveis: " . $e->getMessage());
        return $message;
    }
}

function save_template($template_key, $message) {
    try {
        $result = select_query('mod_whatsapp_templates', 'id', [
            'template_key' => $template_key
        ]);
        $template = mysql_fetch_array($result);
        
        if ($template) {
            $update = [
                'message' => $message,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $success = update_query('mod_whatsapp_templates', $update, [
                'id' => $template['id']
            ]);
        } else {
            $default_templates = get_default_templates();
            $insert = [
                'template_key' => $template_key,
                'name' => isset($default_templates[$template_key]) ? $default_templates[$template_key]['name'] : $template_key,
                'message' => $message,
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $success = insert_query('mod_whatsapp_templates', $insert);
        }
        
        if (!$success) {
            logActivity("Erro ao salvar template WhatsApp: " . mysql_error());
            return [
                'success' => false,
                'message' => 'Erro ao salvar template: ' . mysql_error()
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Template salvo com sucesso!'
        ];
        
    } catch (Exception $e) {
        logActivity("Erro ao salvar template WhatsApp: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erro ao salvar template: ' . $e->getMessage()
        ];
    }
}

function get_template_last_update($template_key) {
    try {
        $result = select_query('mod_whatsapp_templates', 'updated_at', [
            'template_key' => $template_key
        ]);
        $template = mysql_fetch_array($result);
        
        return $template ? $template['updated_at'] : date('Y-m-d H:i:s');
    } catch (Exception $e) {
        logActivity("Erro ao buscar última atualização do template: " . $e->getMessage());
        return date('Y-m-d H:i:s');
    }
}
?>