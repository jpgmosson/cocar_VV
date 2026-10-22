"""Gerador de CNH válida para os testes Robot do Renan (CT44 e CT46).

O ``resources/GeradorDados.py`` é compartilhado e editado por uma pessoa só, por isso
o gerador de CNH fica aqui. A regra espelha ``app/Rules/Cnh.php``:
11 dígitos, não todos iguais, e dois dígitos verificadores.
A coluna ``perfis_motorista.cnh`` é ``unique``, então sempre gere uma CNH nova.

A função pública vira a keyword ``Gerar Cnh Valida``.
"""
import random


def _digitos_verificadores(base):
    """Calcula (dv1, dv2) para os 9 primeiros dígitos, como em Cnh.php. None se não existir."""
    dsc = sum(base[i] * (9 - i) for i in range(9))
    dv1 = dsc % 11
    incremento = 0
    if dv1 > 9:
        dv1 = 0
        incremento = 2

    dsc = sum(base[i] * (i + 1) for i in range(9))
    resto = dsc % 11
    dv2 = 0 if resto >= 9 else resto - incremento
    if dv2 < 0:
        return None
    return dv1, dv2


def gerar_cnh_valida():
    """Devolve uma CNH válida (11 dígitos, sem pontuação)."""
    while True:
        base = [random.randint(0, 9) for _ in range(9)]
        dvs = _digitos_verificadores(base)
        if dvs is None:
            continue
        cnh = base + list(dvs)
        if len(set(cnh)) > 1:
            return "".join(str(d) for d in cnh)


def _cnh_ok(cnh):
    """Conferência local (mesma lógica de Cnh.php), usada apenas na autoconferência."""
    if len(cnh) != 11 or len(set(cnh)) == 1:
        return False
    d = [int(c) for c in cnh]
    dvs = _digitos_verificadores(d[:9])
    return dvs is not None and d[9] == dvs[0] and d[10] == dvs[1]


if __name__ == "__main__":
    print("cnh valida :", gerar_cnh_valida())
    # CNH do seed (fluxoTesterSeeder) e CNH inválida usada no CT44
    assert _cnh_ok("92189958447")
    assert not _cnh_ok("11111111111")
    assert all(_cnh_ok(gerar_cnh_valida()) for _ in range(5000))
    print("autoconferencia OK")
