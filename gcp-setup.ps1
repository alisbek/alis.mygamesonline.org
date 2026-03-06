$env:Path = [System.Environment]::GetEnvironmentVariable('Path','Machine') + ';' + [System.Environment]::GetEnvironmentVariable('Path','User')

$startupScript = @'
#!/bin/bash
apt-get update
apt-get install -y apache2 php php-mysql php-curl php-mbstring php-xml php-intl mariadb-server certbot python3-certbot-apache unzip
systemctl enable apache2
systemctl enable mariadb
systemctl start apache2
systemctl start mariadb
'@

$scriptPath = Join-Path $env:TEMP 'gcp-startup.sh'
Set-Content -Path $scriptPath -Value $startupScript -NoNewline

$args = @(
    'compute', 'instances', 'create', 'feltee-web',
    '--project=feltee-store',
    '--zone=us-east1-b',
    '--machine-type=e2-micro',
    '--image-family=debian-12',
    '--image-project=debian-cloud',
    '--boot-disk-size=30GB',
    '--boot-disk-type=pd-standard',
    '--tags=http-server,https-server',
    "--metadata-from-file=startup-script=$scriptPath"
)

& gcloud @args
