<?php

if (!defined("WHMCS")) {
    die("Este arquivo não pode ser acessado diretamente.");
}

require_once __DIR__ . '/api.php';
require_once __DIR__ . '/templates.php';
require_once __DIR__ . '/logs.php';

function whatsapp_notification_config() {
    return [
        'name' => 'Notificação WhatsApp',
        'description' => 'Módulo para envio de notificações via WhatsApp usando Botconect',
        'version' => '10.0',
        'author' => 'BOTWHMCS',
        'fields' => [
            'appkey' => [
                'FriendlyName' => 'App Key',
                'Type' => 'text',
                'Size' => '60',
                'Description' => 'Chave do aplicativo Botconect'
            ],
            'authkey' => [
                'FriendlyName' => 'Auth Key',
                'Type' => 'text',
                'Size' => '60',
                'Description' => 'Chave de autenticação Botconect'
            ],
            'mobile_field' => [
                'FriendlyName' => 'Campo Número Celular',
                'Type' => 'dropdown',
                'Options' => 'Default Profile Field : Phone Number',
                'Description' => 'Campo para número do WhatsApp'
            ],
            'date_format' => [
                'FriendlyName' => 'Formato da Data',
                'Type' => 'text',
                'Value' => '%d.%m.%y',
                'Description' => 'Formato da data para mensagens'
            ],
            'disabled' => [
                'FriendlyName' => 'Temporariamente Desativado',
                'Type' => 'yesno',
                'Description' => 'Desativa temporariamente o envio automático de mensagens'
            ],
            'send_pdf' => [
                'FriendlyName' => 'Enviar PDF da Fatura',
                'Type' => 'yesno',
                'Description' => 'Enviar PDF da fatura junto com a notificação'
            ]
        ]
    ];
}

function whatsapp_notification_activate() {
    $query = "CREATE TABLE IF NOT EXISTS mod_whatsapp_templates (
        id int(11) NOT NULL AUTO_INCREMENT,
        template_key varchar(50) NOT NULL,
        name varchar(100) NOT NULL,
        message text NOT NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY template_key (template_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    full_query($query);
    
    $query = "CREATE TABLE IF NOT EXISTS mod_whatsapp_logs (
        id int(11) NOT NULL AUTO_INCREMENT,
        date datetime NOT NULL,
        `to` varchar(20) NOT NULL,
        message text NOT NULL,
        status varchar(20) NOT NULL,
        response_code int(11) NOT NULL,
        response text,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    full_query($query);
    
    return [
        'status' => 'success',
        'description' => 'Módulo ativado com sucesso!'
    ];
}

function whatsapp_notification_deactivate() {
    return [
        'status' => 'success',
        'description' => 'Módulo desativado com sucesso!'
    ];
}

function whatsapp_notification_output($vars) {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    if ($action == 'test_connection') {
        $appkey = $_POST['appkey'];
        $authkey = $_POST['authkey'];
        $result = whatsapp_test_connection($appkey, $authkey);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    
    if ($action == 'save_settings') {
        $settings = $_POST;
        foreach ($settings as $key => $value) {
            update_query('tbladdonmodules', 
                ['value' => $value],
                ['module' => 'whatsapp_notification', 'setting' => $key]
            );
        }
        header('Location: addonmodules.php?module=whatsapp_notification&saved=true');
        exit;
    }

    if ($action == 'save_template') {
        $key = $_POST['template_key'];
        $message = $_POST['message'];
        $result = save_template($key, $message);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    if ($action == 'toggle_template') {
        $key = $_POST['template_key'];
        $active = (int)$_POST['active'];
        
        update_query('mod_whatsapp_templates', 
            ['active' => $active],
            ['template_key' => $key]
        );
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action == 'send_message') {
        $to = $_POST['to'];
        $message = $_POST['message'];
        $result = whatsapp_send_message($to, $message);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">';
    echo '<link href="https://cdn.jsdelivr.net/npm/emoji-mart@latest/css/emoji-mart.css" rel="stylesheet">';
    echo '<script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>';
    
    echo '<style>
        .whatsapp-notification {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            margin: 20px;
        }
        .nav-tabs {
            border: none;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 30px;
        }
        .nav-tabs > li > a {
            color: #6c757d;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 6px;
            transition: all 0.3s ease;
            border: none !important;
        }
        .nav-tabs > li > a:hover {
            background: rgba(37, 211, 102, 0.1);
            color: #25D366;
        }
        .nav-tabs > li.active > a {
            background: #25D366 !important;
            color: white !important;
            box-shadow: 0 4px 6px rgba(37, 211, 102, 0.2);
        }
        .tab-content {
            padding: 20px 0;
        }
        .btn-whatsapp {
            background: #25D366;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-whatsapp:hover {
            background: #128C7E;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(37, 211, 102, 0.2);
        }
        .btn-whatsapp i {
            font-size: 16px;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .status-success {
            background: #25D366;
            color: white;
        }
        .status-error {
            background: #DC3545;
            color: white;
        }
        .template-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            position: relative;
        }
        .template-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .template-card h4 {
            color: #2c3e50;
            font-size: 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .template-card h4 i {
            color: #25D366;
        }
        .template-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .emoji-picker {
            position: absolute;
            z-index: 1000;
            display: none;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 12px;
        }
        .emoji-trigger {
            cursor: pointer;
            padding: 8px 16px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 15px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .emoji-trigger:hover {
            background: #e9ecef;
        }
        .template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .template-textarea {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            font-size: 14px;
            line-height: 1.6;
            resize: vertical;
            transition: all 0.3s ease;
        }
        .template-textarea:focus {
            background: #ffffff;
            border-color: #25D366;
            box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.1);
        }
        .template-preview {
            margin-top: 15px;
            padding: 15px;
            background: #DCF8C6;
            border-radius: 8px;
            font-size: 14px;
            color: #2c3e50;
        }
        .template-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            color: #6c757d;
            font-size: 13px;
        }
        .template-meta i {
            color: #25D366;
        }
        .save-indicator {
            display: none;
            align-items: center;
            gap: 6px;
            color: #25D366;
            font-size: 13px;
            font-weight: 500;
        }
        .save-indicator.show {
            display: inline-flex;
        }
        .template-status {
            position: absolute;
            top: 15px;
            right: 15px;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #25D366;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .client-select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
    </style>';

    if (isset($_GET['saved'])) {
        echo '<div class="alert alert-success">Configurações salvas com sucesso!</div>';
    }

    echo '<div class="whatsapp-notification">';
    echo '<h2><i class="fab fa-whatsapp"></i> Notificação WhatsApp</h2>';
    
    echo '<div class="test-connection" style="margin-bottom: 20px;">
        <button class="btn btn-whatsapp" onclick="testWhatsAppConnection()">
            <i class="fas fa-plug"></i> Testar Conexão
        </button>
        <span id="connection-status" style="margin-left: 10px;"></span>
    </div>';

    echo '<ul class="nav nav-tabs" role="tablist">
        <li class="active"><a href="#settings" role="tab" data-toggle="tab"><i class="fas fa-cog"></i> Configurações</a></li>
        <li><a href="#client-templates" role="tab" data-toggle="tab"><i class="fas fa-user"></i> Templates Cliente</a></li>
        <li><a href="#admin-templates" role="tab" data-toggle="tab"><i class="fas fa-user-shield"></i> Templates Admin</a></li>
        <li><a href="#send-message" role="tab" data-toggle="tab"><i class="fas fa-paper-plane"></i> Enviar Mensagem</a></li>
        <li><a href="#reports" role="tab" data-toggle="tab"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
        <li><a href="#support" role="tab" data-toggle="tab"><i class="fas fa-question-circle"></i> Suporte</a></li>
    </ul>';

    echo '<div class="tab-content">
        <div class="tab-pane active" id="settings">
            ' . whatsapp_notification_settings($vars) . '
        </div>
        <div class="tab-pane" id="client-templates">
            ' . whatsapp_notification_client_templates($vars) . '
        </div>
        <div class="tab-pane" id="admin-templates">
            ' . whatsapp_notification_admin_templates($vars) . '
        </div>
        <div class="tab-pane" id="send-message">
            ' . whatsapp_notification_send_message($vars) . '
        </div>
        <div class="tab-pane" id="reports">
            ' . whatsapp_notification_reports($vars) . '
        </div>
        <div class="tab-pane" id="support">
            ' . whatsapp_notification_support($vars) . '
        </div>
    </div>';

    echo '<div id="emoji-picker" class="emoji-picker" style="display: none;"></div>';
    
    echo '<script>
        const picker = new EmojiMart.Picker({
            onEmojiSelect: addEmoji,
            set: "twitter",
            theme: "light",
            showPreview: false,
            showSkinTones: false
        });
        document.getElementById("emoji-picker").appendChild(picker);

        function testWhatsAppConnection() {
            var appkey = document.querySelector("input[name=\'appkey\']").value;
            var authkey = document.querySelector("input[name=\'authkey\']").value;
            
            if (!appkey || !authkey) {
                alert("Por favor, preencha as chaves de API primeiro!");
                return;
            }
            
            var statusEl = document.getElementById("connection-status");
            statusEl.innerHTML = "<i class=\'fas fa-spinner fa-spin\'></i> Testando conexão...";
            
            fetch("addonmodules.php?module=whatsapp_notification&action=test_connection", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "appkey=" + encodeURIComponent(appkey) + "&authkey=" + encodeURIComponent(authkey)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusEl.innerHTML = "<span class=\'status-badge status-success\'><i class=\'fas fa-check\'></i> " + data.message + "</span>";
                } else {
                    statusEl.innerHTML = "<span class=\'status-badge status-error\'><i class=\'fas fa-times\'></i> " + data.message + "</span>";
                }
            })
            .catch(error => {
                statusEl.innerHTML = "<span class=\'status-badge status-error\'><i class=\'fas fa-times\'></i> Erro ao testar conexão</span>";
            });
        }

        function saveTemplate(key, element) {
            const card = element.closest(".template-card");
            const message = card.querySelector("textarea").value;
            const saveIndicator = card.querySelector(".save-indicator");
            
            saveIndicator.classList.add("show");
            
            fetch("addonmodules.php?module=whatsapp_notification&action=save_template", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "template_key=" + encodeURIComponent(key) + "&message=" + encodeURIComponent(message)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    setTimeout(() => {
                        saveIndicator.classList.remove("show");
                    }, 2000);
                } else {
                    alert("Erro ao salvar template");
                    saveIndicator.classList.remove("show");
                }
            });
        }

        function toggleTemplate(key, element) {
            const isActive = element.checked;
            
            fetch("addonmodules.php?module=whatsapp_notification&action=toggle_template", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "template_key=" + encodeURIComponent(key) + "&active=" + (isActive ? 1 : 0)
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    element.checked = !isActive;
                    alert("Erro ao alterar status do template");
                }
            });
        }

        function updatePreview(element) {
            const card = element.closest(".template-card");
            const preview = card.querySelector(".template-preview");
            preview.textContent = element.value;
        }

        function sendMessage() {
            const to = document.querySelector("input[name=\'to\']").value;
            const message = document.querySelector("textarea[name=\'message\']").value;
            
            if (!to || !message) {
                alert("Por favor, preencha todos os campos!");
                return;
            }
            
            fetch("addonmodules.php?module=whatsapp_notification&action=send_message", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "to=" + encodeURIComponent(to) + "&message=" + encodeURIComponent(message)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Mensagem enviada com sucesso!");
                    document.querySelector("input[name=\'to\']").value = "";
                    document.querySelector("textarea[name=\'message\']").value = "";
                } else {
                    alert("Erro ao enviar mensagem: " + data.message);
                }
            });
        }

        function toggleEmojiPicker(targetId) {
            const picker = document.getElementById("emoji-picker");
            const target = document.getElementById(targetId);
            const rect = target.getBoundingClientRect();
            
            if (picker.style.display === "none") {
                picker.style.display = "block";
                picker.style.top = (rect.bottom + window.scrollY + 10) + "px";
                picker.style.left = rect.left + "px";
                picker.dataset.target = targetId;
            } else {
                picker.style.display = "none";
            }
        }

        function addEmoji(emoji) {
            const targetId = document.getElementById("emoji-picker").dataset.target;
            const target = document.getElementById(targetId);
            target.value += emoji.native;
            updatePreview(target);
        }

        document.addEventListener("click", function(e) {
            const picker = document.getElementById("emoji-picker");
            if (!e.target.closest(".emoji-trigger") && !e.target.closest(".emoji-picker")) {
                picker.style.display = "none";
            }
        });
    </script>';
    
    echo '</div>';
}

function whatsapp_notification_settings($vars) {
    $output = '<div class="settings-container">';
    
    // Adiciona o tutorial
    $output .= '<div class="alert alert-info">
        <h4><i class="fas fa-info-circle"></i> Tutorial de Configuração</h4>
        <ol>
            <li>Acesse o site <a href="https://botconect.site" target="_blank">botconect.site</a></li>
            <li>Crie uma conta ou faça login</li>
            <li>No painel, vá em "Configurações" > "API"</li>
            <li>Copie as chaves "App Key" e "Auth Key"</li>
            <li>Cole as chaves nos campos correspondentes abaixo</li>
            <li>Clique em "Salvar Configurações"</li>
            <li>Use o botão "Testar Conexão" para verificar se está tudo funcionando</li>
        </ol>
        <p><strong>Observações importantes:</strong></p>
        <ul>
            <li>Certifique-se de que o WhatsApp esteja conectado no painel do Botconect</li>
            <li>O número de telefone dos clientes deve estar no formato internacional (Ex: 5511999999999)</li>
            <li>Para enviar PDFs de faturas, ative a opção "Enviar PDF da Fatura"</li>
        </ul>
    </div>';
    
    $output .= '<h3>Configurações do WhatsApp</h3>';
    $output .= '<form method="post" action="addonmodules.php?module=whatsapp_notification&action=save_settings" class="form-horizontal">
        <div class="form-group">
            <label class="col-sm-3 control-label">App Key</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="appkey" value="' . htmlspecialchars($vars['appkey']) . '">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Auth Key</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="authkey" value="' . htmlspecialchars($vars['authkey']) . '">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Formato da Data</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="date_format" value="' . htmlspecialchars($vars['date_format']) . '">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="disabled" ' . ($vars['disabled'] == 'on' ? 'checked' : '') . '> 
                        Temporariamente Desativado
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="send_pdf" ' . ($vars['send_pdf'] == 'on' ? 'checked' : '') . '> 
                        Enviar PDF da Fatura
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button type="submit" class="btn btn-whatsapp">
                    <i class="fas fa-save"></i> Salvar Configurações
                </button>
            </div>
        </div>
    </form>';
    $output .= '</div>';
    return $output;
}

function whatsapp_notification_client_templates($vars) {
    $templates = get_default_templates();
    $output = '<div class="templates-container">';
    $output .= '<h3>Templates de Cliente</h3>';
    $output .= '<div class="template-grid">';
    
    foreach ($templates as $key => $template) {
        if (strpos($key, 'client_') === 0 || 
            strpos($key, 'invoice_') === 0 || 
            strpos($key, 'hosting_') === 0 || 
            strpos($key, 'service_') === 0 || 
            strpos($key, 'domain_') === 0 || 
            $key === 'order_accepted') {
            
            $result = select_query('mod_whatsapp_templates', '*', ['template_key' => $key]);
            $savedTemplate = mysql_fetch_array($result);
            $isActive = $savedTemplate ? (bool)$savedTemplate['active'] : true;
            
            $output .= '<div class="template-card">
                <div class="template-meta">
                    <span><i class="far fa-clock"></i> Última atualização: ' . get_template_last_update($key) . '</span>
                </div>
                <div class="template-status">
                    <label class="switch">
                        <input type="checkbox" ' . ($isActive ? 'checked' : '') . ' 
                            onchange="toggleTemplate(\'' . $key . '\', this)">
                        <span class="slider"></span>
                    </label>
                </div>
                <h4><i class="fas fa-file-alt"></i> ' . $template['name'] . '</h4>
                <div class="emoji-trigger" onclick="toggleEmojiPicker(\'template-' . $key . '\')">
                    <i class="far fa-smile"></i> Adicionar Emoji
                </div>
                <textarea 
                    id="template-' . $key . '" 
                    class="form-control template-textarea" 
                    rows="3" 
                    oninput="updatePreview(this)"
                >' . get_template($key) . '</textarea>
                <div class="template-preview">' . get_template($key) . '</div>
                <div class="template-actions">
                    <button class="btn btn-whatsapp btn-sm" onclick="saveTemplate(\'' . $key . '\', this)">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                    <div class="save-indicator">
                        <i class="fas fa-check"></i> Salvo com sucesso
                    </div>
                </div>
            </div>';
        }
    }
    
    $output .= '</div></div>';
    return $output;
}

function whatsapp_notification_admin_templates($vars) {
    $templates = [
        'admin_new_order' => [
            'name' => 'Novo Pedido',
            'message' => "🛍️ *Novo Pedido Recebido*\n\nDetalhes do Cliente:\nNome: {firstname} {lastname}\nEmail: {email}\nTelefone: {phonenumber}\n\nValor: {amount}\nProdutos: {service}\n\nAcesse o painel admin para processar."
        ],
        'admin_new_ticket' => [
            'name' => 'Novo Ticket',
            'message' => "🎫 *Novo Ticket*\n\nTicket #{ticketid}\nCliente: {firstname} {lastname}\nAssunto: {ticket_subject}\nPrioridade: {ticket_priority}\n\nAcesse o painel para responder."
        ],
        'admin_payment_received' => [
            'name' => 'Pagamento Recebido',
            'message' => "💰 *Novo Pagamento*\n\nCliente: {firstname} {lastname}\nFatura: #{invoiceid}\nValor: {amount}\nMétodo: {payment_method}"
        ],
        'admin_service_suspended' => [
            'name' => 'Serviço Suspenso',
            'message' => "⚠️ *Serviço Suspenso*\n\nCliente: {firstname} {lastname}\nServiço: {service}\nMotivo: Falta de pagamento\nFatura: #{invoiceid}"
        ],
        'admin_domain_expiring' => [
            'name' => 'Domínio Expirando',
            'message' => "🌐 *Domínio Expirando*\n\nCliente: {firstname} {lastname}\nDomínio: {domain}\nVencimento: {domain_next_due_date}"
        ]
    ];
    
    $output = '<div class="templates-container">';
    $output .= '<h3>Templates de Administrador</h3>';
    $output .= '<div class="template-grid">';
    
    foreach ($templates as $key => $template) {
        $result = select_query('mod_whatsapp_templates', '*', ['template_key' => $key]);
        $savedTemplate = mysql_fetch_array($result);
        $isActive = $savedTemplate ? (bool)$savedTemplate['active'] : true;
        
        $output .= '<div class="template-card">
            <div class="template-meta">
                <span><i class="far fa-clock"></i> Última atualização: ' . get_template_last_update($key) . '</span>
            </div>
            <div class="template-status">
                <label class="switch">
                    <input type="checkbox" ' . ($isActive ? 'checked' : '') . ' 
                        onchange="toggleTemplate(\'' . $key . '\', this)">
                    <span class="slider"></span>
                </label>
            </div>
            <h4><i class="fas fa-file-alt"></i> ' . $template['name'] . '</h4>
            <div class="emoji-trigger" onclick="toggleEmojiPicker(\'template-' . $key . '\')">
                <i class="far fa-smile"></i> Adicionar Emoji
            </div>
            <textarea 
                id="template-' . $key . '" 
                class="form-control template-textarea" 
                rows="3" 
                oninput="updatePreview(this)"
            >' . get_template($key) . '</textarea>
            <div class="template-preview">' . get_template($key) . '</div>
            <div class="template-actions">
                <button class="btn btn-whatsapp btn-sm" onclick="saveTemplate(\'' . $key . '\', this)">
                    <i class="fas fa-save"></i> Salvar
                </button>
                <div class="save-indicator">
                    <i class="fas fa-check"></i> Salvo com sucesso
                </div>
            </div>
        </div>';
    }
    
    $output .= '</div></div>';
    return $output;
}

function whatsapp_notification_send_message($vars) {
    // Busca lista de clientes
    $clients = [];
    $result = select_query('tblclients', 'id,firstname,lastname,phonenumber', '', 'firstname,lastname', 'ASC');
    while ($client = mysql_fetch_array($result)) {
        $clients[] = $client;
    }

    $output = '<div class="send-message-container">';
    $output .= '<h3>Enviar Mensagem</h3>';
    $output .= '<form class="form-horizontal" onsubmit="event.preventDefault(); sendMessage();">
        <div class="form-group">
            <label class="col-sm-3 control-label">Selecionar Cliente</label>
            <div class="col-sm-9">
                <select class="form-control client-select" onchange="document.querySelector(\'input[name=\\\'to\\\']\').value = this.value">
                    <option value="">Selecione um cliente...</option>';
    
    foreach ($clients as $client) {
        if (!empty($client['phonenumber'])) {
            $output .= '<option value="' . preg_replace('/[^0-9]/', '', $client['phonenumber']) . '">' 
                    . htmlspecialchars($client['firstname'] . ' ' . $client['lastname']) 
                    . ' (' . $client['phonenumber'] . ')</option>';
        }
    }
    
    $output .= '</select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Número</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="to" placeholder="Ex: 5511999999999">
                <small class="help-block">Digite o número manualmente ou selecione um cliente acima</small>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Mensagem</label>
            <div class="col-sm-9">
                <div class="emoji-trigger" onclick="toggleEmojiPicker(\'message-text\')">
                    <i class="far fa-smile"></i> Adicionar Emoji
                </div>
                <textarea id="message-text" class="form-control template-textarea" name="message" rows="5"></textarea>
                <div class="template-preview" style="margin-top: 15px;"></div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button type="submit" class="btn btn-whatsapp">
                    <i class="fas fa-paper-plane"></i> Enviar
                </button>
            </div>
        </div>
    </form>';
    $output .= '</div>';
    return $output;
}

function whatsapp_notification_reports($vars) {
    $output = '<div class="reports-container">';
    $output .= '<h3>Relatórios de Mensagens</h3>';
    
    $logs = get_whatsapp_logs();
    
    $output .= '<table class="table table-striped">
        <thead>
            <tr>
                <th>Data</th>
                <th>Número</th>
                <th>Mensagem</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($logs as $log) {
        $output .= '<tr>
            <td>' . $log['date'] . '</td>
            <td>' . $log['to'] . '</td>
            <td>' . htmlspecialchars($log['message']) . '</td>
            <td><span class="status-badge status-' . ($log['status'] == 'enviado' ? 'success' : 'error') . '">' 
                . ucfirst($log['status']) . '</span></td>
        </tr>';
    }
    
    $output .= '</tbody></table>';
    $output .= '</div>';
    return $output;
}

function whatsapp_notification_support($vars) {
    $output = '<div class="support-container">';
    $output .= '<h3>Suporte</h3>';
    $output .= '<div class="panel panel-default">
        <div class="panel-body">
            <h4>Documentação</h4>
            <p>Para mais informações sobre como usar este módulo, consulte nossa documentação completa.</p>
            <a href="#" class="btn btn-whatsapp">
                <i class="fas fa-book"></i> Ver Documentação
            </a>
            
            <hr>
            
            <h4>Contato</h4>
            <p>Precisa de ajuda? Entre em contato com nossa equipe de suporte.</p>
            <a href="#" class="btn btn-whatsapp">
                <i class="fas fa-headset"></i> Contatar Suporte
            </a>
        </div>
    </div>';
    $output .= '</div>';
    return $output;
}