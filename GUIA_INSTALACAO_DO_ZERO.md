# 📘 VendaVision • Guia Completo de Instalação do Zero
> **Projeto Integrador em Computação IV — UNIVESP (Grupo DRP08-S1-G11)**  
> **Público-alvo:** Membros da equipe, avaliadores e testadores.  
> **Objetivo:** Instalar e executar o projeto localmente em qualquer computador (Windows, Linux ou macOS), cobrindo desde a instalação dos programas básicos até o primeiro login no painel.

---

## 📑 Índice de Tópicos
1. [Tópico 1: Resumo do Projeto e Arquitetura](#tópico-1-resumo-do-projeto-e-arquitetura)
2. [Tópico 2: Ferramentas Necessárias (Softwares e Links Oficiais)](#tópico-2-ferramentas-necessárias-softwares-e-links-oficiais)
3. [Tópico 3: Instalação dos Programas por Ordem de Necessidade](#tópico-3-instalação-dos-programas-por-ordem-de-necessidade)
4. [Tópico 4: Obtendo o Código do Projeto (Via Git ou Download Direto)](#tópico-4-obtendo-o-código-do-projeto-via-git-ou-download-direto)
5. [Tópico 5: Configuração das Variáveis de Ambiente (.env)](#tópico-5-configuração-das-variáveis-de-ambiente-env)
6. [Tópico 6: Baixando as Dependências da Aplicação via Docker](#tópico-6-baixando-as-dependências-da-aplicação-via-docker)
7. [Tópico 7: Subindo os Contêineres da Aplicação e Banco de Dados](#tópico-7-subindo-os-contêineres-da-aplicação-e-banco-de-dados)
8. [Tópico 8: Inicialização do Banco de Dados Limpo e Chave de Segurança](#tópico-8-inicialização-do-banco-de-dados-limpo-e-chave-de-segurança)
9. [Tópico 9: Acesso ao Sistema e Credenciais Padrão](#tópico-9-acesso-ao-sistema-e-credenciais-padrão)
10. [Tópico 10: Comandos Úteis do Dia a Dia (Ligar, Parar, Logs)](#tópico-10-comandos-úteis-do-dia-a-dia-ligar-parar-logs)
11. [Tópico 11: Resolução de Problemas Frequentes (FAQ)](#tópico-11-resolução-de-problemas-frequentes-faq)
12. [Tópico 12: Finalização](#tópico-12-finalização)

---

## Tópico 1: Resumo do Projeto e Arquitetura

O **VendaVision** é um sistema web corporativo para gestão comercial, precificação inteligente, análise de margens de lucro e controle de pedidos e produtos.

### Vantagem da Instalação Conteinerizada (Docker):
Você **NÃO** precisa instalar PHP, Composer, Node.js nem MySQL na sua máquina física. O Docker cria ambientes virtuais isolados e padronizados com todas as versões exatas necessárias, garantindo que o sistema funcione com o mesmo comportamento em qualquer computador.

---

## Tópico 2: Ferramentas Necessárias (Softwares e Links Oficiais)

Você precisará de apenas **duas ferramentas** gratuitas instaladas no seu computador:

| Ferramenta | Para que serve? | Link Oficial de Download |
| :--- | :--- | :--- |
| **Docker Desktop** | Executa os servidores do sistema (PHP 8.4, Nginx/Web e MySQL 8.4) sem poluir seu computador. | [docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop/) |
| **Git** | Baixa e sincroniza as atualizações do código do projeto. | [git-scm.com/downloads](https://git-scm.com/downloads) |

*(Opcional: um editor como o [Visual Studio Code](https://code.visualstudio.com/) caso queira visualizar ou editar arquivos).*

---

## Tópico 3: Instalação dos Programas por Ordem de Necessidade

Siga a ordem abaixo para evitar erros de dependência do sistema operacional:

### 3.1. Instalando o Docker Desktop (Windows / Mac / Linux)

#### No Windows:
1. Baixe o instalador no link oficial acima.
2. Durante a instalação, certifique-se de marcar a opção **"Use WSL 2 instead of Hyper-V (recommended)"**.
3. Conclua o instalador e, se solicitado, reinicie o computador.
4. Abra o **Docker Desktop** pelo menu Iniciar.
5. Aceite os termos de serviço (*Docker Subscription Service Agreement*).
6. Aguarde alguns instantes até o ícone no canto inferior esquerdo do Docker Desktop ficar verde (**"Engine running"**).

> ⚠️ **Importante no Windows:** Se o Docker alertar que o WSL 2 precisa ser atualizado, abra o PowerShell como Administrador e execute:  
> `wsl --update` e reinicie o Docker Desktop.

#### No Linux (Ubuntu/Debian):
Instale o Docker Engine e Docker Compose Plugin via terminal:
```bash
sudo apt update
sudo apt install -y docker.io docker-compose-v2
sudo usermod -aG docker $USER
```
*(Faça logoff e login novamente para aplicar as permissões de grupo).*

---

### 3.2. Instalando o Git

#### No Windows:
1. Baixe o instalador no site oficial do Git e execute-o.
2. Pode avançar clicando em **Next** em todas as etapas mantendo as opções padrão recomendadas.
3. Ao finalizar, você terá o terminal **Git Bash** e o comando `git` disponível no PowerShell.

#### No Linux:
```bash
sudo apt install -y git
```

---

## Tópico 4: Obtendo o Código do Projeto (Via Git ou Download Direto)

Escolha **uma** das opções abaixo:

### Opção A: Via Git (Recomendada para membros do grupo)
Abra o seu terminal preferido (**PowerShell** no Windows ou **Terminal** no Linux/Mac) e execute:

```bash
git clone https://github.com/drp08s1g11/vendavision.git
cd vendavision
```

### Opção B: Via Download do Arquivo ZIP (Sem Git)
1. Acesse o repositório no navegador: [github.com/drp08s1g11/vendavision](https://github.com/drp08s1g11/vendavision)
2. Clique no botão verde **`<> Code`** e selecione **Download ZIP**.
3. Extraia a pasta do arquivo ZIP em um local de sua preferência (ex: `C:\Projetos\vendavision` ou na sua Área de Trabalho).
4. Abra o terminal **dentro** da pasta descompactada.

---

## Tópico 5: Configuração das Variáveis de Ambiente (.env)

O Laravel utiliza um arquivo chamado `.env` para saber quais portas e conexões de banco de dados utilizar. Já deixamos um modelo 100% pronto e compatível com o Docker.

No terminal, dentro da pasta do projeto, execute:

### No Windows (PowerShell):
```powershell
Copy-Item .env.example .env
```

### No Linux ou macOS (Bash):
```bash
cp .env.example .env
```

*(Esse comando cria o seu arquivo `.env` local com as configurações do banco de dados MySQL conteinerizado).*

---

## Tópico 6: Baixando as Dependências da Aplicação via Docker

Como o repositório é limpo (não inclui os milhares de arquivos de bibliotecas de terceiros), usamos uma imagem oficial e descartável do Docker para baixar tudo de forma rápida e segura, sem precisar de PHP na máquina:

### No Windows (PowerShell):
```powershell
docker run --rm -v ${PWD}:/var/www/html -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
```

### No Linux ou macOS (Terminal Bash):
```bash
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
```

> ⏳ **O que esse comando faz?** Ele cria um contêiner temporário com PHP 8.4 e Composer, baixa todas as bibliotecas do projeto para a pasta `vendor/` e se autodestrói em seguida. Leva cerca de 1 a 2 minutos dependendo da sua internet.

---

## Tópico 7: Subindo os Contêineres da Aplicação e Banco de Dados

Agora que as dependências estão prontas, vamos subir os serviços definitivos da aplicação (Servidor Web + MySQL):

```bash
docker compose up -d
```

> 💡 **O que acontece agora?**  
> - Na primeira vez, o Docker irá construir a imagem da aplicação baseada no arquivo `docker/8.4/Dockerfile` e baixar a imagem oficial do MySQL 8.4.  
> - O parâmetro `-d` (*detached*) roda os servidores em segundo plano, liberando o seu terminal para novos comandos.

Para conferir se os contêineres estão rodando com sucesso, execute:
```bash
docker compose ps
```
Você deverá ver os contêineres com status `Up` ou `running`.

---

## Tópico 8: Inicialização do Banco de Dados Limpo e Chave de Segurança

Com os contêineres ativos, execute os dois comandos a seguir para preparar a segurança e a base de dados:

### 8.1. Gerar a chave criptográfica da aplicação
```bash
docker compose exec laravel.test php artisan key:generate
```
*(Gera a chave de criptografia de sessões no arquivo `.env`)*.

---

### 8.2. Executar as migrações e criar o usuário Administrador
```bash
docker compose exec laravel.test php artisan migrate --seed
```

> ✨ **Base 100% Limpa para Novos Usuários:**  
> Esse comando cria todas as tabelas vazias no banco MySQL e cadastra exclusivamente o usuário Administrador inicial. Nenhum produto ou venda fictícia poluirá o sistema, permitindo cadastros reais a partir do zero.

---

## Tópico 9: Acesso ao Sistema e Credenciais Padrão

Pronto! A aplicação já está respondendo no seu navegador.

Abra seu navegador (Google Chrome, Firefox, Edge, etc.) e acerte o endereço:

🌐 **URL de Acesso ao Painel Administrativo:**  
👉 **[http://localhost/admin](http://localhost/admin)**

### Credenciais de Login Padrão:
- **E-mail:** `admin@ambrozio.com.br`
- **Senha:** `password`

*(Ao entrar, você verá a interface com tema de alto contraste, menus de Produtos, Vendas, Clientes, Categorias, Simulador de Preço, Curva ABC e a página de Dicas e Instruções para Importação de Planilhas).*

---

## Tópico 10: Comandos Úteis do Dia a Dia (Ligar, Parar, Logs)

Depois que o projeto já foi instalado pela primeira vez, você não precisa repetir os passos anteriores. No seu dia a dia de testes, use apenas:

- **Para desligar o sistema (quando for parar de trabalhar):**
  ```bash
  docker compose stop
  ```

- **Para ligar o sistema novamente:**
  ```bash
  docker compose start
  ```

- **Para ver os logs do sistema em tempo real (útil para diagnosticar erros):**
  ```bash
  docker compose logs -f laravel.test
  ```

- **(Opcional) Popular com dados de teste demonstrativos:**  
  Se em algum momento o grupo desejar preencher o banco com produtos e vendas de exemplo para gravação de vídeo ou apresentação do Projeto Integrador:
  ```bash
  docker compose exec laravel.test php artisan db:seed --class=AmbrozioDecoraSeeder
  ```

---

## Tópico 11: Resolução de Problemas Frequentes (FAQ)

### P1: O comando `docker compose up` deu erro de "port 80 is already in use" ou "port 3306 is already in use".
- **Causa:** Você possui um servidor local (como XAMPP, WampServer, IIS, MySQL local ou Skype) rodando na porta 80 ou 3306.
- **Solução:**
  1. Feche o XAMPP ou pare o serviço MySQL local no gerenciador de serviços do Windows.
  2. Ou altere as portas no seu arquivo `.env`:
     - Mude `APP_PORT=80` para `APP_PORT=8080`
     - Mude `FORWARD_DB_PORT=3306` para `FORWARD_DB_PORT=3307`
  3. Em seguida acesse `http://localhost:8080/admin`.

### P2: O terminal diz "docker: command not found".
- **Causa:** O Docker Desktop não está instalado ou o terminal foi aberto antes da instalação terminar.
- **Solução:** Abra o Docker Desktop, aguarde inicializar e reinicie o seu terminal.

### P3: Erro de permissão no Linux ao rodar comandos docker.
- **Solução:** Rode os comandos com `sudo` ou adicione seu usuário ao grupo docker:
  `sudo usermod -aG docker $USER` e relogue na sessão.

---

## Tópico 12: Finalização

Parabéns! Se você chegou até aqui, seu ambiente de desenvolvimento e testes está 100% operacional, reproduzindo com fidelidade a infraestrutura do projeto integrador.

Dúvidas ou sugestões? Entre em contato com os membros da equipe através do repositório oficial no GitHub:  
🔗 **[https://github.com/drp08s1g11/vendavision](https://github.com/drp08s1g11/vendavision)**
