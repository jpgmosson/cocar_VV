*** Settings ***
Documentation     Carteira: depósito, retenção de saldo e liquidação de trajeto (CT01, CT02, CT03, CT04, CT06).
...               Pré-requisito: aplicação rodando com o seed (motorista@org.com.br) e acesso à internet
...               (autocomplete de endereços, cálculo de rota e mapa).
...               Os passos de cada caso espelham os testes de tests/Feature/JoaoPozzan/CarteiraTest.php,
...               adaptados para a interface.
Resource          carteira_resources.robot
Test Setup        Abrir o navegador
Test Teardown     Fechar o navegador

*** Test Cases ***
CT01 - Depósito válido atualiza o saldo e gera transação
    [Documentation]    Usuário com R$ 50,00 deposita R$ 25,50 pela tela da carteira.
    ...                Esperado: volta para /carteira sem erro, saldo de R$ 75.50 e um "Depósito" de R$ 25.50
    ...                com status "sucesso" no histórico (/atividade).
    [Tags]    CT01    carteira
    Criar um usuário com saldo de R$ 50,00
    Depositar R$ 25,50 pela carteira
    Verificar que o depósito voltou para a carteira sem erros
    Verificar que o saldo da carteira foi atualizado para R$ 75,50
    Verificar que a transação de depósito foi gerada com sucesso

CT02 - Depósito com valor inválido é rejeitado
    [Documentation]    Usuário com R$ 50,00 tenta depositar sem valor, valor negativo, com três casas decimais,
    ...                não numérico e zero.
    ...                Esperado: permanece em /carteira, o saldo continua R$ 50.00 e nenhuma transação nova é gerada
    ...                (a view da carteira não exibe a mensagem de erro do campo valor, então ela não é verificada).
    [Tags]    CT02    carteira
    Criar um usuário com saldo de R$ 50,00
    Depositar sem informar o valor
    Verificar que o depósito foi rejeitado e o saldo continua R$ 50,00
    Depositar valor negativo
    Verificar que o depósito foi rejeitado e o saldo continua R$ 50,00
    Depositar valor com mais de duas casas decimais
    Verificar que o depósito foi rejeitado e o saldo continua R$ 50,00
    Depositar valor não numérico
    Verificar que o depósito foi rejeitado e o saldo continua R$ 50,00
    Depositar valor zero
    Verificar que o depósito foi rejeitado e o saldo continua R$ 50,00
    Verificar que nenhuma nova transação foi gerada

CT03 - Retenção com saldo exatamente igual ao valor
    [Documentation]    Passageiro deposita exatamente o valor máximo estimado da corrida e confirma o pedido.
    ...                Esperado: pedido criado (/pedido-carona/{id}), saldo zerado (R$ 0.00) e "Reserva de Saldo"
    ...                do valor máximo com status "sucesso" em /atividade.
    ...                Ao final o pedido é cancelado para não aparecer como sugestão nos testes de outras pessoas.
    [Tags]    CT03    carteira    retencao
    Criar um passageiro sem saldo
    Consultar o valor máximo estimado da corrida
    Depositar exatamente o valor máximo estimado
    Solicitar a carona no mesmo trajeto
    Verificar que o saldo da carteira foi zerado
    Verificar que a retenção foi registrada com sucesso
    Cancelar o pedido criado para não interferir nos outros testes

CT04 - Retenção com saldo insuficiente
    [Documentation]    Passageiro deposita um centavo a menos que o valor máximo estimado e tenta pedir a carona.
    ...                Esperado: a estimativa mostra "Saldo Insuficiente" sem o botão de confirmar, o saldo não muda
    ...                e nenhuma "Reserva de Saldo" é registrada. Na interface o pedido é barrado antes da retenção
    ...                (PedidoCaronaService::novoPedido), então a transação "falhou" do PHPUnit não chega a ser gravada.
    [Tags]    CT04    carteira    retencao
    Criar um passageiro sem saldo
    Consultar o valor máximo estimado da corrida
    Depositar um centavo a menos que o valor máximo estimado
    Tentar solicitar a carona no mesmo trajeto
    Verificar que o pedido foi bloqueado por saldo insuficiente
    Verificar que o saldo da carteira não foi alterado
    Verificar que nenhuma retenção foi registrada

CT06 - Liquidação ao finalizar trajeto sem caronas
    [Documentation]    Motorista do seed publica um trajeto, inicia e finaliza sem aceitar nenhuma carona.
    ...                Esperado: trajeto concluído (o atalho de viagem ativa some), nenhuma transação nova
    ...                (sem liquidação nem ajuda de custo) e saldo do motorista inalterado.
    [Tags]    CT06    carteira    trajeto
    Autenticar o motorista do seed
    Guardar o saldo e o histórico atuais do motorista
    Publicar um trajeto sem aceitar caronas
    Iniciar o trajeto
    Verificar que o trajeto está em andamento
    Finalizar o trajeto
    Verificar que o trajeto foi concluído
    Verificar que nenhuma transação foi gerada para o motorista
    Verificar que o saldo do motorista não foi alterado
