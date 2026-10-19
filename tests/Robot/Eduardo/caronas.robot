*** Settings ***
Documentation     Testes do domínio de caronas
...               CT18, CT19, CT20, CT21 e CT23.
Resource          ./caronas_resources.robot
Test Setup        Abrir o navegador
Test Teardown     Fechar o navegador


*** Test Cases ***
CT20 - Lança exceção e não grava registros quando saldo for insuficiente
    [Documentation]    Passageiro sem saldo suficiente tenta solicitar uma carona.
    ...                Esperado: a interface exibe aviso de "Saldo Insuficiente", oculta o botão de confirmação
    ...                e nenhuma retenção ou transação é gravada em /atividade.
    [Tags]    CT20    carona    carteira
    Registrar passageiro descartável
    Preencher origem e destino como passageiro
    Verificar aviso de saldo insuficiente na estimativa
    Verificar histórico de atividade não possui transações

CT21 - Retém saldo máximo com sucesso quando o saldo for suficiente
    [Documentation]    Passageiro deposita R$ 50,00 na carteira e solicita carona em rota válida.
    ...                Esperado: pedido é criado com redirecionamento para /pedido-carona/{id},
    ...                o valor máximo estimado é retido do saldo e a transação "Reserva de Saldo" consta em /atividade.
    [Tags]    CT21    carona    carteira
    Registrar passageiro descartável
    Adicionar saldo na carteira    50
    Verificar saldo na carteira igual a    50.00
    Preencher origem e destino como passageiro
    ${url_pedido}=    Confirmar pedido de carona
    Verificar status do pedido na tela do passageiro    ${url_pedido}    Procurando motorista...
    Verificar transação registrada no histórico    Reserva de Saldo
    # Cancela ao final para não deixar pedido órfão interferindo nos próximos testes
    Verificar status do pedido na tela do passageiro    ${url_pedido}    Procurando motorista...
    Cancelar pedido atual pelo painel do passageiro

CT18 - Cancelamento de pedido atualiza status e estorna retenção de saldo
    [Documentation]    Passageiro com saldo deposita R$ 50,00, cria um pedido (gerando retenção) e em seguida o cancela.
    ...                Esperado: o saldo da carteira retorna integralmente para R$ 50.00 e o histórico
    ...                em /atividade registra a transação de "Estorno".
    [Tags]    CT18    carona    cancelamento
    Registrar passageiro descartável
    Adicionar saldo na carteira    50
    Preencher origem e destino como passageiro
    ${url_pedido}=    Confirmar pedido de carona
    Verificar status do pedido na tela do passageiro    ${url_pedido}    Procurando motorista...
    Cancelar pedido atual pelo painel do passageiro
    Verificar saldo na carteira igual a    50.00
    Verificar transação registrada no histórico    Estorno

CT19 - Cancelar trajeto reverte pedidos ativos e preserva histórico de falha
    [Documentation]    Passageiro solicita carona, motorista publica trajeto compatível, aceita o passageiro
    ...                (mudando o status para "aceita") e em seguida cancela o trajeto.
    ...                Esperado: o pedido do passageiro ativo reverte automaticamente para "Procurando motorista...".
    [Tags]    CT19    trajeto    cancelamento
    ${email_p1}    ${nome_p1}=    Registrar passageiro descartável
    Adicionar saldo na carteira    50
    Preencher origem e destino como passageiro
    ${url_pedido_p1}=    Confirmar pedido de carona
    Encerrar sessão
    Fazer login    ${EMAIL_MOTORISTA}    ${SENHA_SEED}
    ${url_trajeto}=    Publicar trajeto como motorista
    Aguardar carregamento de sugestões no trajeto
    Verificar passageiro presente nas sugestões do trajeto    ${nome_p1}
    Aceitar passageiro no trajeto    ${nome_p1}
    Encerrar sessão
    Fazer login    ${email_p1}    ${SENHA_PADRAO}
    Verificar status do pedido na tela do passageiro    ${url_pedido_p1}    Carona aceita
    Encerrar sessão
    Fazer login    ${EMAIL_MOTORISTA}    ${SENHA_SEED}
    Go To    ${url_trajeto}
    Cancelar trajeto atual pelo painel do motorista
    Encerrar sessão
    Fazer login    ${email_p1}    ${SENHA_PADRAO}
    Verificar status do pedido na tela do passageiro    ${url_pedido_p1}    Procurando motorista...
    Cancelar pedido atual pelo painel do passageiro

CT23 - Filtra estritamente por distância máxima, mesma organização e ausência de vínculo prévio
    [Documentation]    Dois passageiros criam pedidos: um próximo da rota do motorista e outro fora do raio (> 5km).
    ...                Esperado: apenas o passageiro próximo aparece nas sugestões do trajeto; após ser aceito
    ...                (vinculado ao trajeto), ele deixa de aparecer como sugestão pendente.
    [Tags]    CT23    trajeto    sugestoes
    ${email_perto}    ${nome_perto}=    Registrar passageiro descartável
    Adicionar saldo na carteira    100
    Preencher origem e destino como passageiro    ${ENDERECO_ORIGEM_PERTO}    ${ENDERECO_DESTINO_PERTO}
    ${url_pedido_perto}=    Confirmar pedido de carona
    Encerrar sessão
    ${email_longe}    ${nome_longe}=    Registrar passageiro descartável
    Adicionar saldo na carteira    100
    Preencher origem e destino como passageiro    ${ENDERECO_ORIGEM_LONGE}    ${ENDERECO_DESTINO_PERTO}
    ${url_pedido_longe}=    Confirmar pedido de carona
    Encerrar sessão
    Fazer login    ${EMAIL_MOTORISTA}    ${SENHA_SEED}
    ${url_trajeto}=    Publicar trajeto como motorista    ${ENDERECO_ORIGEM_PERTO}    ${ENDERECO_DESTINO_PERTO}
    Aguardar carregamento de sugestões no trajeto
    Verificar passageiro presente nas sugestões do trajeto    ${nome_perto}
    Verificar passageiro ausente nas sugestões do trajeto    ${nome_longe}
    Aceitar passageiro no trajeto    ${nome_perto}
    Go To    ${url_trajeto}
    Verificar passageiro ausente nas sugestões do trajeto    ${nome_perto}
    Cancelar trajeto atual pelo painel do motorista
    Encerrar sessão
    Fazer login    ${email_perto}    ${SENHA_PADRAO}
    Go To    ${url_pedido_perto}
    Cancelar pedido atual pelo painel do passageiro
    Encerrar sessão
    Fazer login    ${email_longe}    ${SENHA_PADRAO}
    Go To    ${url_pedido_longe}
    Cancelar pedido atual pelo painel do passageiro
