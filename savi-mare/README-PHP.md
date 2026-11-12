# Savi Mare Sociale — primeira versão do login

Esta etapa implementa autenticação de clientes e administradores. O HTML/CSS original foi mantido; as alterações nas páginas existentes limitam-se aos destinos dos links e ao envio dos formulários para o PHP.

## Requisitos

- PHP 8.0+;
- MySQL ou MariaDB;
- extensões PHP `pdo_mysql` e `pdo`;
- Apache (XAMPP) ou servidor PHP equivalente.

## Configurar o banco

1. Inicie Apache e MySQL no XAMPP.
2. Abra `http://localhost/phpmyadmin`.
3. Importe `database/schema.sql`.

O script cria o banco `savi_mare_sociale` e a tabela `users`.

## Configurar conexão

Os valores padrão são adequados para XAMPP local:

- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_NAME=savi_mare_sociale`
- `DB_USER=root`
- `DB_PASS=` (vazio)

Se necessário, edite `config/database.php` ou configure as variáveis de ambiente.

## Executar

Coloque a pasta do projeto em `C:\xampp\htdocs\savi-mare` e acesse:

- Loja: `http://localhost/savi-mare/index.html`
- Login de cliente: `http://localhost/savi-mare/login.php`
- Cadastro: `http://localhost/savi-mare/criar-conta.php`
- Login administrativo: `http://localhost/savi-mare/admin-login.php`

## Credenciais administrativas iniciais

- Usuário: `admin`
- Senha: `admin`

O administrador é criado automaticamente quando a primeira página de autenticação é acessada, desde que o banco e a tabela já tenham sido importados. A senha é salva com `password_hash()`, nunca em texto puro.

**Atenção:** `admin/admin` é uma credencial fraca solicitada para esta versão inicial. Troque-a antes de publicar o sistema na internet. Não reutilize essa senha em outros serviços.

## O que funciona nesta etapa

- cadastro de cliente com nome, e-mail, telefone e senha;
- validação de e-mail, campos obrigatórios e confirmação de senha;
- bloqueio de e-mail duplicado;
- login de cliente por e-mail;
- login administrativo por `admin` ou e-mail;
- sessões PHP com regeneração do ID após login;
- proteção das páginas `admin-produtos.php` e `admin-categorias.php`;
- logout via POST;
- consultas PDO preparadas, hash de senha e token CSRF;
- navegação entre login, cadastro, loja e páginas administrativas.

## Limite desta etapa

Os formulários e dados demonstrativos de produtos e categorias foram preservados. O CRUD de produtos/categorias (gravar, editar, excluir e carregar os registros do banco) não faz parte desta implementação de autenticação.
