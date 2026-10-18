# Testes de interface (Robot Framework)

## Pré-requisitos
- Python, `robotframework` e `robotframework-seleniumlibrary` instalados.
- Navegador instalado (o Selenium Manager baixa o driver sozinho).
- Aplicação rodando.

## Subir a aplicação
Na pasta `cocar-testes`:

```powershell
php artisan serve
```

URL: http://127.0.0.1:8000.
**Atenção:** o vhost `cocar.test` do Laragon aponta para OUTRO projeto (`C:/laragon/www/cocar`). Não use.

## PYTHONIOENCODING
O valor `utf-8:surrogateescape` quebra o Robot. Sempre defina antes:

```powershell
$env:PYTHONIOENCODING='utf-8'
```

## Configuração pessoal
```powershell
Copy-Item tests/Robot/local.args.example tests/Robot/local.args
```
Ajuste `BROWSER` e `BASE_URL`. O `local.args` é ignorado pelo git.

## Executar
```powershell
.\tests\Robot\rodar.ps1 -Pessoa JoaoMosson
```
Um caso só:
```powershell
.\tests\Robot\rodar.ps1 -Pessoa JoaoMosson -Caso CT09
```
O script define o `PYTHONIOENCODING` sozinho e usa o `local.args` se existir.
Os resultados (`log.html`, `report.html`, `output.xml` e prints) ficam em `tests/Robot/results/<Pessoa>/` e devem ser commitados na entrega (cada pessoa só a sua pasta).

## Convenções
- Um arquivo `.robot` por pessoa, dentro da própria pasta.
- Cada caso com `[Documentation]` e `[Tags]` com o número do CT (ex.: `CT09`).
- `Test Setup    Abrir o navegador` e `Test Teardown    Fechar o navegador`.
- Sem `Sleep` fixo: use `Wait Until ...`.
- Dados sempre gerados por `GeradorDados` (todo campo tem `unique` no banco).
- Keywords compartilhadas ficam em `resources/comum.resource`, editado por uma pessoa só.
- Campos de CPF/CNPJ são mascarados: digite no input visível (`cpf_display`, `cnpj_display`, `administrador-cpf_display`).

## Dados do seed
Senha de todos: `SenhaForte!`

| Usuário | E-mail |
|---|---|
| Admin do sistema | admin@cocar.com.br |
| Admin da organização | admin@org.com.br |
| Motorista | motorista@org.com.br |
| Passageiro | passageiro@org.com.br |

Domínio da organização seed: `org.com.br`.

**Os testes criam e excluem registros.** O ideal é rodar contra um banco separado (`DB_DATABASE` diferente do de desenvolvimento, com PostGIS).

## Responsáveis

| Pasta | Responsável |
|---|---|
| Eduardo | |
| JoaoMosson | |
| JoaoPozzan | |
| Renan | |
| Victor | |
