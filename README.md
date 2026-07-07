# Fechou

SaaS MVC em PHP 8+, MySQL, HTML, CSS e JavaScript puros.

## Etapa 1

- Base MVC e autoload próprio
- Login, cadastro e recuperação inicial
- Tema claro/escuro
- Dashboard inicial
- Script SQL base para multiempresa

## Etapa 2

- Cadastro, edição, exclusão e busca de clientes
- Tabelas normalizadas para clientes, serviços e produtos
- Menu lateral apontando para o novo módulo operacional

## Estrutura

- `app/Controllers`
- `app/Core`
- `app/Helpers`
- `app/Models`
- `app/Views`
- `config`
- `database`
- `public`
- `routes`
- `storage`

## Próximo passo

Expandir a etapa 2 com clientes, serviços e produtos, mantendo isolamento por empresa.

## Acesso padrão

Para criar um usuário inicial de acesso, execute:

`php scripts/create_default_user.php`

Credenciais padrão:

- E-mail: `admin@fechou.com`
- Senha: `Fechou@123`