*** Settings ***
Resource          ../resources/comum.resource
Library           String


*** Variables ***
${EMAIL_MOTORISTA}             motorista@org.com.br
${NOME_MOTORISTA}              motorista
${SENHA_PASSAGEIRO_TESTE}      SenhaForte123!
${ORIGEM_PASSAGEIRO}           Rua XV de Novembro Curitiba
${ORIGEM_MOTORISTA}            Praça Tiradentes Curitiba
${DESTINO_TESTE}               Jardim Botânico Curitiba
${SALDO_PASSAGEIRO_TESTE}      100.00
${SEL_TITULO_PASSAGEIRO}       css:.bottom-panel__title
${SEL_INICIAR_TRAJETO}         css:.bottom-panel form[hx-post$="/iniciar"] button
${SEL_CANCELAR_TRAJETO}        css:.bottom-panel > form[hx-post] button.btn--red
${XPATH_MARCADOR_SUGESTAO}     //div[contains(@class,'maplibregl-marker') and .//*[contains(@fill,'be602d')]]
${XPATH_MARCADOR_PARADA}       //div[contains(@class,'maplibregl-marker') and .//*[contains(@fill,'365691')]]


*** Keywords ***
Preparar teste de trajetos
    [Documentation]    Inicializa dados por caso, sem depender da execução de outro CT.
    Set Test Variable    ${EMAIL_PASSAGEIRO_TESTE}    ${EMPTY}
    Set Test Variable    ${URL_PEDIDO_TESTE}    ${EMPTY}
    Set Test Variable    ${URL_TRAJETO_TESTE}    ${EMPTY}
    Set Test Variable    ${PEDIDO_TESTE_ATIVO}    ${FALSE}
    Set Test Variable    ${TRAJETO_TESTE_ATIVO}    ${FALSE}
    Set Test Variable    ${NAVEGADOR_MOTORISTA_TESTE}    ${EMPTY}
    Abrir o navegador
    ${navegadores}=    Get Browser Ids
    Set Test Variable    ${NAVEGADOR_PASSAGEIRO_TESTE}    ${navegadores}[-1]

Encerrar teste de trajetos
    [Documentation]    Registra a tela antes da limpeza, cancela apenas os registros deste CT e sempre fecha o navegador.
    ...                Falhas de limpeza também reprovam o caso, mas não impedem as demais tentativas de encerramento.
    TRY
        Run Keyword And Ignore Error    Capture Page Screenshot
        Run Keyword And Continue On Failure    Limpar trajeto de teste
        Run Keyword And Continue On Failure    Limpar pedido de teste
    FINALLY
        Close All Browsers
    END

Registrar passageiro de teste
    [Documentation]    Cadastra passageiro com e-mail e CPF novos no domínio do seed; o cadastro já inicia a sessão.
    ${email}=    Gerar Email Unico    ${DOMINIO_SEED}
    ${cpf}=    Gerar Cpf Valido
    Set Test Variable    ${EMAIL_PASSAGEIRO_TESTE}    ${email}
    Set Test Variable    ${NOME_PASSAGEIRO_TESTE}    Passageiro Victor ${cpf}
    Ir para    /register
    Preencher formulário de registro    ${NOME_PASSAGEIRO_TESTE}    ${email}    ${cpf}    ${SENHA_PASSAGEIRO_TESTE}
    Wait Until Keyword Succeeds    ${TIMEOUT}    200ms    Location Should Be    ${BASE_URL}/home
    Wait Until Element Is Visible    css:.home-layout

Criar pedido do passageiro de teste
    [Documentation]    Deposita saldo pela carteira e cria pedido pelo autocomplete, guardando a URL para verificação e limpeza.
    Ir para    /carteira
    Wait Until Element Is Visible    id:valor-input
    Input Text    id:valor-input    ${SALDO_PASSAGEIRO_TESTE}
    Click Button    css:form[action$="/carteira"] button[type=submit]
    Wait Until Keyword Succeeds    ${TIMEOUT}    200ms    Textfield Value Should Be    id:valor-input    ${EMPTY}
    Element Text Should Be    css:.wallet__balance    R$ ${SALDO_PASSAGEIRO_TESTE}
    Ir para    /home
    Selecionar endereço do trajeto    origem    ${ORIGEM_PASSAGEIRO}
    Selecionar endereço do trajeto    destino    ${DESTINO_TESTE}
    Wait Until Element Is Visible    css:#estimate-container button[type=submit]
    Click Button    css:#estimate-container button[type=submit]
    Wait Until Location Contains    /pedido-carona/
    ${url}=    Get Location
    ${id}=    Fetch From Right    ${url}    /
    Set Test Variable    ${URL_PEDIDO_TESTE}    ${url}
    Set Test Variable    ${ID_PEDIDO_TESTE}    ${id}
    Set Test Variable    ${PEDIDO_TESTE_ATIVO}    ${TRUE}
    Verificar status do passageiro    Procurando motorista...

Selecionar endereço do trajeto
    [Arguments]    ${campo}    ${busca}
    Wait Until Element Is Visible    css:#${campo}-wrapper input[type=text]
    Input Text    css:#${campo}-wrapper input[type=text]    ${busca}
    Wait Until Element Is Visible    css:#${campo}-wrapper .autocomplete-item__button
    Click Element    css:#${campo}-wrapper .autocomplete-item__button
    Wait Until Keyword Succeeds    ${TIMEOUT}    200ms    Endereço do trajeto deve estar selecionado    ${campo}

Endereço do trajeto deve estar selecionado
    [Documentation]    Apenas lê os campos preenchidos pelo autocomplete; não injeta dados nos inputs ocultos.
    [Arguments]    ${campo}
    ${coordenadas}=    Get Value    id:${campo}_coords
    ${endereco}=    Get Value    id:${campo}_endereco
    Should Not Be Empty    ${coordenadas}
    Should Not Be Empty    ${endereco}

Acessar como motorista de teste
    [Documentation]    Usa uma sessão própria e faz login apenas uma vez por CT, evitando o limite de tentativas do Fortify.
    IF    $NAVEGADOR_MOTORISTA_TESTE == ''
        Abrir o navegador
        ${navegadores}=    Get Browser Ids
        Fazer login    ${EMAIL_MOTORISTA}    ${SENHA_SEED}
        Set Test Variable    ${NAVEGADOR_MOTORISTA_TESTE}    ${navegadores}[-1]
    ELSE
        Switch Browser    ${NAVEGADOR_MOTORISTA_TESTE}
    END

Acessar como passageiro de teste
    [Documentation]    Retoma a sessão iniciada no cadastro do passageiro, sem novo login a cada verificação.
    Switch Browser    ${NAVEGADOR_PASSAGEIRO_TESTE}

Publicar trajeto de teste
    [Documentation]    Publica um trajeto próprio, com origem próxima e distinta da do passageiro, e guarda sua URL.
    Ir para    /home/motorista
    Selecionar endereço do trajeto    origem    ${ORIGEM_MOTORISTA}
    Selecionar endereço do trajeto    destino    ${DESTINO_TESTE}
    Click Button    css:form.route-builder button[type=submit]
    Wait Until Location Contains    /trajeto/
    ${url}=    Get Location
    Set Test Variable    ${URL_TRAJETO_TESTE}    ${url}
    Set Test Variable    ${TRAJETO_TESTE_ATIVO}    ${TRUE}
    Wait Until Element Is Visible    ${SEL_INICIAR_TRAJETO}

Preparar trajeto com passageiro aceito
    Registrar passageiro de teste
    Criar pedido do passageiro de teste
    Acessar como motorista de teste
    Publicar trajeto de teste
    Aceitar passageiro de teste
    Acessar como passageiro de teste
    Verificar status do passageiro    Carona aceita
    Acessar como motorista de teste

Aceitar passageiro de teste
    [Documentation]    Procura o popup do passageiro deste caso e confirma o formulário vinculado ao ID do seu pedido.
    Go To    ${URL_TRAJETO_TESTE}
    Wait Until Keyword Succeeds    ${TIMEOUT}    500ms    Localizar sugestão do passageiro de teste
    ${botao}=    Set Variable    css:.maplibregl-popup-content form[hx-post$="/caronas/${ID_PEDIDO_TESTE}"] button
    Click Button    ${botao}
    Wait Until Page Does Not Contain Element    ${botao}

Localizar sugestão do passageiro de teste
    ${quantidade}=    Get Element Count    xpath:${XPATH_MARCADOR_SUGESTAO}
    FOR    ${indice}    IN RANGE    1    ${quantidade} + 1
        Fechar popup do mapa
        Click Element    xpath:(${XPATH_MARCADOR_SUGESTAO})[${indice}]
        ${titulo}=    Set Variable    xpath://div[contains(@class,'maplibregl-popup-content')]//span[contains(@class,'map-popup__title') and normalize-space()='${NOME_PASSAGEIRO_TESTE}']
        ${encontrado}=    Get Element Count    ${titulo}
        IF    ${encontrado} > 0
            Element Should Be Visible    ${titulo}
            RETURN
        END
    END
    Fail    O passageiro ${NOME_PASSAGEIRO_TESTE} não foi encontrado nas sugestões do trajeto.

Fechar popup do mapa
    ${quantidade}=    Get Element Count    css:.maplibregl-popup-close-button
    IF    ${quantidade} > 0
        Click Button    css:.maplibregl-popup-close-button
    END

Iniciar trajeto de teste
    Go To    ${URL_TRAJETO_TESTE}
    Wait Until Element Is Visible    ${SEL_INICIAR_TRAJETO}
    Click Button    ${SEL_INICIAR_TRAJETO}
    Wait Until Page Does Not Contain Element    ${SEL_INICIAR_TRAJETO}
    Location Should Be    ${URL_TRAJETO_TESTE}

Abrir ação da parada no mapa
    [Documentation]    Examina os marcadores azuis do trajeto próprio até abrir a ação desejada; nenhuma ação é enviada nas tentativas.
    [Arguments]    ${acao}
    Go To    ${URL_TRAJETO_TESTE}
    Wait Until Keyword Succeeds    ${TIMEOUT}    500ms    Localizar ação da parada no mapa    ${acao}

Localizar ação da parada no mapa
    [Arguments]    ${acao}
    ${quantidade}=    Get Element Count    xpath:${XPATH_MARCADOR_PARADA}
    FOR    ${indice}    IN RANGE    ${quantidade}    0    -1
        Fechar popup do mapa
        Click Element    xpath:(${XPATH_MARCADOR_PARADA})[${indice}]
        ${botao}=    Set Variable    xpath://div[contains(@class,'maplibregl-popup-content')]//button[normalize-space()='${acao}']
        ${encontrado}=    Get Element Count    ${botao}
        IF    ${encontrado} > 0
            Element Should Be Visible    ${botao}
            RETURN
        END
    END
    Fail    A ação ${acao} não foi encontrada nas paradas do trajeto de teste.

Embarcar passageiro de teste
    [Documentation]    Há somente uma carona aceita neste trajeto; confirma o embarque pelo popup da sua parada.
    Abrir ação da parada no mapa    Embarcar
    ${botao}=    Set Variable    css:.maplibregl-popup-content form[hx-post*="/embarcar/"] button
    Click Button    ${botao}
    Wait Until Page Does Not Contain Element    ${botao}

Cancelar carona do passageiro de teste
    Abrir ação da parada no mapa    Cancelar
    ${botao}=    Set Variable    css:.maplibregl-popup-content form[hx-delete*="/caronas/"] button
    Click Button    ${botao}
    Wait Until Page Does Not Contain Element    ${botao}

Finalizar trajeto de teste
    Abrir ação da parada no mapa    Finalizar
    Click Button    css:.maplibregl-popup-content form[hx-post$="/finalizar"] button
    Wait Until Keyword Succeeds    ${TIMEOUT}    200ms    Location Should Be    ${BASE_URL}/home
    Set Test Variable    ${TRAJETO_TESTE_ATIVO}    ${FALSE}
    Set Test Variable    ${PEDIDO_TESTE_ATIVO}    ${FALSE}

Verificar status do passageiro
    [Arguments]    ${titulo_esperado}
    Go To    ${URL_PEDIDO_TESTE}
    Wait Until Element Is Visible    ${SEL_TITULO_PASSAGEIRO}
    Element Text Should Be    ${SEL_TITULO_PASSAGEIRO}    ${titulo_esperado}

Verificar indicador do pedido
    [Arguments]    ${status_esperado}
    Ir para    /home
    Verificar indicador de viagem ativa    ${URL_PEDIDO_TESTE}    Pedido    ${status_esperado}

Verificar indicador do trajeto
    [Arguments]    ${status_esperado}
    Ir para    /home/motorista
    Verificar indicador de viagem ativa    ${URL_TRAJETO_TESTE}    Trajeto    ${status_esperado}

Verificar indicador de viagem ativa
    [Arguments]    ${url}    ${rotulo}    ${status}
    ${indicador}=    Set Variable    xpath://a[contains(@class,'floating-pill') and @href='${url}']
    Wait Until Element Is Visible    ${indicador}
    ${rotulo_exibido}=    Get Text    ${indicador}//span[contains(@class,'floating-pill__label')]
    Should Be Equal As Strings    ${rotulo_exibido}    ${rotulo}    ignore_case=True
    Element Text Should Be    ${indicador}//span[contains(@class,'floating-pill__value')]    ${status}

Abrir pedido pelo indicador de viagem ativa
    Click Element    xpath://a[contains(@class,'floating-pill') and @href='${URL_PEDIDO_TESTE}']
    Wait Until Keyword Succeeds    ${TIMEOUT}    200ms    Location Should Be    ${URL_PEDIDO_TESTE}

Abrir trajeto pelo indicador de viagem ativa
    Click Element    xpath://a[contains(@class,'floating-pill') and @href='${URL_TRAJETO_TESTE}']
    Wait Until Keyword Succeeds    ${TIMEOUT}    200ms    Location Should Be    ${URL_TRAJETO_TESTE}

Verificar ausência de indicador de viagem ativa
    Ir para    /home
    Wait Until Element Is Visible    css:.home-layout
    Page Should Not Contain Element    css:a.floating-pill

Verificar ausência do indicador do trajeto concluído
    Ir para    /home/motorista
    Wait Until Element Is Visible    css:form.route-builder
    Page Should Not Contain Element    xpath://a[contains(@class,'floating-pill') and @href='${URL_TRAJETO_TESTE}']

Limpar trajeto de teste
    IF    $TRAJETO_TESTE_ATIVO
        Acessar como motorista de teste
        Go To    ${URL_TRAJETO_TESTE}
        Wait Until Element Is Visible    ${SEL_CANCELAR_TRAJETO}
        Click Button    ${SEL_CANCELAR_TRAJETO}
        Wait Until Keyword Succeeds    ${TIMEOUT}    200ms    Location Should Be    ${BASE_URL}/home
        Set Test Variable    ${TRAJETO_TESTE_ATIVO}    ${FALSE}
    END

Limpar pedido de teste
    IF    $PEDIDO_TESTE_ATIVO
        Acessar como passageiro de teste
        Go To    ${URL_PEDIDO_TESTE}
        Wait Until Element Is Visible    css:.bottom-panel form[action$="/pedido-carona/${ID_PEDIDO_TESTE}"] button
        Click Button    css:.bottom-panel form[action$="/pedido-carona/${ID_PEDIDO_TESTE}"] button
        Wait Until Keyword Succeeds    ${TIMEOUT}    200ms    Location Should Be    ${BASE_URL}/home
        Set Test Variable    ${PEDIDO_TESTE_ATIVO}    ${FALSE}
    END
