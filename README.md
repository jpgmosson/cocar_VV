# CoCar - Plataforma de Caronas Corporativas

O **CoCar** é uma aplicação focada em mobilidade corporativa sustentável. A plataforma conecta motoristas e passageiros da mesma organização, gerenciando trajetos otimizados, grupos de caronas e transações financeiras (carteira virtual/ajuda de custo) de forma segura e eficiente.

##  Stack Tecnológico

* **Backend:** Laravel 11
* **Banco de Dados:** PostgreSQL com extensão **PostGIS** (dados geoespaciais)
* **Integrações:** Mapbox API (Roteamento e Otimização de Trajetos)
* **Frontend:** Blade Templates + **HTMX** (para interações dinâmicas e reativas sem overhead de SPA)
* **Infraestrutura:** Docker & Docker Compose (Banco de Dados Conteinerizado)

## Destaques da Arquitetura e Padrões de Projeto

A base de código foi desenvolvida com foco em manutenibilidade, segurança e performance, aplicando conceitos sólidos de Engenharia de Software:

* **Service Pattern:** A lógica de negócio complexa foi totalmente abstraída dos *Controllers* e delegada para serviços dedicados (`TrajetoService`, `PagamentoService`, `PedidoCaronaService`), garantindo o princípio de responsabilidade única (SRP).
* **Processamento Geoespacial (PostGIS):** Utilização avançada do banco de dados para cálculos de distância e rotas (`ST_Distance`, `ST_LineLocatePoint`, `ST_DWithin`). Isso foi encapsulado de forma limpa no Laravel utilizando **Value Objects** (`Point`) e **Custom Casts** (`GeoJSONCast`, `PointCast`).
* **Forte Tipagem e DTOs:** Uso de *Data Transfer Objects* (`MapRoute`, `RouteResult`) para trafegar dados de APIs externas (Mapbox) de forma estruturada.
* **State Management com Enums:** Gerenciamento seguro de estados da aplicação utilizando Enums fortemente tipados do PHP 8.1+ (`FaseCarona`, `StatusTransacao`, `TipoTransacao`, etc.), evitando *magic strings* e facilitando refatorações.
* **Transações e Carteira Virtual (Ledger):** Lógica robusta de transações financeiras garantindo integridade ACID via `DB::transaction`. O sistema lida automaticamente com retenção preventiva de saldo, liquidação ao fim da corrida e estornos automáticos (`PagamentoService`).
* **Validação de Domínio:** *Rules* customizadas (ex: validações de CNH, CNPJ e CPF baseadas em cálculo de dígitos verificadores) garantindo a integridade dos dados na porta de entrada da aplicação.

##  Como Executar o Projeto Localmente

O projeto utiliza o Docker para fornecer o ambiente de banco de dados (PostgreSQL + PostGIS) de maneira isolada.

### Pré-requisitos
* PHP 8.2+
* Composer
* Docker e Docker Compose

### Passo a Passo

1. **Clone o repositório:**
   ```bash
   git clone [https://github.com/seu-usuario/cocar.git](https://github.com/seu-usuario/cocar.git)
   cd cocar
2. **Instale as dependências do PHP:**
    ```bash
   composer install
3. **Configure o ambiente:
Copie o arquivo de exemplo e configure suas variáveis de ambiente (especialmente o token do Mapbox):**
    ```bash
    cp .env.example .env
    php artisan key:generate
4. **Suba o banco de dados (Docker):**
    ```bash
   docker-compose up -d
5. **Execute as Migrations e os Seeders:
Isso criará a estrutura do banco e populará o sistema com usuários de teste (Admin, Motorista e Passageiro).**  
    ```bash 
    php artisan migrate:fresh --seed
6. **Inicie o servidor local:**
    ```bash
    composer dev

A aplicação estará disponível em http://localhost:8000.

Desenvolvido com ☕ e código limpo pela equipe CoCar.
