# 🏥 Sistema de Gestão de Saúde - Distrito Sanitário

Sistema web desenvolvido em **PHP** para gerenciamento de dados da saúde pública, incluindo atendimentos, visitas, equipes e relatórios, com organização por distrito sanitário.

---

## 🚀 Funcionalidades

- 📊 Dashboard com dados consolidados
- 👥 Gestão de usuários
- 🏥 Controle por equipe e unidade
- 📅 Registro de atendimentos
- 🏠 Registro de visitas domiciliares
- 💬 Chat interno entre usuários
- 📄 Geração de relatórios
- 🔐 Sistema de autenticação (login/logout)
- 📈 Integração com dados externos (Google Sheets / JSON)
- ⚡ Cache de dados para alta performance

---

## 🛠️ Tecnologias utilizadas

- PHP (backend)
- MySQL (banco de dados)
- JavaScript
- HTML5 / CSS3
- AdminLTE (interface)
- JSON (cache e integração)

---

### 📂 Estrutura do projeto
- /CORE → Configurações, conexão e cache
- /security → Autenticação e proteção de sessão
- /views → Telas e dashboards
- /cache → Cache de dados JSON
- /dist → Interface AdminLTE (frontend)
- /partials → Componentes reutilizáveis


---

## ⚙️ Como executar o projeto

### 🔧 Pré-requisitos

- PHP 7.4+
- MySQL
- Servidor local (XAMPP, WAMP ou Laragon)

---

### ▶️ Instalação

```bash
git clone https://github.com/seu-usuario/seu-repositorio.git
cd seu-repositorio
```

---


### 🗄️ Banco de dados

Devido a limitações de configuração no banco e-SUS, foi utilizado um banco relacional auxiliar para:

- Definição de metas
- Relacionamento de unidades por distrito
- Ajustes e validações dos dados importados

## Passos:
- Crie um banco no MySQL
- Importe o arquivo:

```bash
/CORE/banco/secsauderecife.sql
```

---

### ⚙️ Configuração

Edite o arquivo:

```bash
CORE/.env
```

Configure as variáveis:

- DB_HOST
- DB_USER
- DB_PASS
- DB_NAME

---

### ▶️ Execução

Coloque o projeto no servidor local:

- XAMPP → htdocs
- WAMP → www

Acesse:

http://localhost/seu-projeto

---

### 🔐 Autenticação

O sistema possui:

- Login → /security/login.php
- Proteção de sessão → session_guard.php
- Logout

---

### 💬 Módulo de Chat - Em produção

Permite comunicação interna entre usuários em tempo real.

---

### 📊 Módulos principais
- atendimentos.php
- visitas.php
- interdicoes.php
- falecomsuaequipe.php

---

### ⚡ Cache

O sistema utiliza cache em JSON para melhorar desempenho:

/cache
/CORE/cache
📸 Screenshots

Adicione imagens do sistema:

![Dashboard](./caminho-da-imagem.png)

---

### 📌 Melhorias futuras
 API REST
 Melhorias de UI/UX
 Exportação avançada (PDF/Excel)
 Controle de permissões por perfil
 Logs de auditoria

---

### 🤝 Contribuição
Fork o projeto
Crie uma branch (feature/minha-feature)
Commit suas alterações
Push
Abra um Pull Request

---

### 🔒 Segurança
Proteção de sessão implementada
Controle de acesso por usuário
Uso de .env para dados sensíveis

---

### 📄 Licença

Este projeto está sob a licença MIT.


---

### 👨‍💻 Autor

Desenvolvido por Danillo Almeida Marques
