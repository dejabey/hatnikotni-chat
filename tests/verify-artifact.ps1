<#
.SYNOPSIS
  Verify a downloaded Hatnikotni Chat CI artifact and extract the installable ZIP.

.DESCRIPTION
  1. Hashes the outer artifact ZIP (the file GitHub Actions downloads) and compares
     it with the digest recorded in PROJECT-HANDOFF.md.
  2. Extracts the inner ZIP (the one to upload in WordPress) next to the outer file.
  3. Prints the inner ZIP's name, size and SHA-256 so it can be recorded.

  Read-only apart from creating the extraction folder. Uses only built-in
  PowerShell commands (Windows PowerShell 5.1 or PowerShell 7).

.EXAMPLE
  powershell -ExecutionPolicy Bypass -File .\verify-artifact.ps1 `
    -Zip "$HOME\Downloads\hatnikotni-chat-0.1.29.zip" `
    -Expected f85ad689b589c51ddf055de400e189faade95689b5c0724b44c74e1fab1989a7
#>
param(
	[Parameter(Mandatory = $true)][string]$Zip,
	[Parameter(Mandatory = $true)][string]$Expected
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path -LiteralPath $Zip)) {
	Write-Host "FAIL: file not found: $Zip" -ForegroundColor Red
	exit 1
}

$expectedHash = ($Expected -replace '^sha256:', '').Trim().ToUpperInvariant()
$outer        = Get-Item -LiteralPath $Zip
$outerHash    = (Get-FileHash -LiteralPath $outer.FullName -Algorithm SHA256).Hash

Write-Host "Outer artifact : $($outer.Name) ($($outer.Length) bytes)"
Write-Host "SHA-256        : $outerHash"
Write-Host "Expected       : $expectedHash"

if ($outerHash -ne $expectedHash) {
	Write-Host 'FAIL: hash does not match. Do not install this file.' -ForegroundColor Red
	exit 1
}
Write-Host 'PASS: outer artifact matches the CI digest.' -ForegroundColor Green

$target = Join-Path $outer.DirectoryName ($outer.BaseName + '-extracted')
if (Test-Path -LiteralPath $target) {
	Write-Host "FAIL: $target already exists. Remove or rename it, then run again." -ForegroundColor Red
	exit 1
}
Expand-Archive -LiteralPath $outer.FullName -DestinationPath $target

$inner = @(Get-ChildItem -LiteralPath $target -Filter '*.zip' -File)
if ($inner.Count -ne 1) {
	Write-Host "FAIL: expected exactly one inner ZIP, found $($inner.Count)." -ForegroundColor Red
	exit 1
}

$innerHash = (Get-FileHash -LiteralPath $inner[0].FullName -Algorithm SHA256).Hash
Write-Host ''
Write-Host "Inner ZIP      : $($inner[0].FullName)"
Write-Host "Size           : $($inner[0].Length) bytes"
Write-Host "SHA-256        : $innerHash"
Write-Host ''
Write-Host 'Upload the INNER ZIP above in WordPress (Plugins > Add New > Upload Plugin).' -ForegroundColor Green
Write-Host 'Report both SHA-256 values so they can be recorded in PROJECT-HANDOFF.md.'
