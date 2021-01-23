if (Test-Path "logs") {
    Write-Output "[] `"logs`" dir exists"
} else {
    New-Item -Path "." -Name "logs" -ItemType "directory"
    Write-Output "[] `"logs`" dir created"
}

if (Test-Path "logs\nginx.pid") {
    Write-Output "[] Server is already running"
} else {
    Start-Process -FilePath "C:\Users\jitrs\Documents\opt\nginx-1.18.0\nginx.exe" -ArgumentList "-c .\nginx.conf"
    Write-Output "[] Server started"
}