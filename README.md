# 🏥 Sistema de Gestão de Saúde - Distrito Sanitário

Sistema web desenvolvido em PHP para gerenciamento de dados de saúde, incluindo atendimentos, visitas, equipes e relatórios, com foco em organização por distrito sanitário.

---

## 🚀 Funcionalidades

- 📊 Dashboard com dados consolidados
- 👥 Gestão de usuários
- 🏥 Controle por equipe e unidade
- 📅 Registro de atendimentos
- 🏠 Registro de visitas
- 💬 Chat interno entre usuários
- 📄 Geração de relatórios (incluindo folha)
- 🔐 Sistema de autenticação (login/logout)
- 📈 Integração com dados externos (Google Sheets / JSON)
- ⚡ Cache de dados para performance

---

## 🛠️ Tecnologias utilizadas

- PHP (backend principal)
- MySQL (banco de dados)
- JavaScript
- HTML5 / CSS3
- AdminLTE (interface)
- JSON (cache e integração de dados)

---

## 📂 Estrutura do projeto
/CORE → Configurações, conexão e cache
/security → Autenticação e proteção de sessão
/views → Telas e dashboards
/cache → Cache de dados JSON
/dist → Interface AdminLTE (frontend)
/folha → Geração de relatórios
/partials → Componentes reutilizáveis


## ⚙️ Como executar o projeto

### 🔧 Pré-requisitos

- PHP 7.4+
- MySQL
- Servidor local (XAMPP, WAMP, Laragon)

---

### ▶️ Instalação

```bash
# Clone o repositório
git clone https://github.com/seu-usuario/seu-repositorio.git

# Acesse a pasta
cd seu-repositorio

🗄️ Banco de dados
Como não conseguimos fazer os ajustes pelo banco de dados esus, utilizei um banco de dados relacional para colocar as configurações de metas, quais unidades pertencem a determinado distrito e com isso ao pegar as informações do banco esus ele valida e vai ajustando o sistema ao que precisa.

Crie um banco no MySQL
Importe o arquivo:
/CORE/banco/secsauderecife.sql

⚙️ Configuração

Edite o arquivo:

CORE/.ENV

Configure:

Host do banco
Usuário
Senha
Nome do banco

▶️ Executar

Coloque o projeto na pasta do servidor:

XAMPP → htdocs
WAMP → www

Acesse:

http://localhost/seu-projeto

🔐 Autenticação

O sistema possui:

Login (/security/login.php)
Controle de sessão (session_guard.php)
Logout

💬 Módulo de Chat

Arquivos principais:

chat_send.php
chat_get.php

Permite comunicação interna entre usuários.

📊 Módulos principais
atendimentos.php
visitas.php
equipe.php
interdicoes.php
users.php

⚡ Cache

O sistema utiliza cache em JSON para melhorar performance:

/cache
/CORE/cache

📸 Screenshots

Adicione prints do sistema aqui:

![Dashboard](./caminho-da-imagem.png)

📌 Melhorias futuras
 API REST
 Melhorar UI/UX
 Exportação avançada (PDF/Excel)
 Controle de permissões por perfil
 Logs de auditoria

 🤝 Contribuição
Fork o projeto
Crie uma branch (feature/minha-feature)
Commit suas alterações
Push
Abra um Pull Request

🔒 Segurança
Proteção de sessão implementada
Controle de acesso por usuário
Arquivo .env para variáveis sensíveis
📄 Licença

Este projeto está sob a licença MIT.

👨‍💻 Autor

Desenvolvido por Danillo Almeida Marques
