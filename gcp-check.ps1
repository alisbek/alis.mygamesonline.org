$env:Path = [System.Environment]::GetEnvironmentVariable('Path','Machine') + ';' + [System.Environment]::GetEnvironmentVariable('Path','User')

echo 'y' | gcloud compute ssh feltee-web --zone=us-east1-b --project=feltee-store --quiet -- "bash -c 'systemctl is-active apache2; systemctl is-active mariadb; php -v | head -1; php -m | grep -iE curl\|mysql\|mbstring\|xml\|intl | tr \\n \\  ; echo; df -h / | tail -1; free -h | grep Mem'"
