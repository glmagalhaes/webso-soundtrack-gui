<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consulta Soundtrack de Filmes</title>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <link rel="stylesheet" href="css/style.css" type="text/css" />
    <script type="text/javascript" src="/js/soundtrackFinder.js"></script>
  </head>
  <body>
    <div class='main-img'>
    <img src="css/search.png"></img><br><br>
    </div>
  <div class='main-form'>
    <form class="myform" action="php/queryDbpedia.php" method="get">
      <input id="nameInput" type="text" name="searchName" placeholder="Digite o nome..."><br><br>
      <input type="radio" id="search-radio" name="searchParam" value="Diretor"/>Diretor
      <input type="radio" name="searchParam" value="Ator"/>Ator<br>
      <input type="checkbox" name="omdb" value='true'>Procurar no OMDb?<br><br>
      <input type="submit" onclick = 'hideForm();' class="procura-btn" value="Procurar">
    </form>
    <span class='procurando-msg'>Procurando...</span>
  </div>
  
  </body>
</html>
