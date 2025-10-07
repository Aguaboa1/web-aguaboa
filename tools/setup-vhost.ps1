<#
Setup script para Windows/XAMPP (rodar como Administrador)
O script fará:
 - Backup dos arquivos modificados
 - Adicionar entrada '127.0.0.1 web-aguaboa.local' no hosts (se não existir)
 - Descomentar LoadModule mod_rewrite no httpd.conf (se estiver comentado)
 - Ajustar AllowOverride para All no <Directory "C:/xampp/htdocs"> (se necessário)
 - Anexar o conteúdo de tools/vhost-example.conf em httpd-vhosts.conf se não houver vhost web-aguaboa.local
 - Reiniciar o serviço Apache2.4 (se possível)

Use com cuidado e revise antes de executar. Execute no PowerShell como Administrador.
#>

# Verifica se está como Administrador
if (-not ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Error "Este script precisa ser executado como Administrador. Pare e execute o PowerShell como Administrador."
    exit 1
}

$projectRoot = 'C:\xampp\htdocs\gestao-aguaboa-php'
$toolsDir = Join-Path $projectRoot 'tools'
$vhostExample = Join-Path $toolsDir 'vhost-example.conf'
$apacheConfDir = 'C:\xampp\apache\conf'
$apacheExtra = Join-Path $apacheConfDir 'extra'
$httpdConf = Join-Path $apacheConfDir 'httpd.conf'
$httpdVhosts = Join-Path $apacheExtra 'httpd-vhosts.conf'
$hostsFile = Join-Path $env:SystemRoot 'System32\drivers\etc\hosts'

# Backup function
function Backup-IfExists($path) {
    if (Test-Path $path) {
        $bak = "${path}.bak_$(Get-Date -Format 'yyyyMMddHHmmss')"
        Copy-Item -Path $path -Destination $bak -Force
        Write-Host "Backup criado: $bak"
    }
}

# 1) Backup
Backup-IfExists -path $hostsFile
Backup-IfExists -path $httpdConf
Backup-IfExists -path $httpdVhosts

# 2) Atualizar hosts
$hostEntry = '127.0.0.1 web-aguaboa.local'
$hostsContent = Get-Content -Path $hostsFile -ErrorAction Stop
if ($hostsContent -notcontains $hostEntry -and -not ($hostsContent -match 'web-aguaboa\.local')) {
    Add-Content -Path $hostsFile -Value "`n$hostEntry"
    Write-Host "Entrada adicionada ao hosts: $hostEntry"
} else {
    Write-Host "Hosts já contém entrada para web-aguaboa.local"
}

# 3) Ajustar httpd.conf (mod_rewrite e AllowOverride All)
if (-not (Test-Path $httpdConf)) {
    Write-Warning "Arquivo httpd.conf não encontrado em: $httpdConf. Verifique sua instalação do XAMPP."
} else {
    $httpdText = Get-Content -Raw -Path $httpdConf -ErrorAction Stop

    # Descomentar mod_rewrite
    if ($httpdText -match '#\s*LoadModule\s+rewrite_module\s+modules/mod_rewrite\.so') {
        $httpdText = $httpdText -replace '#\s*(LoadModule\s+rewrite_module\s+modules/mod_rewrite\.so)', '$1'
        Write-Host 'Descomentada a linha do mod_rewrite.'
    } else {
        Write-Host 'Linha mod_rewrite já está descomentada (ou não foi encontrada).'
    }

    # Ajustar AllowOverride dentro do bloco <Directory "C:/xampp/htdocs">
    if ($httpdText -match '<Directory "C:/xampp/htdocs">[\s\S]*?AllowOverride\s+None') {
        $httpdText = $httpdText -replace '(<Directory "C:/xampp/htdocs">[\s\S]*?AllowOverride\s+)None', '$1All'
        Write-Host 'AllowOverride ajustado para All no bloco C:/xampp/htdocs.'
    } else {
        Write-Host 'AllowOverride já está configurado como All ou o bloco não foi encontrado.'
    }

    # Gravar as mudanças
    Set-Content -Path $httpdConf -Value $httpdText -Force
    Write-Host 'httpd.conf atualizado.'
}

# 4) Anexar vhost se necessário
if (-not (Test-Path $vhostExample)) {
    Write-Warning "Arquivo de exemplo de vhost não encontrado em: $vhostExample. Verifique tools/vhost-example.conf"
} elseif (-not (Test-Path $httpdVhosts)) {
    # Se o httpd-vhosts.conf não existir, cria um novo com o conteúdo
    Copy-Item -Path $vhostExample -Destination $httpdVhosts -Force
    Write-Host "Criado $httpdVhosts a partir do exemplo."
} else {
    $vhostsContent = Get-Content -Path $httpdVhosts -ErrorAction Stop
    if ($vhostsContent -notmatch 'web-aguaboa\.local') {
        Get-Content -Path $vhostExample | Add-Content -Path $httpdVhosts
        Write-Host 'VHost web-aguaboa.local anexado em httpd-vhosts.conf.'
    } else {
        Write-Host 'httpd-vhosts.conf já contém web-aguaboa.local. Nenhuma ação tomada.'
    }
}

# 5) Reiniciar serviço Apache
try {
    Restart-Service -Name 'Apache2.4' -Force -ErrorAction Stop
    Write-Host 'Serviço Apache2.4 reiniciado com sucesso.'
} catch {
    Write-Warning "Não foi possível reiniciar o serviço Apache2.4 automaticamente: $($_.Exception.Message)"
    Write-Host 'Por favor reinicie o Apache via XAMPP Control Panel.'
}

Write-Host 'Concluído. Agora abra http://web-aguaboa.local/departments (ou http://localhost/gestao-aguaboa-php/public/departments se não usar o VirtualHost).'
