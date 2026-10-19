*** Settings ***
Resource          ../resources/comum.resource


*** Variables ***
${EMAIL_MOTORISTA}               motorista@org.com.br
${SENHA_PADRAO}                  SenhaForte123!
${ENDERECO_ORIGEM_PERTO}         Rua XV de Novembro Curitiba
${ENDERECO_DESTINO_PERTO}        Jardim Botânico Curitiba
${ENDERECO_ORIGEM_LONGE}         Aeroporto Afonso Pena São José dos Pinhais

${SEL_AUTOCOMPLETE_ITEM}         css:.autocomplete-item__button
${SEL_SUGESTOES_CONTAINER}       id:sugestoes-carona
${SEL_POPUP_SUGESTAO}            xpath://div[contains(@class, 'map-popup')]

${SEL_BTN_CANCELAR_TRAJETO}      css:button.btn--red
${SEL_MARKER_PASSAGEIRO}         xpath://div[contains(@class, 'maplibregl-marker') and .//*[contains(@fill, 'be602d')]]
${SEL_POPUP_TITULO}              css:.maplibregl-popup-content .map-popup__title
${SEL_BTN_ADICIONAR_CARONA}      css:.maplibregl-popup-content form.action-form button[type=submit]
${SEL_INPUT_VALOR}               id:valor-input
${SEL_BTN_SUBMIT_CARTEIRA}       css:form[action$="/carteira"] button[type=submit]
${SEL_SALDO_CARTEIRA}            css:.wallet__balance

${SEL_ESTIMATIVA_ERRO}           css:#estimate-container .cost-estimate--error
${SEL_ESTIMATIVA_LABEL}          css:#estimate-container .cost-estimate__label
${SEL_ESTIMATIVA_FEEDBACK}       css:#estimate-container .feedback--error
${SEL_ESTIMATIVA_BTN_SUBMIT}     css:#estimate-container button[type=submit]

${SEL_BTN_CANCELAR_PEDIDO}       css:.bottom-panel form button.btn--red
${SEL_TITULO_PAINEL_PASSAGEIRO}  css:.bottom-panel__title

${SEL_PAINEL_CARTEIRA}           css:.wallet-panel
${SEL_CARTAO_TRANSACAO}          css:.tx-card
${SEL_LISTA_TRANSACOES}          css:.tx-list

${SEL_BTN_PUBLICAR_TRAJETO}      css:form.route-builder button[type=submit]


*** Keywords ***
Registrar passageiro descartável
    Ir para    /register
    ${email}=    Gerar Email Unico    ${DOMINIO_SEED}
    ${cpf}=    Gerar Cpf Valido
    ${nome}=    Set Variable    Passageiro ${cpf}
    Preencher formulário de registro    ${nome}    ${email}    ${cpf}    ${SENHA_PADRAO}
    Verificar URL termina com    /home
    RETURN    ${email}    ${nome}

Adicionar saldo na carteira
    [Arguments]    ${valor}
    Ir para    /carteira
    Wait Until Element Is Visible    ${SEL_INPUT_VALOR}
    Input Text    ${SEL_INPUT_VALOR}    ${valor}
    Click Button    ${SEL_BTN_SUBMIT_CARTEIRA}
    Wait Until Element Is Visible    ${SEL_SALDO_CARTEIRA}

Obter saldo atual da carteira
    Ir para    /carteira
    Wait Until Element Is Visible    ${SEL_SALDO_CARTEIRA}
    ${texto}=    Get Text    ${SEL_SALDO_CARTEIRA}
    RETURN    ${texto}

Verificar saldo na carteira igual a
    [Arguments]    ${valor_esperado}
    Ir para    /carteira
    Wait Until Element Is Visible    ${SEL_SALDO_CARTEIRA}
    Element Should Contain    ${SEL_SALDO_CARTEIRA}    ${valor_esperado}

Selecionar endereço no autocomplete
    [Arguments]    ${nome_campo}    ${busca}
    Wait Until Element Is Visible    css:#${nome_campo}-wrapper input[type=text]
    Input Text                       css:#${nome_campo}-wrapper input[type=text]    ${busca}
    Wait Until Element Is Visible    css:#${nome_campo}-wrapper .autocomplete-item__button      timeout=${TIMEOUT}
    Click Element                    css:#${nome_campo}-wrapper .autocomplete-item__button

Preencher origem e destino como passageiro
    [Arguments]    ${origem}=${ENDERECO_ORIGEM_PERTO}    ${destino}=${ENDERECO_DESTINO_PERTO}
    Ir para    /home
    Selecionar endereço no autocomplete    origem     ${origem}
    Selecionar endereço no autocomplete    destino    ${destino}

Verificar aviso de saldo insuficiente na estimativa
    Wait Until Element Is Visible    ${SEL_ESTIMATIVA_ERRO}    timeout=${TIMEOUT}
    Element Should Contain    ${SEL_ESTIMATIVA_LABEL}    SALDO INSUFICIENTE
    Element Should Be Visible    ${SEL_ESTIMATIVA_FEEDBACK}
    Page Should Not Contain Element    ${SEL_ESTIMATIVA_BTN_SUBMIT}

Confirmar pedido de carona
    Wait Until Element Is Visible    ${SEL_ESTIMATIVA_BTN_SUBMIT}    timeout=${TIMEOUT}
    Click Button    ${SEL_ESTIMATIVA_BTN_SUBMIT}
    Wait Until Location Contains    /pedido-carona/
    ${url_pedido}=    Get Location
    RETURN    ${url_pedido}

Cancelar pedido atual pelo painel do passageiro
    Wait Until Element Is Visible    ${SEL_BTN_CANCELAR_PEDIDO}
    Click Button    ${SEL_BTN_CANCELAR_PEDIDO}
    Wait Until Keyword Succeeds    ${TIMEOUT}    200ms    URL não deve conter    /pedido-carona/

Verificar histórico de atividade não possui transações
    Ir para    /atividade
    Wait Until Element Is Visible    ${SEL_PAINEL_CARTEIRA}
    Page Should Not Contain Element    ${SEL_CARTAO_TRANSACAO}

Verificar transação registrada no histórico
    [Arguments]    ${tipo_esperado}
    Ir para    /atividade
    Wait Until Element Is Visible    ${SEL_CARTAO_TRANSACAO}
    Element Should Contain    ${SEL_LISTA_TRANSACOES}    ${tipo_esperado}

Verificar status do pedido na tela do passageiro
    [Arguments]    ${url_pedido}    ${titulo_esperado}
    Go To    ${url_pedido}
    Wait Until Element Is Visible    ${SEL_TITULO_PAINEL_PASSAGEIRO}
    Element Should Contain    ${SEL_TITULO_PAINEL_PASSAGEIRO}    ${titulo_esperado}

Publicar trajeto como motorista
    [Arguments]    ${origem}=${ENDERECO_ORIGEM_PERTO}    ${destino}=${ENDERECO_DESTINO_PERTO}
    Ir para    /home/motorista
    Selecionar endereço no autocomplete    origem     ${origem}
    Selecionar endereço no autocomplete    destino    ${destino}
    Click Button    ${SEL_BTN_PUBLICAR_TRAJETO}
    Wait Until Location Contains    /trajeto/
    ${url_trajeto}=    Get Location
    RETURN    ${url_trajeto}

Aguardar carregamento de sugestões no trajeto
    Wait Until Page Contains Element    ${SEL_MARKER_PASSAGEIRO}    timeout=${TIMEOUT}

Abrir popup da sugestão no mapa
    Wait Until Page Contains Element    ${SEL_MARKER_PASSAGEIRO}    timeout=${TIMEOUT}
    Click Element                       ${SEL_MARKER_PASSAGEIRO}
    Wait Until Page Contains Element    ${SEL_POPUP_TITULO}         timeout=${TIMEOUT}

Verificar passageiro presente nas sugestões do trajeto
    [Arguments]    ${nome_passageiro}
    Abrir popup da sugestão no mapa
    Element Should Contain    ${SEL_POPUP_TITULO}    ${nome_passageiro}

Verificar passageiro ausente nas sugestões do trajeto
    [Arguments]    ${nome_esperado}
    ${nomes_no_mapa}=    Execute JavaScript
    ...    return Array.from(document.querySelectorAll('.map-popup__title')).map(e => e.innerText.trim());
    Should Not Contain    ${nomes_no_mapa}    ${nome_esperado}

Aceitar passageiro no trajeto
    [Arguments]    ${nome_passageiro}
    Wait Until Page Contains Element    ${SEL_BTN_ADICIONAR_CARONA}    timeout=${TIMEOUT}
    Click Button                        ${SEL_BTN_ADICIONAR_CARONA}
    Wait Until Page Does Not Contain Element    ${SEL_BTN_ADICIONAR_CARONA}    timeout=${TIMEOUT}

Cancelar trajeto atual pelo painel do motorista
    Wait Until Element Is Visible       ${SEL_BTN_CANCELAR_TRAJETO}    timeout=${TIMEOUT}
    Click Button                        ${SEL_BTN_CANCELAR_TRAJETO}
    Wait Until Keyword Succeeds         ${TIMEOUT}    200ms    URL não deve conter    /trajeto/
