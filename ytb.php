<?php
if(isset($_POST['titulo']) && isset($_POST['descricao']) && isset($_POST['data']) && isset($_POST['hora']) && isset($_POST['privacidade'])){
  setcookie('titulo', $_POST['titulo']);
  setcookie('descricao', $_POST['descricao']);
  setcookie('data', $_POST['data']);
  setcookie('hora', $_POST['hora']);
  setcookie('privacidade', $_POST['privacidade']);
  $titulo = $_POST['titulo'];
  $descricao = $_POST['descricao'];
  $data = $_POST['data'];
  $hora = $_POST['hora'];
  $privacidade = $_POST['privacidade'];
}elseif(isset($_COOKIE['titulo']) && isset($_COOKIE['descricao']) && isset($_COOKIE['data']) && isset($_COOKIE['hora']) && isset($_COOKIE['privacidade'])){
  $titulo = $_COOKIE['titulo'];
  $descricao = $_COOKIE['descricao'];
  $data = $_COOKIE['data'];
  $hora = $_COOKIE['hora'];
  $privacidade = $_COOKIE['privacidade'];
  unset($_COOKIE['titulo']);
  unset($_COOKIE['descricao']);
  unset($_COOKIE['data']);
  unset($_COOKIE['hora']);
  unset($_COOKIE['private']);
}else{
  header("location:index.php");
}
/**
 * Requisitos da Biblioteca
 *
 * 1. Instale o compositor (https://getcomposer.org)
 * 2. Na linha de comando, mude para este diretório (api-samples/php)
 * 3. Exigir a biblioteca google/apiclient
 * $ compositor requer google/apiclient:~2.0
 */
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}

require_once __DIR__ . '/vendor/autoload.php'; 
session_start();

/*
 * Você pode adquirir um ID de cliente e um segredo do cliente OAuth 2.0 no
 * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
 * Para obter mais informações sobre como usar o OAuth 2.0 para acessar as APIs do Google, consulte:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Verifique se você ativou a API de dados do YouTube para seu projeto.
 */
$OAUTH2_CLIENT_ID = 'REPLACE_ME';
$OAUTH2_CLIENT_SECRET = 'REPLACE_ME';

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setScopes('https://www.googleapis.com/auth/youtube');
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
    FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);

// Defina um objeto que será usado para fazer todas as requisições da API.
$youtube = new Google_Service_YouTube($client);

// Verifica se existe um token de autenticação para os escopos necessários
$tokenSessionKey = 'token-' . $client->prepareScopes();
if (isset($_GET['code'])) {
  if (strval($_SESSION['state']) !== strval($_GET['state'])) {
    die('The session state did not match.');
  }

  $client->authenticate($_GET['code']);
  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
  header('Location: ' . $redirect);
}

/*if (isset($_SESSION[$tokenSessionKey])) {
  $client->setAccessToken($_SESSION[$tokenSessionKey]);
}*/

// Verifique se o token de acesso foi adquirido com sucesso.
if ($client->getAccessToken()) {
  try {
    // Cria um objeto para o snippet do recurso liveBroadcast. Especificar valores
    // para o título do snippet, horário de início programado e horário de término programado.
    $broadcastSnippet = new Google_Service_YouTube_LiveBroadcastSnippet();
    $broadcastSnippet->setTitle($titulo);
    $broadcastSnippet->setDescription($descricao);
    $broadcastSnippet->setScheduledStartTime(str_replace("/", "-", $data).'T'.$hora.'-03:00');

    // Cria um objeto para o status do recurso liveBroadcast e define o
    // status da transmissão.
    $status = new Google_Service_YouTube_LiveBroadcastStatus();
    $status->setPrivacyStatus($privacidade);

    
    // Cria a solicitação de API que insere o recurso liveBroadcast.
    $broadcastInsert = new Google_Service_YouTube_LiveBroadcast();
    $broadcastInsert->setSnippet($broadcastSnippet);
    $broadcastInsert->setStatus($status);
    $broadcastInsert->setKind('youtube#liveBroadcast');

    // Executa a requisição e retorna um objeto que contém informações
    // sobre a nova transmissão.
    $broadcastsResponse = $youtube->liveBroadcasts->insert('snippet,status',
        $broadcastInsert, array());


    // Cria um objeto para o snippet do recurso liveStream. Especifique um valor
    // para o título do snippet.
    $streamSnippet = new Google_Service_YouTube_LiveStreamSnippet();
    $streamSnippet->setTitle('New Stream');

    // Cria um objeto para detalhes da rede de distribuição de conteúdo para o live
    // stream e especifique o formato do stream e o tipo de ingestão.
    $cdn = new Google_Service_YouTube_CdnSettings();
    $cdn->setResolution("1080p");
    $cdn->setFrameRate("60fps");
    $cdn->setIngestionType('rtmp');

    // Cria a solicitação de API que insere o recurso liveStream.
    $streamInsert = new Google_Service_YouTube_LiveStream();
    $streamInsert->setSnippet($streamSnippet);
    $streamInsert->setCdn($cdn);
    $streamInsert->setKind('youtube#liveStream');

    // Executa a requisição e retorna um objeto que contém informações
    // sobre o novo fluxo.
    $streamsResponse = $youtube->liveStreams->insert('snippet,cdn',
        $streamInsert, array());

    
    // Vincula a transmissão à transmissão ao vivo.
    $bindBroadcastResponse = $youtube->liveBroadcasts->bind(
        $broadcastsResponse['id'],'id,contentDetails',
        array(
            'streamId' => $streamsResponse['id'],
        ));
    header('location:https://youtu.be/'.$bindBroadcastResponse['id']);
    $htmlBody = "<h3>Added Broadcast</h3><ul>";
    $htmlBody .= sprintf('<li>%s published at %s (%s)</li>',
        $broadcastsResponse['snippet']['title'],
        $broadcastsResponse['snippet']['publishedAt'],
        $broadcastsResponse['id']);
    $htmlBody .= '</ul>';

    $htmlBody .= "<h3>Added Stream</h3><ul>";
    $htmlBody .= sprintf('<li>%s (%s)</li>',
        $streamsResponse['snippet']['title'],
        $streamsResponse['id']);
    $htmlBody .= '</ul>';

    $htmlBody .= "<h3>Bound Broadcast</h3><ul>";
    $htmlBody .= sprintf('<li>Broadcast (%s) was bound to stream (%s).</li>',
        $bindBroadcastResponse['id'],
        $bindBroadcastResponse['contentDetails']['boundStreamId']);
    $htmlBody .= '</ul>';

  } catch (Google_Service_Exception $e) {
    $htmlBody = sprintf('<p>A service error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
    $htmlBody = sprintf('<p>An client error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  }

  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
} elseif ($OAUTH2_CLIENT_ID == 'REPLACE_ME') {
  $htmlBody = <<<END
  <h3>Client Credentials Required</h3>
  <p>
    You need to set <code>\$OAUTH2_CLIENT_ID</code> and
    <code>\$OAUTH2_CLIENT_ID</code> before proceeding.
  <p>
END;
} else {
  // Se o usuário não autorizou o aplicativo, inicie o fluxo OAuth
  $state = mt_rand();
  $client->setState($state);
  $_SESSION['state'] = $state;

  $authUrl = $client->createAuthUrl();
  header('location:'.$authUrl);
  $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
}
?>