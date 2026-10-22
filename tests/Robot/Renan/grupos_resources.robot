*** Settings ***
Documentation     Keywords e variáveis dos testes de grupos de carona e cadastro/triagem de motoristas (CT40, CT41, CT43, CT44 e CT46).
Resource          ../resources/comum.resource
Library           String
Library           ${CURDIR}/GeradorCnh.py


*** Variables ***
${EMAIL_MOTORISTA}                 motorista@org.com.br
${SENHA_PADRAO}                    SenhaForte123!
${CNH_INVALIDA}                    11111111111

${MSG_VAGAS_MAXIMO}                O limite máximo é de 4 vagas por carro.
${MSG_PASSAGEIROS_EXCEDEM}         Selecione no máximo a mesma quantidade de passageiros das vagas disponíveis.
${MSG_GRUPO_CRIADO}                Grupo de carona criado com sucesso!
${MSG_SAIU_DO_GRUPO}               Você saiu do grupo de carona com sucesso!
${MSG_GRUPO_EXCLUIDO}              Grupo de carona excluído com sucesso.
${TXT_SEJA_MOTORISTA}              Seja um Motorista
${TXT_CADASTRO_EM_ANALISE}         Cadastro em Análise

${SEL_GRUPO_NOME}                  id:nome
${SEL_GRUPO_FREQUENCIA}            id:frequencia
${SEL_GRUPO_VAGAS}                 id:vagas
${SEL_GRUPO_SALVAR}                xpath://button[normalize-space()='Salvar Grupo']
${SEL_CNH_VISIVEL}                 id:cnh_display
${SEL_CNH_ENVIAR}                  css:form.driver-onboarding__form button[type=submit]

${XP_CARD_GRUPO}                   //div[contains(@class,'trip-card')][.//span[contains(@class,'trip-card__location') and normalize-space()='NOME_GRUPO']]
${XP_SECAO_INSCRITOS}              //section[.//h3[normalize-space()='Grupos Inscritos']]
${XP_SECAO_DISPONIVEIS}            //section[.//h3[normalize-space()='Grupos Disponíveis']]
${XP_CARD_PENDENTE}                //div[contains(@class,'card--stretch')][.//span[contains(@class,'data-list__value') and normalize-space()='EMAIL_PENDENTE']]


*** Keywords ***
Registrar passageiro descartável
    [Documentation]    Cadastra um passageiro novo (e-mail e CPF únicos) no domínio informado e deixa a sessão aberta em /home.
    ...                Devolve o e-mail e o nome. Sem sessão ativa, pois /register só abre para visitante.
    [Arguments]    ${dominio}=${DOMINIO_SEED}
    Ir para    /register
    ${email}=    Gerar Email Unico    ${dominio}
    ${cpf}=    Gerar Cpf Valido
    ${nome}=    Set Variable    Passageiro Renan ${cpf}
    Preencher formulário de registro    ${nome}    ${email}    ${cpf}    ${SENHA_PADRAO}
    Verificar URL termina com    /home
    RETURN    ${email}    ${nome}

Criar organização descartável
    [Documentation]    Cadastra uma organização nova (CNPJ e domínio únicos) pelo formulário público.
    ...                O cadastro já inicia a sessão do administrador. Devolve o domínio de e-mail da organização.
    ${dominio}=    Gerar Dominio Unico
    ${cnpj}=    Gerar Cnpj Valido
    ${cpf_admin}=    Gerar Cpf Valido
    ${email_admin}=    Gerar Email Unico    ${dominio}
    Ir para    /cadastro/organizacao
    Wait Until Element Is Visible    id:organizacao-nome
    Input Text    id:organizacao-nome    Organização Renan ${cnpj}
    Preencher campo mascarado    cnpj_display    ${cnpj}
    Input Text    id:dominio-email    ${dominio}
    Input Text    id:administrador-nome    Admin Renan ${cpf_admin}
    Input Text    id:administrador-email    ${email_admin}
    Preencher campo mascarado    administrador-cpf_display    ${cpf_admin}
    Input Text    id:password    ${SENHA_PADRAO}
    Input Text    id:password_confirmation    ${SENHA_PADRAO}
    Click Button    css:#formulario button[type=submit]
    Verificar URL termina com    /dashboard
    RETURN    ${dominio}

Acessar como motorista do seed
    [Documentation]    Encerra qualquer sessão e entra como motorista@org.com.br (motorista aprovado do seed).
    Encerrar sessão
    Fazer login    ${EMAIL_MOTORISTA}    ${SENHA_SEED}

Gerar nome único de grupo
    [Documentation]    Nome de grupo com sufixo numérico aleatório, para localizar na lista só o grupo criado neste caso.
    ${sufixo}=    Gerar Cpf Valido
    Set Test Variable    ${NOME_GRUPO}    Grupo Renan ${sufixo}
    RETURN    ${NOME_GRUPO}


# ---------- Limpeza ----------

Limpar grupo e fechar o navegador
    [Documentation]    Teardown dos casos que criam grupo: remove o grupo do banco (se ainda existir) mesmo que o teste
    ...                tenha falhado no meio e depois fecha o navegador. Falha na limpeza não esconde o resultado do teste.
    Run Keyword And Ignore Error    Excluir grupo do teste se existir
    Fechar o navegador

Excluir grupo do teste se existir
    [Documentation]    Entra como motorista dono e decide pelo estado da página: exclui o grupo se o cartão estiver na lista;
    ...                se o nome nunca foi gerado ou o grupo já foi excluído, só registra e segue.
    ${nome}=    Get Variable Value    ${NOME_GRUPO}    ${EMPTY}
    IF    '${nome}' == ''
        Log    Nenhum grupo foi gerado neste teste; nada para limpar.
        RETURN
    END
    Run Keyword And Ignore Error    Handle Alert    action=DISMISS    timeout=1s
    Acessar como motorista do seed
    Ir para    /grupos
    Wait Until Page Contains    Seus Grupos Criados    timeout=${TIMEOUT}
    ${cartao}=    Cartão do grupo    ${nome}
    ${existe}=    Run Keyword And Return Status    Page Should Contain Element    xpath:${cartao}
    IF    ${existe}
        Excluir grupo pelo painel do motorista    ${nome}
    ELSE
        Log    O grupo ${nome} já não existe; nada para limpar.
    END


# ---------- Formulário de criação de grupo ----------

Abrir formulário de criação de grupo
    Ir para    /motorista/criar
    Wait Until Element Is Visible    ${SEL_GRUPO_NOME}

Preencher dados do grupo
    [Documentation]    Preenche nome, frequência e vagas. A frequência alterna os blocos de dias (semanal/mensal) pelo onchange da página.
    [Arguments]    ${nome}    ${frequencia}    ${vagas}
    Input Text    ${SEL_GRUPO_NOME}    ${nome}
    Selecionar frequência do grupo    ${frequencia}
    Input Text    ${SEL_GRUPO_VAGAS}    ${vagas}

Selecionar frequência do grupo
    [Arguments]    ${frequencia}
    Select From List By Value    ${SEL_GRUPO_FREQUENCIA}    ${frequencia}

Marcar dia da semana
    [Documentation]    Clica na pílula do dia (o checkbox real fica oculto por CSS; o clique vai no <span> irmão).
    [Arguments]    ${dia}
    ${pilula}=    Set Variable    xpath://input[@name='dias_semana[]' and @value='${dia}']/following-sibling::span
    Wait Until Element Is Visible    ${pilula}
    Click Element    ${pilula}

Marcar dia do mês
    [Arguments]    ${dia}
    ${circulo}=    Set Variable    xpath://input[@name='dias_mes[]' and @value='${dia}']/following-sibling::span
    Wait Until Element Is Visible    ${circulo}
    Click Element    ${circulo}

Selecionar passageiro na lista
    [Documentation]    Marca o passageiro pelo e-mail exibido no cartão e confirma que o checkbox ficou marcado.
    [Arguments]    ${email}
    ${cartao}=    Set Variable    //label[contains(@class,'passenger-card-label')][.//span[normalize-space()='${email}']]
    Wait Until Page Contains Element    xpath:${cartao}
    Click Element    xpath:${cartao}
    Checkbox Should Be Selected    xpath:${cartao}//input[@type='checkbox']

Desativar validação nativa do formulário
    [Documentation]    O navegador barra vagas fora de 1..4 (input type=number com min/max) antes de enviar.
    ...                Para exercitar a regra do SERVIDOR, desliga só a validação nativa (novalidate); nenhum dado é alterado.
    Execute JavaScript    document.querySelector('form.route-builder').noValidate = true;

Salvar grupo
    Click Button    ${SEL_GRUPO_SALVAR}

Verificar erro de validação do grupo
    [Documentation]    Após o erro o Laravel volta para /motorista/criar e a view mostra a mensagem junto ao campo.
    [Arguments]    ${mensagem}
    Wait Until Page Contains    ${mensagem}    timeout=${TIMEOUT}
    Verificar URL termina com    /motorista/criar

Verificar grupo criado com sucesso
    Verificar URL termina com    /home/motorista
    Wait Until Page Contains    ${MSG_GRUPO_CRIADO}    timeout=${TIMEOUT}

Criar grupo semanal com passageiro
    [Documentation]    Motorista logado cria um grupo semanal (segunda) com o passageiro já selecionado.
    [Arguments]    ${nome}    ${vagas}    ${email_passageiro}
    Abrir formulário de criação de grupo
    Preencher dados do grupo    ${nome}    semanal    ${vagas}
    Marcar dia da semana    seg
    Selecionar passageiro na lista    ${email_passageiro}
    Salvar grupo
    Verificar grupo criado com sucesso


# ---------- Verificações sobre listas de grupos ----------

Cartão do grupo
    [Documentation]    Devolve o XPath (sem prefixo) do cartão do grupo com o nome informado.
    [Arguments]    ${nome}
    ${xpath}=    Replace String    ${XP_CARD_GRUPO}    NOME_GRUPO    ${nome}
    RETURN    ${xpath}

Verificar grupo na lista do motorista
    [Arguments]    ${nome}    ${frequencia_formatada}    ${vagas_texto}
    Ir para    /grupos
    ${cartao}=    Cartão do grupo    ${nome}
    Wait Until Element Is Visible    xpath:${cartao}
    Element Should Contain    xpath:${cartao}    ${frequencia_formatada}
    Element Should Contain    xpath:${cartao}    ${vagas_texto}

Verificar que o grupo não foi criado
    [Documentation]    Abre /grupos (visão do motorista) e confirma que nenhum cartão tem o nome informado.
    [Arguments]    ${nome}
    Ir para    /grupos
    Wait Until Page Contains    Seus Grupos Criados    timeout=${TIMEOUT}
    ${cartao}=    Cartão do grupo    ${nome}
    Page Should Not Contain Element    xpath:${cartao}

Excluir grupo pelo painel do motorista
    [Documentation]    Clica na lixeira do cartão, aceita o confirm() do navegador e confere a mensagem e o sumiço do grupo.
    [Arguments]    ${nome}
    Ir para    /grupos
    ${cartao}=    Cartão do grupo    ${nome}
    Wait Until Element Is Visible    xpath:${cartao}
    Click Button    xpath:${cartao}//button[@aria-label='Excluir grupo']
    Handle Alert    action=ACCEPT    timeout=${TIMEOUT}
    Wait Until Page Contains    ${MSG_GRUPO_EXCLUIDO}    timeout=${TIMEOUT}
    Page Should Not Contain Element    xpath:${cartao}

Verificar passageiro oferecido na seleção
    [Arguments]    ${email}
    Page Should Contain Element    xpath://label[contains(@class,'passenger-card-label')][.//span[normalize-space()='${email}']]

Verificar passageiro não oferecido na seleção
    [Arguments]    ${email}
    Page Should Not Contain Element    xpath://label[contains(@class,'passenger-card-label')][.//span[normalize-space()='${email}']]

Verificar grupo em grupos inscritos do passageiro
    [Arguments]    ${nome}
    Ir para    /grupos
    ${cartao}=    Cartão do grupo    ${nome}
    Wait Until Element Is Visible    xpath:${XP_SECAO_INSCRITOS}${cartao}

Sair do grupo pelo painel do passageiro
    [Documentation]    Clica em "Sair" no cartão de Grupos Inscritos e aceita o confirm() do navegador.
    [Arguments]    ${nome}
    ${cartao}=    Cartão do grupo    ${nome}
    Click Button    xpath:${XP_SECAO_INSCRITOS}${cartao}//button[normalize-space()='Sair']
    Handle Alert    action=ACCEPT    timeout=${TIMEOUT}
    Wait Until Page Contains    ${MSG_SAIU_DO_GRUPO}    timeout=${TIMEOUT}

Verificar que o passageiro saiu do grupo
    [Documentation]    O grupo some de "Grupos Inscritos" e volta a aparecer em "Grupos Disponíveis" (vínculo removido, vaga liberada).
    [Arguments]    ${nome}
    ${cartao}=    Cartão do grupo    ${nome}
    Page Should Not Contain Element    xpath:${XP_SECAO_INSCRITOS}${cartao}
    Page Should Contain Element    xpath:${XP_SECAO_DISPONIVEIS}${cartao}


# ---------- Cadastro de motorista e triagem ----------

Abrir formulário de cadastro de motorista
    Ir para    /home/motorista
    Wait Until Page Contains    ${TXT_SEJA_MOTORISTA}    timeout=${TIMEOUT}
    Wait Until Element Is Visible    ${SEL_CNH_VISIVEL}

Enviar CNH
    [Documentation]    Digita no input visível (cnh_display) e envia. Ao voltar, a página recarrega com o campo vazio.
    [Arguments]    ${cnh}
    Preencher campo mascarado    cnh_display    ${cnh}
    Click Button    ${SEL_CNH_ENVIAR}

Verificar que o cadastro de motorista foi rejeitado
    [Documentation]    A view não exibe o erro de validação da CNH; a rejeição é confirmada porque a tela continua sendo
    ...                a de cadastro (nenhum perfil criado) e a de "Cadastro em Análise" não aparece, nem após recarregar.
    Wait Until Keyword Succeeds    ${TIMEOUT}    200ms    Textfield Value Should Be    ${SEL_CNH_VISIVEL}    ${EMPTY}
    Verificar URL termina com    /home/motorista
    Page Should Contain    ${TXT_SEJA_MOTORISTA}
    Page Should Not Contain    ${TXT_CADASTRO_EM_ANALISE}
    Ir para    /home/motorista
    Wait Until Page Contains    ${TXT_SEJA_MOTORISTA}    timeout=${TIMEOUT}
    Page Should Not Contain    ${TXT_CADASTRO_EM_ANALISE}

Enviar cadastro de motorista com CNH válida
    [Documentation]    Passageiro logado envia uma CNH válida nova e cai na tela "Cadastro em Análise". Devolve a CNH.
    ${cnh}=    Gerar Cnh Valida
    Abrir formulário de cadastro de motorista
    Enviar CNH    ${cnh}
    Wait Until Page Contains    ${TXT_CADASTRO_EM_ANALISE}    timeout=${TIMEOUT}
    RETURN    ${cnh}

Cartão pendente na triagem
    [Documentation]    Devolve o XPath (sem prefixo) do cartão do motorista pendente com o e-mail informado.
    [Arguments]    ${email}
    ${xpath}=    Replace String    ${XP_CARD_PENDENTE}    EMAIL_PENDENTE    ${email}
    RETURN    ${xpath}

Acessar triagem de motoristas como administrador
    [Documentation]    Encerra a sessão, entra como admin@org.com.br e abre /dashboard/triagem-motorista.
    Encerrar sessão
    Fazer login    ${EMAIL_ADMIN_ORG}    ${SENHA_SEED}
    Ir para    /dashboard/triagem-motorista

Verificar motorista pendente na triagem
    [Arguments]    ${email}    ${cnh}
    ${cartao}=    Cartão pendente na triagem    ${email}
    Wait Until Element Is Visible    xpath:${cartao}
    Element Should Contain    xpath:${cartao}    ${cnh}

Rejeitar motorista na triagem
    [Documentation]    Clica em "Rejeitar" no cartão do motorista e espera o cartão sair da triagem.
    [Arguments]    ${email}
    ${cartao}=    Cartão pendente na triagem    ${email}
    Click Button    xpath:${cartao}//button[normalize-space()='Rejeitar']
    Wait Until Page Does Not Contain Element    xpath:${cartao}    timeout=${TIMEOUT}

Verificar que o perfil de motorista foi removido
    [Documentation]    Entra como o usuário rejeitado: /home/motorista volta a oferecer o cadastro (não há mais perfil pendente).
    [Arguments]    ${email}
    Encerrar sessão
    Fazer login    ${email}    ${SENHA_PADRAO}
    Ir para    /home/motorista
    Wait Until Page Contains    ${TXT_SEJA_MOTORISTA}    timeout=${TIMEOUT}
    Page Should Not Contain    ${TXT_CADASTRO_EM_ANALISE}
