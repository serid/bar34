function MaybeCreateDir {
    param (
        $Name
    )

    if (Test-Path $Name -PathType "Leaf") {
        Write-Output "[] Cannot create dir `"${Name}`". File with this name exists"
        throw "Err"
    }

    if (Test-Path $Name -PathType "Container") {
        Write-Output "[] `"${Name}`" dir exists"
    } else {
        New-Item -Path "." -Name $Name -ItemType "directory"
        Write-Output "[] `"${Name}`" dir created"
    }
}

MaybeCreateDir -Name "logs"
MaybeCreateDir -Name "temp"

if (Test-Path "logs\nginx.pid") {
    Write-Output "[] Server is already running"
} else {
    Start-Process -FilePath "C:\Users\jitrs\Documents\opt\nginx-1.18.0\nginx.exe" -ArgumentList "-c .\nginx.conf"
    Write-Output "[] Server started"
}