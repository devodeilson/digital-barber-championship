<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('system_texts', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group');
            $table->text('content_pt')->nullable();
            $table->text('content_en')->nullable();
            $table->text('content_es')->nullable();
            $table->string('description');
            $table->timestamps();
        });

        // Inserir textos padrão do sistema
        $this->seedDefaultTexts();
    }

    public function down()
    {
        Schema::dropIfExists('system_texts');
    }

    private function seedDefaultTexts()
    {
        $texts = [
            // Autenticação
            [
                'key' => 'auth.login',
                'group' => 'auth',
                'content_pt' => 'Entrar',
                'content_en' => 'Login',
                'content_es' => 'Iniciar Sesión',
                'description' => 'Botão de login'
            ],
            [
                'key' => 'auth.register',
                'group' => 'auth',
                'content_pt' => 'Registrar',
                'content_en' => 'Register',
                'content_es' => 'Registrarse',
                'description' => 'Botão de registro'
            ],
            [
                'key' => 'auth.logout',
                'group' => 'auth',
                'content_pt' => 'Sair',
                'content_en' => 'Logout',
                'content_es' => 'Cerrar Sesión',
                'description' => 'Botão de logout'
            ],

            // Menu Principal
            [
                'key' => 'menu.home',
                'group' => 'menu',
                'content_pt' => 'Início',
                'content_en' => 'Home',
                'content_es' => 'Inicio',
                'description' => 'Menu início'
            ],
            [
                'key' => 'menu.championships',
                'group' => 'menu',
                'content_pt' => 'Campeonatos',
                'content_en' => 'Championships',
                'content_es' => 'Campeonatos',
                'description' => 'Menu campeonatos'
            ],
            [
                'key' => 'menu.profile',
                'group' => 'menu',
                'content_pt' => 'Meu Perfil',
                'content_en' => 'My Profile',
                'content_es' => 'Mi Perfil',
                'description' => 'Menu perfil'
            ],

            // Campeonatos
            [
                'key' => 'championships.new',
                'group' => 'championships',
                'content_pt' => 'Novo Campeonato',
                'content_en' => 'New Championship',
                'content_es' => 'Nuevo Campeonato',
                'description' => 'Título novo campeonato'
            ],
            [
                'key' => 'championships.edit',
                'group' => 'championships',
                'content_pt' => 'Editar Campeonato',
                'content_en' => 'Edit Championship',
                'content_es' => 'Editar Campeonato',
                'description' => 'Título editar campeonato'
            ],
            [
                'key' => 'championships.name',
                'group' => 'championships',
                'content_pt' => 'Nome do Campeonato',
                'content_en' => 'Championship Name',
                'content_es' => 'Nombre del Campeonato',
                'description' => 'Campo nome do campeonato'
            ],
            [
                'key' => 'championships.description',
                'group' => 'championships',
                'content_pt' => 'Descrição',
                'content_en' => 'Description',
                'content_es' => 'Descripción',
                'description' => 'Campo descrição'
            ],
            [
                'key' => 'championships.rules',
                'group' => 'championships',
                'content_pt' => 'Regras',
                'content_en' => 'Rules',
                'content_es' => 'Reglas',
                'description' => 'Campo regras'
            ],
            [
                'key' => 'championships.prize',
                'group' => 'championships',
                'content_pt' => 'Prêmio',
                'content_en' => 'Prize',
                'content_es' => 'Premio',
                'description' => 'Campo prêmio'
            ],

            // Botões e Ações
            [
                'key' => 'buttons.save',
                'group' => 'buttons',
                'content_pt' => 'Salvar',
                'content_en' => 'Save',
                'content_es' => 'Guardar',
                'description' => 'Botão salvar'
            ],
            [
                'key' => 'buttons.cancel',
                'group' => 'buttons',
                'content_pt' => 'Cancelar',
                'content_en' => 'Cancel',
                'content_es' => 'Cancelar',
                'description' => 'Botão cancelar'
            ],
            [
                'key' => 'buttons.delete',
                'group' => 'buttons',
                'content_pt' => 'Excluir',
                'content_en' => 'Delete',
                'content_es' => 'Eliminar',
                'description' => 'Botão excluir'
            ],
            [
                'key' => 'buttons.edit',
                'group' => 'buttons',
                'content_pt' => 'Editar',
                'content_en' => 'Edit',
                'content_es' => 'Editar',
                'description' => 'Botão editar'
            ],

            // Mensagens
            [
                'key' => 'messages.success',
                'group' => 'messages',
                'content_pt' => 'Operação realizada com sucesso!',
                'content_en' => 'Operation completed successfully!',
                'content_es' => '¡Operación realizada con éxito!',
                'description' => 'Mensagem de sucesso'
            ],
            [
                'key' => 'messages.error',
                'group' => 'messages',
                'content_pt' => 'Ocorreu um erro. Tente novamente.',
                'content_en' => 'An error occurred. Please try again.',
                'content_es' => 'Ocurrió un error. Inténtalo de nuevo.',
                'description' => 'Mensagem de erro'
            ],

            // Formulários
            [
                'key' => 'forms.email',
                'group' => 'forms',
                'content_pt' => 'E-mail',
                'content_en' => 'Email',
                'content_es' => 'Correo electrónico',
                'description' => 'Campo email'
            ],
            [
                'key' => 'forms.password',
                'group' => 'forms',
                'content_pt' => 'Senha',
                'content_en' => 'Password',
                'content_es' => 'Contraseña',
                'description' => 'Campo senha'
            ],
            [
                'key' => 'forms.confirm_password',
                'group' => 'forms',
                'content_pt' => 'Confirmar Senha',
                'content_en' => 'Confirm Password',
                'content_es' => 'Confirmar Contraseña',
                'description' => 'Campo confirmar senha'
            ],

            // Painel Admin
            [
                'key' => 'admin.dashboard',
                'group' => 'admin',
                'content_pt' => 'Painel de Controle',
                'content_en' => 'Dashboard',
                'content_es' => 'Panel de Control',
                'description' => 'Título do dashboard'
            ],
            [
                'key' => 'admin.users',
                'group' => 'admin',
                'content_pt' => 'Gerenciar Usuários',
                'content_en' => 'Manage Users',
                'content_es' => 'Gestionar Usuarios',
                'description' => 'Menu usuários'
            ],
            [
                'key' => 'admin.settings',
                'group' => 'admin',
                'content_pt' => 'Configurações',
                'content_en' => 'Settings',
                'content_es' => 'Configuraciones',
                'description' => 'Menu configurações'
            ],

            // Status
            [
                'key' => 'status.active',
                'group' => 'status',
                'content_pt' => 'Ativo',
                'content_en' => 'Active',
                'content_es' => 'Activo',
                'description' => 'Status ativo'
            ],
            [
                'key' => 'status.inactive',
                'group' => 'status',
                'content_pt' => 'Inativo',
                'content_en' => 'Inactive',
                'content_es' => 'Inactivo',
                'description' => 'Status inativo'
            ],
            [
                'key' => 'status.pending',
                'group' => 'status',
                'content_pt' => 'Pendente',
                'content_en' => 'Pending',
                'content_es' => 'Pendiente',
                'description' => 'Status pendente'
            ],

            // Tabelas
            [
                'key' => 'tables.actions',
                'group' => 'tables',
                'content_pt' => 'Ações',
                'content_en' => 'Actions',
                'content_es' => 'Acciones',
                'description' => 'Coluna ações'
            ],
            [
                'key' => 'tables.name',
                'group' => 'tables',
                'content_pt' => 'Nome',
                'content_en' => 'Name',
                'content_es' => 'Nombre',
                'description' => 'Coluna nome'
            ],
            [
                'key' => 'tables.date',
                'group' => 'tables',
                'content_pt' => 'Data',
                'content_en' => 'Date',
                'content_es' => 'Fecha',
                'description' => 'Coluna data'
            ],

            // Confirmações
            [
                'key' => 'confirm.delete',
                'group' => 'confirm',
                'content_pt' => 'Tem certeza que deseja excluir?',
                'content_en' => 'Are you sure you want to delete?',
                'content_es' => '¿Estás seguro de que quieres eliminar?',
                'description' => 'Confirmação de exclusão'
            ],
            [
                'key' => 'confirm.cancel',
                'group' => 'confirm',
                'content_pt' => 'Tem certeza que deseja cancelar?',
                'content_en' => 'Are you sure you want to cancel?',
                'content_es' => '¿Estás seguro de que quieres cancelar?',
                'description' => 'Confirmação de cancelamento'
            ],

            // Relatórios
            [
                'key' => 'reports.title',
                'group' => 'reports',
                'content_pt' => 'Relatórios',
                'content_en' => 'Reports',
                'content_es' => 'Informes',
                'description' => 'Título relatórios'
            ],
            [
                'key' => 'reports.generate',
                'group' => 'reports',
                'content_pt' => 'Gerar Relatório',
                'content_en' => 'Generate Report',
                'content_es' => 'Generar Informe',
                'description' => 'Botão gerar relatório'
            ],

            // Notificações
            [
                'key' => 'notifications.new_message',
                'group' => 'notifications',
                'content_pt' => 'Nova Mensagem',
                'content_en' => 'New Message',
                'content_es' => 'Nuevo Mensaje',
                'description' => 'Notificação nova mensagem'
            ],
            [
                'key' => 'notifications.welcome',
                'group' => 'notifications',
                'content_pt' => 'Bem-vindo ao sistema!',
                'content_en' => 'Welcome to the system!',
                'content_es' => '¡Bienvenido al sistema!',
                'description' => 'Mensagem de boas-vindas'
            ]
        ];

        DB::table('system_texts')->insert($texts);
    }
};
