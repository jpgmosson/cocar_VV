*** Settings ***
Documentation     Autenticação, usuários e gestão organizacional (CT09, CT10, CT12, CT14, CT15).
...               Pré-requisito: aplicação rodando com o seed (admin@org.com.br, passageiro@org.com.br).
...               Os passos de cada caso espelham a planilha "Cenários e Casos de Teste", adaptados para a interface.
Resource          autenticacao_resources.robot
Test Setup        Abrir o navegador
Test Teardown     Fechar o navegador

*** Test Cases ***
CT09 - Bloqueio de registro com domínio de e-mail não cadastrado
    [Documentation]    Registro com e-mail de um domínio que nenhuma organização possui.
    ...                Esperado: erro no campo e-mail com a mensagem de domínio não cadastrado e nenhum usuário criado
    ...                (na interface: permanece em /register, sem sessão).
    [Tags]    CT09    autenticacao
    Montar payload de registro válido com e-mail de domínio não cadastrado
    Enviar o formulário de registro
    Verificar que o erro de validação foi lançado no campo e-mail
    Verificar que nenhum usuário foi criado com esse e-mail

CT10 - Criação completa de usuário com associação à organização e carteira
    [Documentation]    Registro com e-mail do domínio da organização seed.
    ...                Esperado: usuário associado à organização, tipo padrão (passageiro) e carteira criada
    ...                (/carteira abre normalmente).
    [Tags]    CT10    autenticacao
    Usar a organização cadastrada com o domínio do seed
    Montar payload de registro válido com e-mail do domínio da organização
    Enviar o formulário de registro
    Verificar que a carteira foi criada para o usuário
    Verificar que o usuário foi associado à organização correspondente
    Verificar que o tipo do usuário é padrão

CT12 - Bloqueio e redirecionamento por papel de usuário
    [Documentation]    a) Passageiro tentando abrir /dashboard/usuarios volta para /home.
    ...                b) Admin da organização abrindo /home vai para /dashboard.
    ...                Valida somente a URL final: a mensagem "Acesso negado" é enviada em session('error'),
    ...                mas nenhuma view a exibe.
    [Tags]    CT12    autenticacao
    Usar a rota protegida por VerificarTipo para administradores
    Autenticar usuário com tipo padrão
    Fazer GET na rota restrita
    Verificar que a requisição foi interceptada antes do controller
    Verificar redirecionamento para a rota home
    Autenticar usuário administrador da organização
    Fazer GET na rota home do passageiro
    Verificar redirecionamento para o painel do administrador

CT14 - Exclusão de usuário e encerramento de sessão
    [Documentation]    Usuário descartável exclui a própria conta pelo perfil (sem confirmação JS).
    ...                Esperado: sessão encerrada (termina na raiz, /perfil redireciona para /login) e usuário
    ...                removido (o login com as mesmas credenciais falha com a mensagem de credenciais inválidas).
    [Tags]    CT14    autenticacao
    Criar um usuário e autenticá-lo
    Excluir o usuário pelo perfil
    Verificar que a sessão do usuário foi encerrada
    Verificar que o usuário foi removido da base de dados

CT15 - Validação de CPF
    [Documentation]    Registro com CPFs inválidos (10 dígitos, dígitos repetidos e dígito verificador errado).
    ...                Esperado: permanece em /register com erro sob o CPF. Em seguida, um CPF válido leva a /home.
    [Tags]    CT15    autenticacao
    Abrir o formulário de registro que aplica a regra Cpf
    Validar CPF inválido por tamanho
    Verificar que o CPF inválido foi rejeitado com mensagem de erro
    Validar CPFs com todos os dígitos iguais
    Verificar que o CPF inválido foi rejeitado com mensagem de erro
    Validar CPF com dígito verificador adulterado
    Verificar que o CPF inválido foi rejeitado com mensagem de erro
    Validar CPF correto
    Verificar que o CPF válido foi aceito sem erro
