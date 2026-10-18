"""Gerador de dados únicos e válidos/inválidos para os testes Robot do CoCar.

Todas as funções públicas viram keywords (ex.: ``Gerar Cpf Valido``).
Todo campo relevante tem ``unique`` no banco, então sempre gere dados novos.
"""
import random
import string
import time


def _digito_cpf(digitos):
    peso = len(digitos) + 1
    soma = sum(d * (peso - i) for i, d in enumerate(digitos))
    return ((10 * soma) % 11) % 10


def _digito_cnpj(digitos):
    if len(digitos) == 12:
        pesos = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
    else:
        pesos = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
    soma = sum(d * p for d, p in zip(digitos, pesos))
    resto = soma % 11
    return 0 if resto < 2 else 11 - resto


def _base(tamanho):
    while True:
        digitos = [random.randint(0, 9) for _ in range(tamanho)]
        if len(set(digitos)) > 1:
            return digitos


def _texto(digitos):
    return "".join(str(d) for d in digitos)


def gerar_cpf_valido():
    """Devolve um CPF válido (11 dígitos, sem pontuação)."""
    while True:
        d = _base(9)
        d.append(_digito_cpf(d))
        d.append(_digito_cpf(d))
        if len(set(d)) > 1:
            return _texto(d)


def gerar_cnpj_valido():
    """Devolve um CNPJ válido (14 dígitos, sem pontuação)."""
    while True:
        d = _base(12)
        d.append(_digito_cnpj(d))
        d.append(_digito_cnpj(d))
        if len(set(d)) > 1:
            return _texto(d)


def gerar_cpf_invalido(tipo="digito_errado"):
    """CPF inválido. tipo: digito_errado, repetido ou tamanho."""
    if tipo == "repetido":
        return str(random.randint(0, 9)) * 11
    if tipo == "tamanho":
        return gerar_cpf_valido()[:10]
    if tipo == "digito_errado":
        cpf = gerar_cpf_valido()
        ultimo = (int(cpf[-1]) + random.randint(1, 9)) % 10
        return cpf[:-1] + str(ultimo)
    raise ValueError("tipo deve ser digito_errado, repetido ou tamanho")


def gerar_cnpj_invalido(tipo="digito_errado"):
    """CNPJ inválido. tipo: digito_errado, repetido ou tamanho."""
    if tipo == "repetido":
        return str(random.randint(0, 9)) * 14
    if tipo == "tamanho":
        return gerar_cnpj_valido()[:13]
    if tipo == "digito_errado":
        cnpj = gerar_cnpj_valido()
        ultimo = (int(cnpj[-1]) + random.randint(1, 9)) % 10
        return cnpj[:-1] + str(ultimo)
    raise ValueError("tipo deve ser digito_errado, repetido ou tamanho")


def _sufixo():
    return "".join(random.choices(string.ascii_lowercase + string.digits, k=4))


def gerar_email_unico(dominio):
    """Devolve robot.<timestamp><sufixo>@dominio em minúsculas."""
    return f"robot.{int(time.time() * 1000)}{_sufixo()}@{dominio}".lower()


def gerar_dominio_unico():
    """Devolve robot<timestamp><sufixo>.com.br."""
    return f"robot{int(time.time() * 1000)}{_sufixo()}.com.br".lower()


def _cpf_ok(cpf):
    if len(cpf) != 11 or len(set(cpf)) == 1:
        return False
    d = [int(c) for c in cpf]
    return d[9] == _digito_cpf(d[:9]) and d[10] == _digito_cpf(d[:10])


def _cnpj_ok(cnpj):
    if len(cnpj) != 14 or len(set(cnpj)) == 1:
        return False
    d = [int(c) for c in cnpj]
    return d[12] == _digito_cnpj(d[:12]) and d[13] == _digito_cnpj(d[:13])


if __name__ == "__main__":
    print("cpf valido        :", gerar_cpf_valido())
    print("cnpj valido       :", gerar_cnpj_valido())
    for t in ("digito_errado", "repetido", "tamanho"):
        print(f"cpf invalido {t:14}:", gerar_cpf_invalido(t))
        print(f"cnpj invalido {t:13}:", gerar_cnpj_invalido(t))
    print("email unico       :", gerar_email_unico("org.com.br"))
    print("dominio unico     :", gerar_dominio_unico())
    # autoconferência
    assert _cpf_ok("52998224725") and _cnpj_ok("11222333000181")
    assert all(_cpf_ok(gerar_cpf_valido()) for _ in range(2000))
    assert all(_cnpj_ok(gerar_cnpj_valido()) for _ in range(2000))
    assert not any(_cpf_ok(gerar_cpf_invalido(t)) for t in ("digito_errado", "repetido", "tamanho") for _ in range(500))
    assert not any(_cnpj_ok(gerar_cnpj_invalido(t)) for t in ("digito_errado", "repetido", "tamanho") for _ in range(500))
    print("autoconferencia OK")
