# youtube_api_agendar_eventos

Agende transmissões no youtube sem abrir o Youtube Studio, ultilizando o formulario do "index.php" ou simplesmente enviando os dados via POST para o arquivo "ytb.php"

Antes de começar adicione o bootstrap e o Google Api Client:
composer require twbs/bootstrap:5.2.0
composer require google/apiclient:~2.0

Crie as suas credenciais no https://console.cloud.google.com/ e altere as variaveis do aquivo ytb.php:
$OAUTH2_CLIENT_ID = 'REPLACE_ME';
$OAUTH2_CLIENT_SECRET = 'REPLACE_ME';
