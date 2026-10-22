*** Settings ***
Documentation     Grupos de carona e cadastro/triagem de motoristas: CT40, CT41, CT43, CT44 e CT46 da planilha do RA2.
...               Adaptação para a interface dos testes de tests/Feature/Renan.
...               Pré-requisito: aplicação rodando com o seed (admin@org.com.br e motorista@org.com.br aprovado).
...               Os dados de cada caso (passageiros, organização, CNH, nome do grupo) são gerados na hora.
Resource          grupos_resources.robot
Test Setup        Abrir o navegador
Test Teardown     Fechar o navegador


*** Test Cases ***
CT40 - Criação de grupo com validação condicional e limite de vagas
    [Documentation]    Motorista aprovado tenta criar um grupo semanal com 5 vagas e depois um grupo semanal com 4 vagas.
    ...                Esperado: a 1ª tentativa é rejeitada com o erro do campo vagas (limite de 4) e nenhum grupo é criado;
    ...                a 2ª redireciona para /home/motorista com a mensagem de sucesso e o grupo aparece como "Semanal (Seg, Qui)" com 4 vagas.
    ...                Na 2ª tentativa os dias do mês 10 e 20 são marcados com a frequência mensal e a frequência volta para semanal,
    ...                então o formulário envia dias_mes indevidamente. A interface só mostra os dias da semana;
    ...                que dias_mes foi gravado como null permanece coberto pelo teste Pest (assertDatabaseHas).
    ...                A 1ª tentativa desliga a validação nativa do navegador (max=4) para que a regra do servidor seja exercitada.
    [Tags]    CT40    grupos    criacao
    [Teardown]    Limpar grupo e fechar o navegador
    Acessar como motorista do seed
    ${nome}=    Gerar nome único de grupo
    Abrir formulário de criação de grupo
    Preencher dados do grupo    ${nome}    semanal    5
    Marcar dia da semana    seg
    Marcar dia da semana    qua
    Desativar validação nativa do formulário
    Salvar grupo
    Verificar erro de validação do grupo    ${MSG_VAGAS_MAXIMO}
    Verificar que o grupo não foi criado    ${nome}
    Abrir formulário de criação de grupo
    Preencher dados do grupo    ${nome}    mensal    4
    Marcar dia do mês    10
    Marcar dia do mês    20
    Selecionar frequência do grupo    semanal
    Marcar dia da semana    seg
    Marcar dia da semana    qui
    Salvar grupo
    Verificar grupo criado com sucesso
    Verificar grupo na lista do motorista    ${nome}    Semanal (Seg, Qui)    0 / 4 Vagas

CT41 - Bloqueio de passageiros acima das vagas ou de outra organização
    [Documentation]    Dois passageiros da organização do motorista e um passageiro de outra organização existem.
    ...                a) Na seleção de passageiros do formulário aparecem os dois da mesma organização e não aparece o da outra
    ...                   (pela interface não há como enviar um passageiro de outra organização; o erro passageiros.0 da requisição
    ...                   forjada continua coberto pelo teste Pest).
    ...                b) Com 1 vaga e os 2 passageiros marcados, o envio é rejeitado com o erro do campo passageiros
    ...                   e nenhum grupo é criado.
    [Tags]    CT41    grupos    criacao    multitenant
    ${email_1}    ${nome_1}=    Registrar passageiro descartável
    Encerrar sessão
    ${email_2}    ${nome_2}=    Registrar passageiro descartável
    Encerrar sessão
    ${dominio_b}=    Criar organização descartável
    Encerrar sessão
    ${email_b}    ${nome_b}=    Registrar passageiro descartável    ${dominio_b}
    Acessar como motorista do seed
    Abrir formulário de criação de grupo
    @{emails_da_organizacao}=    Create List    ${email_1}    ${email_2}
    FOR    ${email}    IN    @{emails_da_organizacao}
        Verificar passageiro oferecido na seleção    ${email}
    END
    Verificar passageiro não oferecido na seleção    ${email_b}
    ${nome}=    Gerar nome único de grupo
    Preencher dados do grupo    ${nome}    semanal    1
    Marcar dia da semana    seg
    FOR    ${email}    IN    @{emails_da_organizacao}
        Selecionar passageiro na lista    ${email}
    END
    Salvar grupo
    Verificar erro de validação do grupo    ${MSG_PASSAGEIROS_EXCEDEM}
    Verificar que o grupo não foi criado    ${nome}

CT43 - Saída de passageiro e exclusão de grupo restrita ao motorista dono
    [Documentation]    O motorista dono cria um grupo com um passageiro; o passageiro sai do grupo; o motorista dono exclui o grupo.
    ...                Esperado: o passageiro vê o grupo em "Grupos Inscritos", sai com a mensagem de sucesso e o grupo deixa de
    ...                constar nessa lista (volta a ficar disponível); o dono exclui com a mensagem de sucesso e o grupo some.
    ...                A tentativa de exclusão por outro motorista não é feita pela interface, pois /grupos só lista os grupos do
    ...                próprio motorista (o bloqueio "Acesso não autorizado" continua coberto pelo teste Pest).
    [Tags]    CT43    grupos    saida    exclusao
    [Teardown]    Limpar grupo e fechar o navegador
    ${email}    ${nome_passageiro}=    Registrar passageiro descartável
    Acessar como motorista do seed
    ${nome}=    Gerar nome único de grupo
    Criar grupo semanal com passageiro    ${nome}    2    ${email}
    Encerrar sessão
    Fazer login    ${email}    ${SENHA_PADRAO}
    Verificar grupo em grupos inscritos do passageiro    ${nome}
    Sair do grupo pelo painel do passageiro    ${nome}
    Verificar que o passageiro saiu do grupo    ${nome}
    Acessar como motorista do seed
    Excluir grupo pelo painel do motorista    ${nome}

CT44 - Bloqueio de cadastro de motorista com CNH inválida
    [Documentation]    Passageiro sem perfil de motorista envia a CNH 11111111111 (dígitos repetidos).
    ...                Esperado: o cadastro é recusado e nenhum perfil de motorista é criado (a tela continua sendo a de cadastro,
    ...                sem "Cadastro em Análise", mesmo ao recarregar). A view não exibe a mensagem de erro do campo cnh,
    ...                então ela não é verificada.
    [Tags]    CT44    motorista    cnh
    Registrar passageiro descartável
    Abrir formulário de cadastro de motorista
    Enviar CNH    ${CNH_INVALIDA}
    Verificar que o cadastro de motorista foi rejeitado

CT46 - Rejeição de motorista remove o perfil
    [Documentation]    Passageiro envia uma CNH válida e fica em análise; o administrador da organização rejeita o cadastro na triagem.
    ...                Esperado: o cartão sai da triagem e o perfil deixa de existir (o usuário volta a ver o formulário de cadastro
    ...                de motorista em /home/motorista, e não a tela de "Cadastro em Análise").
    [Tags]    CT46    motorista    triagem
    ${email}    ${nome}=    Registrar passageiro descartável
    ${cnh}=    Enviar cadastro de motorista com CNH válida
    Acessar triagem de motoristas como administrador
    Verificar motorista pendente na triagem    ${email}    ${cnh}
    Rejeitar motorista na triagem    ${email}
    Verificar que o perfil de motorista foi removido    ${email}
