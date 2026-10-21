*** Settings ***
Documentation     Trajetos e viagem ativa: CT30, CT32, CT34, CT36 e CT38 da planilha do RA2.
...               Adaptação para a interface dos testes de tests/Unit/Victor e tests/Feature/Victor.
...               Pré-requisitos: aplicação com seed, motorista aprovado e serviços de endereço, rota e mapa disponíveis.
Resource          trajetos_resources.robot
Test Setup        Preparar teste de trajetos
Test Teardown     Encerrar teste de trajetos


*** Test Cases ***
CT30 - Iniciar trajeto planejado e atualizar carona aceita
    [Documentation]    Motorista aceita o passageiro e inicia um trajeto planejado.
    ...                Esperado: indicador do motorista em "Em Andamento" e passageiro vendo "Motorista a caminho".
    ...                Horário, coordenadas e preservação de caronas canceladas permanecem cobertos no backend.
    [Tags]    CT30    trajeto    inicio
    Preparar trajeto com passageiro aceito
    Iniciar trajeto de teste
    Verificar indicador do trajeto    Em Andamento
    Capture Page Screenshot    ct30-trajeto-em-andamento.png
    Acessar como passageiro de teste
    Verificar status do passageiro    Motorista a caminho
    Capture Page Screenshot    ct30-motorista-a-caminho.png

CT32 - Confirmar embarque do passageiro
    [Documentation]    Em um trajeto iniciado, o motorista abre a parada do passageiro no mapa e confirma o embarque.
    ...                Esperado: a tela muda de "Motorista a caminho" para "A caminho do destino"
    ...                e o indicador do pedido passa a "Em Andamento". Não consulta horários ou localização no banco.
    [Tags]    CT32    trajeto    embarque
    Preparar trajeto com passageiro aceito
    Iniciar trajeto de teste
    Acessar como passageiro de teste
    Verificar status do passageiro    Motorista a caminho
    Acessar como motorista de teste
    Embarcar passageiro de teste
    Acessar como passageiro de teste
    Verificar status do passageiro    A caminho do destino
    Capture Page Screenshot    ct32-passageiro-embarcado.png
    Verificar indicador do pedido    Em Andamento
    Capture Page Screenshot    ct32-indicador-em-andamento.png

CT34 - Cancelar carona e liberar o pedido do passageiro
    [Documentation]    Motorista cancela individualmente a carona aceita pelo popup da parada no mapa.
    ...                Esperado: o trajeto continua planejado e o passageiro volta a "Procurando motorista...".
    ...                A geometria da rota e a chamada ao serviço de mapas não são verificadas pela interface.
    [Tags]    CT34    trajeto    cancelamento
    Preparar trajeto com passageiro aceito
    Cancelar carona do passageiro de teste
    Verificar indicador do trajeto    Planejado
    Capture Page Screenshot    ct34-trajeto-preservado.png
    Acessar como passageiro de teste
    Verificar status do passageiro    Procurando motorista...
    Page Should Not Contain Element    css:.bottom-panel__profile
    Capture Page Screenshot    ct34-pedido-liberado.png
    Verificar indicador do pedido    Procurando Motorista

CT36 - Aceitar pedido disponível e criar vínculo de carona
    [Documentation]    Motorista publica trajeto compatível e aceita o pedido do passageiro identificado pelo nome e ID.
    ...                Esperado: passageiro vê "Carona aceita", nome do motorista e indicador do pedido em "Aceita".
    [Tags]    CT36    trajeto    aceite
    Registrar passageiro de teste
    Criar pedido do passageiro de teste
    Acessar como motorista de teste
    Publicar trajeto de teste
    Aceitar passageiro de teste
    Capture Page Screenshot    ct36-pedido-aceito-motorista.png
    Acessar como passageiro de teste
    Verificar status do passageiro    Carona aceita
    Element Text Should Be    css:.bottom-panel__name    ${NOME_MOTORISTA}
    Element Text Should Be    css:.bottom-panel__subtitle    Aguardando motorista iniciar trajeto
    Capture Page Screenshot    ct36-carona-aceita-passageiro.png
    Verificar indicador do pedido    Aceita

CT38 - Exibir indicador de viagem ativa conforme o estado do usuário
    [Documentation]    Verifica ausência de indicador para usuário novo, pedido procurando motorista,
    ...                trajeto planejado, carona em andamento e ausência após a conclusão da viagem.
    ...                Confere também que os atalhos abrem o pedido e o trajeto criados neste caso.
    [Tags]    CT38    trajeto    viagem_ativa
    Registrar passageiro de teste
    Verificar ausência de indicador de viagem ativa
    Capture Page Screenshot    ct38-usuario-sem-viagem.png
    Criar pedido do passageiro de teste
    Verificar indicador do pedido    Procurando Motorista
    Abrir pedido pelo indicador de viagem ativa
    Verificar status do passageiro    Procurando motorista...
    Capture Page Screenshot    ct38-pedido-procurando-motorista.png
    Acessar como motorista de teste
    Publicar trajeto de teste
    Verificar indicador do trajeto    Planejado
    Capture Page Screenshot    ct38-trajeto-planejado.png
    Abrir trajeto pelo indicador de viagem ativa
    Aceitar passageiro de teste
    Iniciar trajeto de teste
    Embarcar passageiro de teste
    Acessar como passageiro de teste
    Verificar status do passageiro    A caminho do destino
    Verificar indicador do pedido    Em Andamento
    Capture Page Screenshot    ct38-carona-em-andamento.png
    Acessar como motorista de teste
    Finalizar trajeto de teste
    Verificar ausência do indicador do trajeto concluído
    Capture Page Screenshot    ct38-motorista-apos-conclusao.png
    Acessar como passageiro de teste
    Verificar ausência de indicador de viagem ativa
    Capture Page Screenshot    ct38-passageiro-apos-conclusao.png
