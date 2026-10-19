#!/usr/bin/env bash
set -euo pipefail
export PYTHONIOENCODING='utf-8'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RAIZ="$(cd "${SCRIPT_DIR}/../.." && pwd)"
cd "$RAIZ"

PESSOA=""
CASO=""
DRY_RUN=false

while [[ $# -gt 0 ]]; do
    case "$1" in
    -p | --pessoa)
        PESSOA="$2"
        shift 2
        ;;
    -c | --caso)
        CASO="$2"
        shift 2
        ;;
    -d | --dry-run)
        DRY_RUN=true
        shift
        ;;
    *)
        if [[ -z "$PESSOA" ]]; then
            PESSOA="$1"
        elif [[ -z "$CASO" ]]; then
            CASO="$1"
        fi
        shift
        ;;
    esac
done

VALID_PESSOAS=("Eduardo" "JoaoMosson" "JoaoPozzan" "Renan" "Victor")
if [[ -z "$PESSOA" ]] || ! [[ " ${VALID_PESSOAS[*]} " =~ [[:space:]]${PESSOA}[[:space:]] ]]; then
    echo "Erro: Pessoa é obrigatória e deve ser uma de: ${VALID_PESSOAS[*]}" >&2
    exit 1
fi

# 1. Start database
docker compose up -d >/dev/null 2>&1

cleanup() {
    [[ -n "${SERVER_PID:-}" ]] && kill "$SERVER_PID" 2>/dev/null || true
    [[ -n "${VITE_PID:-}" ]] && kill "$VITE_PID" 2>/dev/null || true
    docker compose down >/dev/null 2>&1 || true
}
trap cleanup EXIT

# 2. Wait for Postgres on port 5432
until (echo >/dev/tcp/127.0.0.1/5432) 2>/dev/null; do
    sleep 0.5
done

# 3. Seed database
php artisan migrate:fresh --seed >/dev/null 2>&1

# 4. Start HTTP server and Vite without TTY allocation (completely silenced)
nohup php artisan serve </dev/null >/dev/null 2>&1 &
SERVER_PID=$!

nohup npx vite --logLevel silent </dev/null >/dev/null 2>&1 &
VITE_PID=$!

# Wait for HTTP server readiness
until (echo >/dev/tcp/127.0.0.1/8000) 2>/dev/null; do
    sleep 0.3
done

# 5. Build and execute Robot Framework
PASTA_TESTES="tests/Robot/${PESSOA}"
SAIDA="tests/Robot/results/${PESSOA}"
ARGS=()

if [[ -f "tests/Robot/local.args" ]]; then
    ARGS+=("--argumentfile" "tests/Robot/local.args")
fi

ARGS+=("--outputdir" "$SAIDA")
[[ -n "$CASO" ]] && ARGS+=("--include" "$CASO")
[[ "$DRY_RUN" == true ]] && ARGS+=("--dryrun")
ARGS+=("$PASTA_TESTES")

set +e
robot "${ARGS[@]}"
CODIGO=$?
set -e

echo "Log: ${RAIZ}/${SAIDA}/log.html"
exit $CODIGO
