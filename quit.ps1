if (Test-Path "logs\nginx.pid") {
    C:\Users\jitrs\Documents\opt\nginx-1.18.0\nginx.exe -c .\nginx.conf -s quit
    Write-Output "[] Server stopped"
} else {
    Write-Output "[] Server is not running"
}