*** Settings ***
Resource          ../resources/comum.resource

*** Variables ***
${SENHA_NOVA}               SenhaForte123!
${MSG_DOMINIO}              O domínio do seu e-mail não está cadastrado com nenhuma organização parceira.
${TRECHO_CPF_INVALIDO}      não é um CPF válido
${TRECHO_CREDENCIAL}        credenciais indicadas não coincidem
${ROTA_RESTRITA}            /dashboard/usuarios


*** Keywords ***
Montar payload de registro válido com e-mail de domínio não cadastrado
    [Documentation]    Gera nome, e-mail de domínio inexistente, CPF e senha válidos (CT09, passo 1).
    ${dominio}=    Gerar Dominio Unico
    ${email}=    Gerar Email Unico    ${dominio}
    ${cpf}=    Gerar Cpf Valido
    Auth Definir payload de registro    ${email}    ${cpf}

Enviar o formulário de registro
    [Documentation]    Abre /register, preenche com o payload montado e envia (equivale a chamar CreateNewUser::create).
    Ir para    /register
    Preencher formulário de registro    Usuario Robot    ${EMAIL_NOVO}    ${CPF_NOVO}    ${SENHA_NOVA}

Verificar que o erro de validação foi lançado no campo e-mail
    [Documentation]    Permanece em /register e o erro sob o e-mail traz a mensagem de domínio não cadastrado.
    Verificar URL termina com    /register
    Verificar mensagem de erro de campo    ${MSG_DOMINIO}

Verificar que nenhum usuário foi criado com esse e-mail
    [Documentation]    Sem acesso ao banco: confirma que o usuário não ficou logado (acessar /home redireciona para /login).
    Ir para    /home
    Verificar URL termina com    /login

Usar a organização cadastrada com o domínio do seed
    [Documentation]    A organização vem do seed (org.com.br); a interface não cria organização neste caso (CT10, passo 1).
    Set Test Variable    ${DOMINIO_NOVO}    ${DOMINIO_SEED}

Montar payload de registro válido com e-mail do domínio da organização
    [Documentation]    Gera e-mail único no domínio da organização, CPF e senha válidos (CT10, passo 2).
    ${email}=    Gerar Email Unico    ${DOMINIO_NOVO}
    ${cpf}=    Gerar Cpf Valido
    Auth Definir payload de registro    ${email}    ${cpf}

Verificar que a carteira foi criada para o usuário
    [Documentation]    Usuário logado em /home e a página /carteira abre com o saldo (carteira criada no registro).
    Verificar URL termina com    /home
    Ir para    /carteira
    Verificar URL termina com    /carteira
    Capture Page Screenshot    ct10-carteira.png
    Auth Página não deve ser de erro

Verificar que o usuário foi associado à organização correspondente
    [Documentation]    O admin da organização encontra o novo e-mail na listagem de usuários da própria organização.
    Encerrar sessão
    Auth Fazer login    ${EMAIL_ADMIN_ORG}    ${SENHA_SEED}
    Ir para    /dashboard/usuarios
    Auth Pesquisar usuário na listagem    ${EMAIL_NOVO}
    Auth Tabela de usuários deve conter    ${EMAIL_NOVO}

Verificar que o tipo do usuário é padrão
    [Documentation]    Na listagem, a linha do usuário novo mostra o selo "Passageiro" (tipo padrão, sem perfil de motorista).
    Page Should Contain Element    xpath://table[contains(@class,'table')]//tr[td[normalize-space()='${EMAIL_NOVO}']]//span[normalize-space()='Passageiro']

Usar a rota protegida por VerificarTipo para administradores
    [Documentation]    A rota restrita a administradores já existe: ${ROTA_RESTRITA} (CT12, passo 1).
    Log    Rota restrita: ${ROTA_RESTRITA}

Autenticar usuário com tipo padrão
    [Documentation]    Login como o passageiro do seed.
    Auth Fazer login    ${EMAIL_PASSAGEIRO}    ${SENHA_SEED}

Fazer GET na rota restrita
    [Documentation]    Abre a rota restrita a administradores.
    Ir para    ${ROTA_RESTRITA}

Verificar que a requisição foi interceptada antes do controller
    [Documentation]    A tela de usuários do administrador não é exibida (a URL não é mais /dashboard).
    Auth URL não deve conter    /dashboard

Verificar redirecionamento para a rota home
    [Documentation]    Termina em /home. A mensagem flash "Acesso negado" não é exibida em nenhuma view, então não é verificada.
    Verificar URL termina com    /home
    Capture Page Screenshot    ct12-passageiro-bloqueado.png

Autenticar usuário administrador da organização
    [Documentation]    Encerra a sessão e faz login como admin da organização do seed.
    Encerrar sessão
    Auth Fazer login    ${EMAIL_ADMIN_ORG}    ${SENHA_SEED}

Fazer GET na rota home do passageiro
    [Documentation]    Abre /home, rota restrita a usuários padrão.
    Ir para    /home

Verificar redirecionamento para o painel do administrador
    [Documentation]    Termina em /dashboard.
    Verificar URL termina com    /dashboard

Criar um usuário e autenticá-lo
    [Documentation]    Registra um usuário descartável novo (o registro já deixa o usuário logado em /home).
    Auth Registrar usuário descartável
    Verificar URL termina com    /home

Excluir o usuário pelo perfil
    [Documentation]    Abre /perfil e clica em "Excluir Permanentemente" (equivale a chamar DeleteUser::delete).
    Ir para    /perfil
    Auth Excluir a própria conta

Verificar que a sessão do usuário foi encerrada
    [Documentation]    Termina na raiz e abrir /perfil redireciona para /login.
    Auth URL deve ser a raiz
    Capture Page Screenshot    ct14-conta-excluida.png
    Ir para    /perfil
    Verificar URL termina com    /login
    Capture Page Screenshot    ct14-perfil-redireciona-login.png

Verificar que o usuário foi removido da base de dados
    [Documentation]    Sem acesso ao banco: o login com as mesmas credenciais falha com a mensagem de credenciais inválidas.
    Auth Tentar login com credenciais    ${EMAIL_NOVO}    ${SENHA_NOVA}
    Verificar URL termina com    /login
    Verificar mensagem de erro de campo    ${TRECHO_CREDENCIAL}

Abrir o formulário de registro que aplica a regra Cpf
    [Documentation]    Abre /register (CT15, passo 1); o domínio do seed isola o erro do CPF.
    Set Test Variable    ${DOMINIO_NOVO}    ${DOMINIO_SEED}
    Ir para    /register

Validar CPF inválido por tamanho
    [Documentation]    Envia o registro com CPF de 10 dígitos.
    Auth Enviar registro com CPF inválido    tamanho

Validar CPFs com todos os dígitos iguais
    [Documentation]    Envia o registro com CPF de dígitos repetidos.
    Auth Enviar registro com CPF inválido    repetido

Validar CPF com dígito verificador adulterado
    [Documentation]    Envia o registro com CPF de dígito verificador errado.
    Auth Enviar registro com CPF inválido    digito_errado

Validar CPF correto
    [Documentation]    Envia o registro com CPF válido (sem máscara; a máscara é aplicada pela tela).
    Ir para    /register
    ${email}=    Gerar Email Unico    ${DOMINIO_NOVO}
    ${cpf}=    Gerar Cpf Valido
    Preencher formulário de registro    Usuario Robot    ${email}    ${cpf}    ${SENHA_NOVA}

Verificar que o CPF inválido foi rejeitado com mensagem de erro
    [Documentation]    Permanece em /register com erro sob o CPF.
    Verificar URL termina com    /register
    Verificar mensagem de erro de campo    ${TRECHO_CPF_INVALIDO}
    Capture Page Screenshot    ct15-cpf-${TIPO_CPF}.png

Verificar que o CPF válido foi aceito sem erro
    [Documentation]    Registro aceito: o usuário vai para /home.
    Verificar URL termina com    /home

Auth Definir payload de registro
    [Documentation]    Guarda e-mail e CPF do payload em variáveis do caso de teste.
    [Arguments]    ${email}    ${cpf}
    Set Test Variable    ${EMAIL_NOVO}    ${email}
    Set Test Variable    ${CPF_NOVO}    ${cpf}

Auth Enviar registro com CPF inválido
    [Documentation]    Recarrega /register, gera e-mail novo e envia o formulário com CPF inválido do tipo informado.
    [Arguments]    ${tipo}
    Set Test Variable    ${TIPO_CPF}    ${tipo}
    Ir para    /register
    ${email}=    Gerar Email Unico    ${DOMINIO_NOVO}
    ${cpf}=    Gerar Cpf Invalido    ${tipo}
    Preencher formulário de registro    Usuario Robot    ${email}    ${cpf}    ${SENHA_NOVA}

Auth Registrar usuário descartável
    [Documentation]    Registra um usuário novo no domínio do seed e guarda o e-mail em ${EMAIL_NOVO}.
    Ir para    /register
    ${email}=    Gerar Email Unico    ${DOMINIO_SEED}
    ${cpf}=    Gerar Cpf Valido
    Preencher formulário de registro    Usuario Robot    ${email}    ${cpf}    ${SENHA_NOVA}
    Set Test Variable    ${EMAIL_NOVO}    ${email}

Auth Página não deve ser de erro
    [Documentation]    Garante que a página atual é a carteira e não uma tela de erro.
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
