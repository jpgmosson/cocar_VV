<#
.SYNOPSIS
    Roda os testes Robot de uma pessoa, com resultados em tests/Robot/results/<Pessoa>/.
.EXAMPLE
    .\tests\Robot\rodar.ps1 -Pessoa JoaoMosson
    .\tests\Robot\rodar.ps1 -Pessoa JoaoMosson -Caso CT09
#>
param(
    [Parameter(Mandatory = $true)]
    [ValidateSet('Eduardo', 'JoaoMosson', 'JoaoPozzan', 'Renan', 'Victor')]
    [string]$Pessoa,

    [string]$Caso,

    # Apenas valida os testes (robot --dryrun), sem abrir navegador.
    [switch]$DryRun
)

$ErrorActionPreference = 'Stop'
$env:PYTHONIOENCODING = 'utf-8'

$raiz = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
Set-Location $raiz

$pastaTestes = "tests/Robot/$Pessoa"
$saida = "tests/Robot/results/$Pessoa"
$argumentos = @()

if (Test-Path 'tests/Robot/local.args') {
    $argumentos += @('--argumentfile', 'tests/Robot/local.args')
}
# Depois do argumentfile, para prevalecer.
$argumentos += @('--outputdir', $saida)
if ($Caso) { $argumentos += @('--include', $Caso) }
if ($DryRun) { $argumentos += '--dryrun' }
$argumentos += $pastaTestes

Write-Host "robot $($argumentos -join ' ')"
& robot @argumentos
$codigo = $LASTEXITCODE

Write-Host "Log: $(Join-Path $raiz "$saida/log.html")"
exit $codigo
