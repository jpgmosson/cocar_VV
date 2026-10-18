*** Settings ***
Documentation     Autenticação, usuários e gestão organizacional (CT09, CT10, CT12, CT14, CT15).
...               Pré-requisito: aplicação rodando com o seed (admin@org.com.br, passageiro@org.com.br).
Resource          ../resources/comum.resource
Test Setup        Abrir o navegador
Test Teardown     Fechar o navegador


*** Variables ***
${SENHA_NOVA}               SenhaForte123!
${MSG_DOMINIO}              O domínio do seu e-mail não está cadastrado com nenhuma organização parceira.
${TRECHO_CPF_INVALIDO}      não é um CPF válido
${TRECHO_CREDENCIAL}        credenciais indicadas não coincidem


*** Test Cases ***
CT09 - Bloqueio de registro com domínio de e-mail não cadastrado
    [Documentation]    Registro com e-mail de um domínio que nenhuma organização possui.
    ...                Esperado: permanece em /register e o erro sob o e-mail informa que o domínio não está cadastrado.
    [Tags]    CT09    autenticacao
    Ir para    /register
    ${dominio}=    Gerar Dominio Unico
    ${email}=    Gerar Email Unico    ${dominio}
    ${cpf}=    Gerar Cpf Valido
    Preencher formulário de registro    Usuario Robot    ${email}    ${cpf}    ${SENHA_NOVA}
    Verificar URL termina com    /register
    Verificar mensagem de erro de campo    ${MSG_DOMINIO}

CT10 - Criação completa de usuário com associação à organização e carteira
    [Documentation]    Registro com e-mail do domínio da organização seed.
    ...                Esperado: usuário logado em /home, /carteira abre normalmente e, depois,
    ...                o admin da organização encontra o novo e-mail na listagem de usuários (vínculo com a organização).
    [Tags]    CT10    autenticacao
    ${email}=    Auth Registrar usuário descartável
    Verificar URL termina com    /home
    Ir para    /carteira
    Verificar URL termina com    /carteira
    Capture Page Screenshot    ct10-carteira.png
    Auth Página não deve ser de erro
    Encerrar sessão
    Auth Fazer login    ${EMAIL_ADMIN_ORG}    ${SENHA_SEED}
    Ir para    /dashboard/usuarios
    Auth Pesquisar usuário na listagem    ${email}
    Auth Tabela de usuários deve conter    ${email}

CT12 - Bloqueio e redirecionamento por papel de usuário
    [Documentation]    a) Passageiro tentando abrir /dashboard/usuarios volta para /home.
    ...                b) Admin da organização abrindo /home vai para /dashboard.
    ...                Valida somente a URL final: a mensagem "Acesso negado" é enviada em session('error'),
    ...                mas nenhuma view a exibe.
    [Tags]    CT12    autenticacao
    Auth Fazer login    ${EMAIL_PASSAGEIRO}    ${SENHA_SEED}
    Ir para    /dashboard/usuarios
    Verificar URL termina com    /home
    Capture Page Screenshot    ct12-passageiro-bloqueado.png
    Auth URL não deve conter    /dashboard
    Encerrar sessão
    Auth Fazer login    ${EMAIL_ADMIN_ORG}    ${SENHA_SEED}
    Ir para    /home
    Verificar URL termina com    /dashboard

CT14 - Exclusão de usuário e encerramento de sessão
    [Documentation]    Usuário descartável exclui a própria conta pelo perfil (sem confirmação JS).
    ...                Esperado: termina na raiz, /perfil redireciona para /login e o login com as mesmas
    ...                credenciais falha com a mensagem de credenciais inválidas.
    [Tags]    CT14    autenticacao
    ${email}=    Auth Registrar usuário descartável
    Verificar URL termina com    /home
    Ir para    /perfil
    Auth Excluir a própria conta
    Auth URL deve ser a raiz
    Capture Page Screenshot    ct14-conta-excluida.png
    Ir para    /perfil
    Verificar URL termina com    /login
    Capture Page Screenshot    ct14-perfil-redireciona-login.png
    Auth Tentar login com credenciais    ${email}    ${SENHA_NOVA}
    Verificar URL termina com    /login
    Verificar mensagem de erro de campo    ${TRECHO_CREDENCIAL}

CT15 - Validação de CPF
    [Documentation]    Registro com CPFs inválidos (dígito verificador errado, dígitos repetidos e 10 dígitos).
    ...                Esperado: permanece em /register com erro sob o CPF. Em seguida, um CPF válido leva a /home.
    [Tags]    CT15    autenticacao
    FOR    ${tipo}    IN    digito_errado    repetido    tamanho
        Ir para    /register
        ${email}=    Gerar Email Unico    ${DOMINIO_SEED}
        ${cpf}=    Gerar Cpf Invalido    ${tipo}
        Preencher formulário de registro    Usuario Robot    ${email}    ${cpf}    ${SENHA_NOVA}
        Verificar URL termina com    /register
        Verificar mensagem de erro de campo    ${TRECHO_CPF_INVALIDO}
        Capture Page Screenshot    ct15-cpf-${tipo}.png
    END
    Ir para    /register
    ${email}=    Gerar Email Unico    ${DOMINIO_SEED}
    ${cpf}=    Gerar Cpf Valido
    Preencher formulário de registro    Usuario Robot    ${email}    ${cpf}    ${SENHA_NOVA}
    Verificar URL termina com    /home


*** Keywords ***
Auth Registrar usuário descartável
    [Documentation]    Registra um usuário novo no domínio do seed e devolve o e-mail gerado.
    Ir para    /register
    ${email}=    Gerar Email Unico    ${DOMINIO_SEED}
    ${cpf}=    Gerar Cpf Valido
    Preencher formulário de registro    Usuario Robot    ${email}    ${cpf}    ${SENHA_NOVA}
    RETURN    ${email}

Auth Página não deve ser de erro
    [Documentation]    Garante que a página atual não é uma tela de erro 404/500.
    Page Should Contain Element    css:.wallet__balance
    Page Should Not Contain    Server Error
    Page Should Not Contain    Whoops

Auth Pesquisar usuário na listagem
    [Documentation]    Digita no campo de busca e aplica os filtros.
    [Arguments]    ${termo}
    Wait Until Element Is Visible    css:input[name=pesquisa]
    Input Text    css:input[name=pesquisa]    ${termo}
    Click Button    css:#user-filter button[type=submit]

Auth Tabela de usuários deve conter
    [Documentation]    Espera a tabela da listagem conter o texto.
    [Arguments]    ${texto}
    Wait Until Element Is Visible    css:.table
    Wait Until Page Contains Element    xpath://table[contains(@class,'table')]//td[normalize-space()='${texto}']
    Element Should Contain    css:.table    ${texto}

Auth Excluir a própria conta
    [Documentation]    Clica em "Excluir Permanentemente" do perfil (form /deletar-conta, sem confirmação).
    Wait Until Element Is Visible    css:form[action$="/deletar-conta"] button[type=submit]
    Click Button    css:form[action$="/deletar-conta"] button[type=submit]

Auth URL deve ser a raiz
    [Documentation]    Espera o navegador chegar na raiz do site.
    Wait Until Keyword Succeeds    ${TIMEOUT}    200ms    Location Should Be    ${BASE_URL}/

Auth Tentar login com credenciais
    [Documentation]    Envia o formulário de login sem esperar sucesso (para cenários de falha).
    [Arguments]    ${email}    ${senha}
    Ir para    /login
    Wait Until Element Is Visible    css:input[name=email]
    Input Text    css:input[name=email]    ${email}
    Input Text    css:input[name=password]    ${senha}
    Click Button    css:form[action$="/login"] button[type=submit]

Auth URL não deve conter
    [Documentation]    Confere que a URL atual não contém o trecho (o SeleniumLibrary não tem keyword pronta).
    [Arguments]    ${trecho}
    ${url}=    Get Location
    Should Not Contain    ${url}    ${trecho}

Auth Fazer login
    [Documentation]    Mesmo fluxo de "Fazer login" do resource, sem usar Location Should Not Contain (inexistente).
    [Arguments]    ${email}    ${senha}
    Auth Tentar login com credenciais    ${email}    ${senha}
    Wait Until Keyword Succeeds    ${TIMEOUT}    200ms    Auth URL não deve conter    /login
