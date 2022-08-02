<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="vendor\twbs\bootstrap\dist\css\bootstrap.css">
    <link rel="stylesheet" href="style.css">
    <title>CRIAR EVENTO - YTB</title>
</head>
<body>
<form method="POST" action="ytb.php" enctype="multipart/form-data">
    <h3>Agendar lives com API YTB</h3>
    <div class="form-group">
        <label for="FormControlInput1">Titulo</label>
        <input type="text" class="form-control" id="FormControlInput1" name="titulo" required>
    </div>
    <div class="form-group">
        <label for="FormControlTextarea1">Descrição</label>
        <textarea class="form-control" id="FormControlTextarea1" rows="3" name="descricao" required></textarea>
    </div>
    <div class="col-md-5 mb-4 form-check-inline">
        <label for="FormControlSelect1">Data</label>
        <input type="date" id="validationCustom01" class="form-control" id="FormControlSelect1" name="data" min="<?php echo date('Y-m-d')?>" value="<?php echo date('Y-m-d')?>">
    </div>
    <div class="col-md-5 mb-4 form-check-inline">
        <label for="FormControlSelect2">Hora</label>
        <input type="time" id="validationCustom02" class="form-control" id="FormControlSelect2" name="hora" min="<?php echo date('H:i')?>" value="<?php echo date('H:i')?>">
    </div>
    <label class="col-md-5 mb-2">Privacidade</label>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="privacidade" id="Radios1" value="private" checked>
        <label class="form-check-label" for="Radios1">
            Privado
        </label>
        </div>
        <div class="form-check">
        <input class="form-check-input" type="radio" name="privacidade" id="Radios2" value="unlisted">
        <label class="form-check-label" for="Radios2">
            Não listado
        </label>
        </div>
        <div class="form-check">
        <input class="form-check-input" type="radio" name="privacidade" id="Radios3" value="public">
        <label class="form-check-label" for="Radios3">
            Público
        </label>
    </div>
    <br>
    <button class="btn btn-primary" type="submit">Criar</button>
</form>
</body>
</html>