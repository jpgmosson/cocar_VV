*** Settings ***
Resource          ../resources/comum.resource
Library           String

*** Variables ***
${SENHA_NOVA}               SenhaForte123!
${EMAIL_MOTORISTA}          motorista@org.com.br
${ENDERECO_ORIGEM}          Rua XV de Novembro Curitiba
${ENDERECO_DESTINO}         Jardim Botânico Curitiba
${SALDO_INICIAL}            50.00
${VALOR_DEPOSITO}           25.50
${SALDO_ESPERADO}           75.50
${STATUS_SUCESSO}           sucesso
${XPATH_MARCADOR_PARADA}    //div[contains(@class,'maplibregl-marker') and .//*[contains(@fill,'365691')]]
${BTN_FINALIZAR}            xpath://div[contains(@class,'maplibregl-popup-content')]//button[normalize-space()='Finalizar']
${BTN_INICIAR}              xpath://button[normalize-space()='Iniciar Trajeto']


*** Keywords ***
Criar um usuário com saldo de R$ 50,00
    [Documentation]    Registra um passageiro descartável e deposita R$ 50,00 pela carteira (equivale a criarUsuarioComSaldo('50.00')).
    Cart Registrar passageiro descartável
    Cart Depositar pela carteira    ${SALDO_INICIAL}
    Cart Saldo da carteira deve ser    ${SALDO_INICIAL}

Depositar R$ 25,50 pela carteira
    [Documentation]    Abre /carteira, digita 25.50 no campo de valor e envia (equivale ao PATCH em carteira.depositar).
    Cart Depositar pela carteira    ${VALOR_DEPOSITO}

Verificar que o depósito voltou para a carteira sem erros
    [Documentation]    Termina em /carteira (redirect back) sem tela de erro. A view da carteira não exibe erros de validação,
    ...                então a ausência de erro é confirmada pelo saldo atualizado no passo seguinte.
    Verificar URL termina com    /carteira
    Cart Página não deve ser de erro

Verificar que o saldo da carteira foi atualizado para R$ 75,50
    [Documentation]    O saldo exibido em /carteira passa de R$ 50.00 para R$ 75.50.
    Cart Saldo da carteira deve ser    ${SALDO_ESPERADO}
    Capture Page Screenshot    ct01-saldo-atualizado.png

Verificar que a transação de depósito foi gerada com sucesso
    [Documentation]    Sem acesso ao banco: em /atividade aparece um "Depósito" de R$ 25.50 com status "sucesso"
    ...                (depósito não tem pedido nem trajeto) e o saldo atual do histórico é R$ 75.50.
    Ir para    /atividade
    Cart Histórico deve conter transação    Depósito    ${VALOR_DEPOSITO}    ${STATUS_SUCESSO}
    Element Should Contain    css:.wallet-summary__value    R$ ${SALDO_ESPERADO}
    Capture Page Screenshot    ct01-historico-deposito.png

Depositar sem informar o valor
    [Documentation]    Envia o depósito com o campo valor vazio (equivale ao payload sem a chave valor).
    Cart Enviar depósito inválido    ausente    ${EMPTY}

Depositar valor negativo
    [Documentation]    Envia o depósito com -10.00.
    Cart Enviar depósito inválido    negativo    -10.00

Depositar valor com mais de duas casas decimais
    [Documentation]    Envia o depósito com 10.555.
    Cart Enviar depósito inválido    tres_casas    10.555

Depositar valor não numérico
    [Documentation]    Digita "abc" no campo valor. O input é type=number, então o navegador descarta as letras e o campo segue vazio.
    Cart Enviar depósito inválido    nao_numerico    abc

Depositar valor zero
    [Documentation]    Envia o depósito com 0.00.
    Cart Enviar depósito inválido    zero    0.00

Verificar que o depósito foi rejeitado e o saldo continua R$ 50,00
    [Documentation]    Permanece em /carteira e o saldo não muda. Negativo, zero e três casas decimais são barrados pelo navegador
    ...                (input type=number com min=0.01 e step=0.01); o campo vazio chega ao servidor e é recusado pela regra required.
    ...                A mensagem "Valor inválido, verifique e tente novamente" não é exibida na view, então não é verificada.
    Verificar URL termina com    /carteira
    Cart Saldo da carteira deve ser    ${SALDO_INICIAL}
    Capture Page Screenshot    ct02-deposito-${TIPO_VALOR}.png

Verificar que nenhuma nova transação foi gerada
    [Documentation]    Sem acesso ao banco: em /atividade existe somente o depósito inicial de R$ 50.00.
    ${quantidade}=    Cart Contar transações do histórico
    Should Be Equal As Integers    ${quantidade}    1
    Cart Histórico deve conter transação    Depósito    ${SALDO_INICIAL}    ${STATUS_SUCESSO}
    Capture Page Screenshot    ct02-historico-sem-novos-depositos.png

Criar um passageiro sem saldo
    [Documentation]    Registra um passageiro descartável; a carteira é criada no registro com saldo R$ 0.00.
    Cart Registrar passageiro descartável
    Cart Saldo da carteira deve ser    0.00

Consultar o valor máximo estimado da corrida
    [Documentation]    Preenche origem e destino na /home e guarda o valor máximo da estimativa em ${VALOR_MAXIMO}
    ...                (é esse valor que o PedidoCaronaService retém ao confirmar o pedido).
    Cart Preencher origem e destino
    ${maximo}=    Cart Obter valor máximo da estimativa
    Set Test Variable    ${VALOR_MAXIMO}    ${maximo}
    Log    Valor máximo estimado: R$ ${VALOR_MAXIMO}

Depositar exatamente o valor máximo estimado
    [Documentation]    Deposita o valor máximo estimado, deixando o saldo exatamente igual ao valor da retenção (CT03, passo 1).
    Cart Depositar pela carteira    ${VALOR_MAXIMO}
    Cart Saldo da carteira deve ser    ${VALOR_MAXIMO}

Solicitar a carona no mesmo trajeto
    [Documentation]    Preenche a mesma origem e destino e clica em "Confirmar Pedido" (equivale a chamar reterValor).
    Cart Preencher origem e destino
    Cart Confirmar pedido de carona

Verificar que o saldo da carteira foi zerado
    [Documentation]    Com o saldo igual ao valor retido, a carteira fica em R$ 0.00.
    Cart Saldo da carteira deve ser    0.00
    Capture Page Screenshot    ct03-saldo-zerado.png

Verificar que a retenção foi registrada com sucesso
    [Documentation]    Sem acesso ao banco: em /atividade aparece a "Reserva de Saldo" do valor máximo com status "sucesso"
    ...                e o saldo atual do histórico é R$ 0.00.
    Ir para    /atividade
    Cart Histórico deve conter transação    Reserva de Saldo    ${VALOR_MAXIMO}    ${STATUS_SUCESSO}
    Element Should Contain    css:.wallet-summary__value    R$ 0.00
    Capture Page Screenshot    ct03-historico-retencao.png

Cancelar o pedido criado para não interferir nos outros testes
    [Documentation]    Cancela o pedido pelo painel do passageiro para ele não aparecer como sugestão nos trajetos de outros testes.
    Go To    ${URL_PEDIDO}
    Wait Until Element Is Visible    css:.bottom-panel form button.btn--red
    Click Button    css:.bottom-panel form button.btn--red
    Wait Until Keyword Succeeds    ${TIMEOUT}    200ms    URL não deve conter    /pedido-carona/

Depositar um centavo a menos que o valor máximo estimado
    [Documentation]    Deposita o valor máximo menos R$ 0,01 (equivale ao saldo de 29.99 contra uma retenção de 30.00).
    ${valor}=    Evaluate    str(decimal.Decimal($VALOR_MAXIMO) - decimal.Decimal('0.01'))    modules=decimal
    Set Test Variable    ${SALDO_INSUFICIENTE}    ${valor}
    Cart Depositar pela carteira    ${SALDO_INSUFICIENTE}
    Cart Saldo da carteira deve ser    ${SALDO_INSUFICIENTE}

Tentar solicitar a carona no mesmo trajeto
    [Documentation]    Preenche a mesma origem e destino na /home para pedir a carona.
    Cart Preencher origem e destino

Verificar que o pedido foi bloqueado por saldo insuficiente
    [Documentation]    A estimativa mostra "Saldo Insuficiente", informa o saldo atual e não exibe o botão "Confirmar Pedido";
    ...                o usuário continua na /home, sem pedido criado.
    Wait Until Element Is Visible    css:#estimate-container .cost-estimate--error
    ${rotulo}=    Get Text    css:#estimate-container .cost-estimate__label
    Should Be Equal As Strings    ${rotulo}    Saldo Insuficiente    ignore_case=True
    Element Should Contain    css:#estimate-container .feedback--error    R$ ${SALDO_INSUFICIENTE}
    Page Should Not Contain Element    css:#estimate-container button[type=submit]
    Verificar URL termina com    /home
    Capture Page Screenshot    ct04-saldo-insuficiente.png

Verificar que o saldo da carteira não foi alterado
    [Documentation]    O saldo continua um centavo abaixo do valor máximo.
    Cart Saldo da carteira deve ser    ${SALDO_INSUFICIENTE}

Verificar que nenhuma retenção foi registrada
    [Documentation]    Sem acesso ao banco: em /atividade existe somente o depósito e nenhuma "Reserva de Saldo".
    ${quantidade}=    Cart Contar transações do histórico
    Should Be Equal As Integers    ${quantidade}    1
    Page Should Not Contain Element    xpath://details[contains(@class,'tx-card')]//span[contains(@class,'tx-card__title') and normalize-space()='Reserva de Saldo']
    Capture Page Screenshot    ct04-historico-sem-retencao.png

Autenticar o motorista do seed
    [Documentation]    Login como o motorista aprovado do seed.
    Fazer login    ${EMAIL_MOTORISTA}    ${SENHA_SEED}

Guardar o saldo e o histórico atuais do motorista
    [Documentation]    O seed cria o motorista com saldo R$ 0.00 (equivale a criarUsuarioComSaldo('0.00')). Como a base pode já ter sido
    ...                usada por outros testes, guarda o saldo e a quantidade de transações para comparar no fim.
    ${saldo}=    Cart Obter saldo da carteira
    ${quantidade}=    Cart Contar transações do histórico
    Set Test Variable    ${SALDO_ANTES}    ${saldo}
    Set Test Variable    ${QTD_TRANSACOES_ANTES}    ${quantidade}

Publicar um trajeto sem aceitar caronas
    [Documentation]    Em /home/motorista escolhe origem e destino e clica em "Publicar Trajeto"; as sugestões do mapa são ignoradas.
    Ir para    /home/motorista
    Cart Selecionar endereço no autocomplete    origem    ${ENDERECO_ORIGEM}
    Cart Selecionar endereço no autocomplete    destino    ${ENDERECO_DESTINO}
    Click Button    css:form.route-builder button[type=submit]
    Wait Until Location Contains    /trajeto/
    ${url}=    Get Location
    Set Test Variable    ${URL_TRAJETO}    ${url}

Iniciar o trajeto
    [Documentation]    Clica em "Iniciar Trajeto" (hx-post em trajeto.iniciar); a página recarrega sem o botão.
    Wait Until Element Is Visible    ${BTN_INICIAR}
    Click Button    ${BTN_INICIAR}
    Wait Until Page Does Not Contain Element    ${BTN_INICIAR}

Verificar que o trajeto está em andamento
    [Documentation]    Na /home/motorista, o atalho flutuante de viagem ativa aponta para o trajeto com o status "Em Andamento".
    Ir para    /home/motorista
    Wait Until Page Contains Element    xpath://a[contains(@class,'floating-pill') and @href='${URL_TRAJETO}']//span[contains(@class,'floating-pill__value') and normalize-space()='Em Andamento']
    Capture Page Screenshot    ct06-trajeto-em-andamento.png

Finalizar o trajeto
    [Documentation]    Volta ao trajeto, abre o popup do marcador de destino (último marcador azul) e clica em "Finalizar"
    ...                (equivale a chamar TrajetoService::finalizarTrajeto).
    Go To    ${URL_TRAJETO}
    Wait Until Page Contains Element    xpath:(${XPATH_MARCADOR_PARADA})[2]
    Click Element    xpath:(${XPATH_MARCADOR_PARADA})[last()]
    Wait Until Element Is Visible    ${BTN_FINALIZAR}
    Click Button    ${BTN_FINALIZAR}

Verificar que o trajeto foi concluído
    [Documentation]    Termina em /home e o atalho flutuante do trajeto some (ele só aparece para trajeto planejado ou em andamento).
    ...                Sem acesso ao banco, é assim que o status "concluido" é confirmado.
    Verificar URL termina com    /home
    Page Should Not Contain Element    xpath://a[contains(@class,'floating-pill') and @href='${URL_TRAJETO}']
    Capture Page Screenshot    ct06-trajeto-concluido.png

Verificar que nenhuma transação foi gerada para o motorista
    [Documentation]    Sem acesso ao banco: o histórico do motorista em /atividade tem a mesma quantidade de transações de antes
    ...                (sem "Fechamento da Carona" nem "Ajuda de Custo").
    ${quantidade}=    Cart Contar transações do histórico
    Should Be Equal As Integers    ${quantidade}    ${QTD_TRANSACOES_ANTES}
    Capture Page Screenshot    ct06-historico-motorista.png

Verificar que o saldo do motorista não foi alterado
    [Documentation]    O saldo em /carteira é o mesmo guardado antes de publicar o trajeto.
    Cart Saldo da carteira deve ser    ${SALDO_ANTES}

Cart Registrar passageiro descartável
    [Documentation]    Registra um usuário novo no domínio do seed (o registro já deixa logado em /home) e guarda o e-mail em ${EMAIL_NOVO}.
    Ir para    /register
    ${email}=    Gerar Email Unico    ${DOMINIO_SEED}
    ${cpf}=    Gerar Cpf Valido
    Preencher formulário de registro    Passageiro Robot    ${email}    ${cpf}    ${SENHA_NOVA}
    Verificar URL termina com    /home
    Set Test Variable    ${EMAIL_NOVO}    ${email}

Cart Depositar pela carteira
    [Documentation]    Abre /carteira, digita o valor e envia; espera a página recarregar com o campo vazio.
    [Arguments]    ${valor}
    Ir para    /carteira
    Wait Until Element Is Visible    id:valor-input
    Input Text    id:valor-input    ${valor}
    Click Button    css:form[action$="/carteira"] button[type=submit]
    Wait Until Keyword Succeeds    ${TIMEOUT}    200ms    Textfield Value Should Be    id:valor-input    ${EMPTY}

Cart Enviar depósito inválido
    [Documentation]    Recarrega /carteira, digita o valor inválido do tipo informado e clica em "Inserir Saldo" sem esperar sucesso.
    [Arguments]    ${tipo}    ${valor}
    Set Test Variable    ${TIPO_VALOR}    ${tipo}
    Ir para    /carteira
    Wait Until Element Is Visible    id:valor-input
    Input Text    id:valor-input    ${valor}
    Click Button    css:form[action$="/carteira"] button[type=submit]

Cart Obter saldo da carteira
    [Documentation]    Abre /carteira e devolve o saldo exibido, sem o "R$" (ex.: 75.50).
    Ir para    /carteira
    Wait Until Element Is Visible    css:.wallet__balance
    ${texto}=    Get Text    css:.wallet__balance
    ${saldo}=    Fetch From Right    ${texto}    R$
    ${saldo}=    Strip String    ${saldo}
    RETURN    ${saldo}

Cart Saldo da carteira deve ser
    [Documentation]    Abre /carteira e confere o saldo exibido (ex.: R$ 75.50).
    [Arguments]    ${valor}
    Ir para    /carteira
    Wait Until Element Is Visible    css:.wallet__balance
    Element Text Should Be    css:.wallet__balance    R$ ${valor}

Cart Página não deve ser de erro
    [Documentation]    Garante que a página atual é a carteira e não uma tela de erro.
    Page Should Contain Element    css:.wallet__balance
    Page Should Not Contain    Valor inválido
    Page Should Not Contain    Server Error
    Page Should Not Contain    Whoops

Cart Contar transações do histórico
    [Documentation]    Abre /atividade e devolve a quantidade de cartões de transação do extrato.
    Ir para    /atividade
    Wait Until Element Is Visible    css:.wallet-panel
    ${quantidade}=    Get Element Count    css:.tx-card
    RETURN    ${quantidade}

Cart Histórico deve conter transação
    [Documentation]    Espera, na página /atividade, um cartão com o título, o valor (R$) e o status informados.
    [Arguments]    ${titulo}    ${valor}    ${status}
    ${cartao}=    Catenate    SEPARATOR=
    ...    xpath://details[contains(@class,'tx-card')]
    ...    [.//span[contains(@class,'tx-card__title') and normalize-space()='${titulo}']]
    ...    [.//span[contains(@class,'tx-card__value') and contains(normalize-space(),'R$ ${valor}')]]
    ...    [.//div[contains(@class,'tx-card__meta')]/span[contains(@class,'badge') and normalize-space()='${status}']]
    Wait Until Page Contains Element    ${cartao}

Cart Selecionar endereço no autocomplete
    [Documentation]    Digita o endereço no campo (origem ou destino) e clica na primeira sugestão do autocomplete.
    [Arguments]    ${campo}    ${busca}
    Wait Until Element Is Visible    css:#${campo}-wrapper input[type=text]
    Input Text    css:#${campo}-wrapper input[type=text]    ${busca}
    Wait Until Element Is Visible    css:#${campo}-wrapper .autocomplete-item__button
    Click Element    css:#${campo}-wrapper .autocomplete-item__button

Cart Preencher origem e destino
    [Documentation]    Abre a /home do passageiro, escolhe origem e destino e espera a estimativa de custo carregar.
    Ir para    /home
    Cart Selecionar endereço no autocomplete    origem    ${ENDERECO_ORIGEM}
    Cart Selecionar endereço no autocomplete    destino    ${ENDERECO_DESTINO}
    Wait Until Element Is Visible    css:#estimate-container .cost-estimate

Cart Obter valor máximo da estimativa
    [Documentation]    Lê a faixa "R$ min - R$ max" da estimativa e devolve só o máximo (ex.: 12.34).
    Wait Until Element Is Visible    css:#estimate-container .cost-estimate__range
    ${faixa}=    Get Text    css:#estimate-container .cost-estimate__range
    ${maximo}=    Fetch From Right    ${faixa}    R$
    ${maximo}=    Strip String    ${maximo}
    RETURN    ${maximo}

Cart Confirmar pedido de carona
    [Documentation]    Clica em "Confirmar Pedido", espera /pedido-carona/{id} com "Procurando motorista..." e guarda a URL em ${URL_PEDIDO}.
    Wait Until Element Is Visible    css:#estimate-container button[type=submit]
    Click Button    css:#estimate-container button[type=submit]
    Wait Until Location Contains    /pedido-carona/
    ${url}=    Get Location
    Set Test Variable    ${URL_PEDIDO}    ${url}
    Wait Until Element Is Visible    css:.bottom-panel__title
    Element Should Contain    css:.bottom-panel__title    Procurando motorista...
