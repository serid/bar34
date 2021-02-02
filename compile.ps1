# This script copies all the static files excluding *.rese* files which are not needed in deployed website

# Invokes $Command on every file found by Get-ChildItem @Args
function Invoke-OnTree {
    param (
        [ScriptBlock]$ScriptBlock
    )

    (Get-ChildItem @Args -Recurse).FullName | ForEach-Object -Process $ScriptBlock
}


function Solution1 {
    New-Item build -ItemType "Directory" -ea:Ignore

    Invoke-OnTree -Path .\static -Exclude *.rese* -ScriptBlock {
        $relPath = Resolve-Path $_ -Relative
        Write-Host "Copying file $relPath"
        Copy-Item $relPath -Destination ".\build\$relPath"
    }
}

function Solution2 {
    Remove-Item .\build -Recurse -Confirm:$false
    Copy-Item -Exclude *.rese* -Path .\static -Destination .\build -Container -Recurse
}

Solution2