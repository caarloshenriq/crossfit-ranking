# Crossfit Ranking API

API REST em PHP puro para consulta de ranking de recordes pessoais por movimento.

## Tecnologias

- PHP 8.4
- MySQL 8.0
- Nginx
- Docker + Docker Compose

## Requisitos

- Docker
- Docker Compose
- Make

## Instalação e execução

### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd crossfit-ranking
```

### 2. Configure as variáveis de ambiente

```bash
cp src/.env.example src/.env
```

### 3. Suba o ambiente

```bash
make up
```

### 4. Instale as dependências

```bash
make install
```

A API estará disponível em `http://localhost:8080`.

---

## Endpoints

### GET /rankings/{identifier}

Retorna o ranking de recordes pessoais de um movimento.

O parâmetro `identifier` pode ser o **id** ou o **nome** do movimento.

**Exemplos:**

```bash
curl http://localhost:8080/rankings/1
curl http://localhost:8080/rankings/Deadlift
curl http://localhost:8080/rankings/Back%20Squat
```

**Resposta de sucesso (200):**

```json
{
    "movement": "Deadlift",
    "ranking": [
        {
            "position": 1,
            "user": "Jose",
            "personal_record": 190,
            "date": "2021-01-06 00:00:00"
        },
        {
            "position": 2,
            "user": "Joao",
            "personal_record": 180,
            "date": "2021-01-02 00:00:00"
        },
        {
            "position": 3,
            "user": "Paulo",
            "personal_record": 170,
            "date": "2021-01-01 00:00:00"
        }
    ]
}
```

**Movimento não encontrado (404):**

```json
{
    "error": "Movement '999' not found."
}
```

---

## Regras de negócio

- O ranking é ordenado de forma **decrescente** pelo recorde pessoal.
- Usuários com o **mesmo recorde pessoal** compartilham a mesma posição (empate), seguindo o padrão `RANK()` do SQL — a próxima posição pula conforme o número de empatados.
- A **data do recorde** corresponde à data do registro com o maior valor, não ao registro mais recente.

---

## Testes

O projeto possui dois tipos de testes:

- **Unit:** testam o `RankingService` isolado com mocks
- **Feature:** testam o fluxo completo com banco de dados dedicado (`crossfit_test`)

### Configurar banco de testes

```bash
cp src/.env.example src/.env.test
# edite src/.env.test e defina DB_NAME=crossfit_test e APP_ENV=test
```

### Rodar os testes

```bash
# todos os testes
make test

# apenas unit
make test-unit

# apenas feature
make test-feature
```

---

## Estrutura do projeto

```
crossfit-ranking/
├── docker/
│   ├── nginx/
│   │   └── default.conf        # configuração do Nginx
│   └── php/
│       └── Dockerfile          # PHP 8.4-fpm + Composer
├── sql/
│   ├── init.sql                # schema + seed de produção
│   └── init_test.sql           # schema + seed de testes
├── src/
│   ├── config/
│   │   └── Database.php        # conexão PDO singleton
│   ├── controllers/
│   │   └── RankingController.php
│   ├── exceptions/
│   │   └── MovementNotFoundException.php
│   ├── repositories/
│   │   └── MovementRepository.php  # queries SQL
│   ├── services/
│   │   └── RankingService.php      # regras de negócio
│   ├── tests/
│   │   ├── Unit/
│   │   │   └── RankingServiceTest.php
│   │   └── Feature/
│   │       └── RankingEndpointTest.php
│   ├── .env.example
│   ├── composer.json
│   ├── index.php               # roteamento
│   └── phpunit.xml
├── docker-compose.yml
├── Makefile
└── README.md
```

---

## Decisões técnicas

### PHP puro sem framework
Conforme requisito do desafio. O roteamento é feito manualmente via `preg_match` no `index.php`, mantendo o código simples e direto.

### Lógica no banco de dados
O ranking é calculado inteiramente no MySQL usando `RANK() OVER`, `GROUP_CONCAT` e `CAST`, evitando processamento desnecessário na camada de aplicação.

### Prepared statements
Todas as queries usam prepared statements do PDO com `EMULATE_PREPARES => false`, garantindo segurança contra SQL injection.

### Busca por id ou nome
A busca distingue id de nome via `ctype_digit()`, evitando ambiguidade no bind de parâmetros e comportamentos inesperados do MySQL ao fazer cast de strings para inteiro.

### Banco de testes isolado
Os testes de feature rodam contra um banco `crossfit_test` dedicado, com dataset próprio que cobre explicitamente todos os cenários — empate na primeira posição, empate no meio do ranking, data do PR diferente da data mais recente, e movimento sem registros.
