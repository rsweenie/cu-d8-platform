```json
{
    // Use IntelliSense to learn about possible attributes.
    // Hover to view descriptions of existing attributes.
    // For more information, visit: https://go.microsoft.com/fwlink/?linkid=830387
    // and https://www.drupal.org/docs/develop/development-tools/configuring-visual-studio-code section title
    // Configuring XDebug
    // https://marketplace.visualstudio.com/items?itemName=felixfbecker.php-debug
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for XDebug",
            "type": "php",
            "request": "launch",
            // https://www.drupal.org/docs/develop/development-tools/configuring-visual-studio-code
            "pathMappings": {
                "/var/www/creighton": "${workspaceRoot}"
            },
            "port": 9000
        },
        {
            "name": "Launch currently open script",
            "type": "php",
            "request": "launch",
            "program": "${file}",
            "cwd": "${fileDirname}",
            "port": 9000
        }
    ]
}
```