*** Settings ***
Documentation     Recursos compartilhados dos testes de interface do CoCar.
...               CONVENÇÃO: toda nova keyword compartilhada vai AQUI e somente UMA pessoa
...               edita este arquivo, para evitar conflito de merge. Keywords específicas de
...               um caso ficam no .robot da própria pasta.
Library           SeleniumLibrary
Library           ${CURDIR}/GeradorDados.py


*** Variables ***
${BROWSER}                  %{ROBOT_BROWSER=chrome}
${BASE_URL}                 %{ROBOT_BASE_URL=http://127.0.0.1:8000}
${SENHA_SEED}               SenhaForte!
${EMAIL_ADMIN_SISTEMA}      admin@cocar.com.br
${EMAIL_ADMIN_ORG}          admin@org.com.br
${EMAIL_PASSAGEIRO}         passageiro@org.com.br
${DOMINIO_SEED}             org.com.br
${TIMEOUT}                  10s
${BROWSER_BINARY}           %{ROBOT_BROWSER_BINARY=}


*** Keywords ***
Abrir o navegador
    [Documentation]    Abre o navegador na ${BASE_URL}, maximiza e define o timeout. Usa ${BROWSER_BINARY} se definido.
    IF    $BROWSER_BINARY != ''
        Open Browser    ${BASE_URL}    ${BROWSER}    options=binary_location=r"${BROWSER_BINARY}"
    ELSE
        Open Browser    ${BASE_URL}    ${BROWSER}
    END
    Maximize Browser Window
    Set Selenium Timeout    ${TIMEOUT}

Fechar o navegador
    [Documentation]    Tira um screenshot e fecha todos os navegadores.
    Capture Page Screenshot
    Close All Browsers

Ir para
    [Documentation]    Navega para um caminho relativo à ${BASE_URL} (ex.: /register).
    [Arguments]    ${caminho}
    Go To    ${BASE_URL}${caminho}

Fazer login
    [Documentation]    Faz login pela tela /login e espera sair dela.
    [Arguments]    ${email}    ${senha}
    Ir para    /login
    Wait Until Element Is Visible    css:input[name=email]
    Input Text    css:input[name=email]    ${email}
    Input Text    css:input[name=password]    ${senha}
    Click Button    css:form[action$="/login"] button[type=submit]
    Wait Until Keyword Succeeds    ${TIMEOUT}    200ms    URL não deve conter    /login

URL não deve conter
    [Documentation]    Confere que a URL atual não contém o trecho (o SeleniumLibrary não tem Location Should Not Contain).
    [Arguments]    ${trecho}
    ${url}=    Get Location
    Should Not Contain    ${url}    ${trecho}

Encerrar sessão
    [Documentation]    Limpa os cookies em vez de clicar em logout (o botão do perfil não tem @csrf e dá 419).
    Delete All Cookies
    Go To    ${BASE_URL}/login

Preencher campo mascarado
    [Documentation]    Digita no input VISÍVEL de um campo mascarado (ex.: cpf_display). Nunca no hidden nem via JS.
    [Arguments]    ${id_visivel}    ${valor}
    Wait Until Element Is Visible    id:${id_visivel}
    Input Text    id:${id_visivel}    ${valor}

Verificar URL termina com
    [Documentation]    Espera a URL conter o caminho informado e confirma.
    [Arguments]    ${caminho}
    Wait Until Location Contains    ${caminho}
    Location Should Contain    ${caminho}

Verificar mensagem de erro de campo
    [Documentation]    Confere que algum .field__error contém o texto.
    [Arguments]    ${texto}
    Wait Until Element Is Visible    css:.field__error
    Element Should Contain    css:.field__error    ${texto}

Preencher formulário de registro
    [Documentation]    Preenche e envia /register (usa name:email porque o id está duplicado no label).
    [Arguments]    ${nome}    ${email}    ${cpf}    ${senha}
    Wait Until Element Is Visible    css:input[name=name]
    Input Text    css:input[name=name]    ${nome}
    Input Text    css:input[name=email]    ${email}
    Preencher campo mascarado    cpf_display    ${cpf}
    Input Text    css:input[name=password]    ${senha}
    Input Text    css:input[name=password_confirmation]    ${senha}
    Click Button    id:btn-enviar
