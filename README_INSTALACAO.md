# 🚀 VendaVision - Guia de Instalação Local com Docker

Este guia orienta como instalar e executar o **VendaVision** em qualquer computador (Windows, Linux ou macOS) utilizando exclusivamente o **Docker**, sem a necessidade de instalar PHP, Composer ou MySQL na máquina hospedeira.

---

## 📋 Pré-requisitos

- **Docker Desktop** instalado e em execução:
  - [Download Docker Desktop](https://www.docker.com/products/docker-desktop/)
  - No Windows, certifique-se de que a opção WSL 2 está ativada nas configurações do Docker Desktop.

---

## 🛠️ Passo a Passo de Instalação

Abra o seu terminal (PowerShell no Windows, ou Bash no Linux/macOS) dentro da pasta raiz do projeto e siga os passos abaixo:

### 1. Criar o arquivo de ambiente (.env)

Copie o modelo de variáveis pré-configurado:

**No Windows (PowerShell):**
```powershell
Copy-Item .env.example .env
```

**No Linux ou macOS (Bash):**
```bash
cp .env.example .env
```

---

### 2. Baixar as dependências do projeto (Caso a pasta `vendor` não esteja presente)

Se você clonou via Git ou baixou um pacote sem a pasta `vendor`, utilize a imagem oficial do Composer via Docker:

**No Windows (PowerShell):**
```powershell
docker run --rm -v ${PWD}:/var/www/html -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
```

**No Linux ou macOS (Bash):**
```bash
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
```

*(Se a pasta `vendor/` já estiver inclusa no seu pacote, você pode pular esta etapa).*

---

### 3. Iniciar os containers Docker

Inicie a aplicação e o banco de dados MySQL em segundo plano:

```bash
docker compose up -d
```

> **Nota:** Na primeira execução, o Docker irá construir a imagem da aplicação e baixar o MySQL. Isso pode levar alguns minutos.

---

### 4. Gerar a chave de aplicação (APP_KEY)

Execute no terminal:

```bash
docker compose exec laravel.test php artisan key:generate
```

---

### 5. Criar o Banco de Dados e Usuário Administrador

Execute as migrações para criar a estrutura do banco de dados e o usuário inicial:

```bash
docker compose exec laravel.test php artisan migrate --seed
```

> 💡 **Projeto 100% Limpo:** Este comando cria as tabelas zeradas (sem produtos, sem vendas de teste) e cadastra apenas o usuário Administrador para que você possa iniciar os cadastros do zero.

---

## 🔑 Acesso ao Sistema

Após concluir os passos acima, acesse pelo navegador:

- **URL:** [http://localhost/admin](http://localhost/admin)
- **E-mail:** `admin@ambrozio.com.br`
- **Senha:** `password`

*(Recomendamos alterar a senha no primeiro acesso).*

---

## 💡 Comandos Úteis

- **Parar o sistema:**
  ```bash
  docker compose stop
  ```

- **Reiniciar o sistema:**
  ```bash
  docker compose start
  ```

- **Ver logs da aplicação:**
  ```bash
  docker compose logs -f laravel.test
  ```

- **(Opcional) Carregar dados fictícios para demonstração:**
  Se em algum momento desejar popular o sistema com dados de teste para demonstração:
  ```bash
  docker compose exec laravel.test php artisan db:seed --class=AmbrozioDecoraSeeder
  ```
