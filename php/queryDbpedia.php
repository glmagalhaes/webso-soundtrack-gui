<?php
//                      .---.     .---.
//                     ( -o- )---( -o- )
//                     ;-...-`   `-...-;
//                    /                 \
//                   /                   \
//                  | /_               _\ |
//                  \`'.`'"--.....--"'`.'`/
//                   \  '.   `._.`   .'  /
//                _.-''.  `-.,___,.-`  .''-._
//               `--._  `'-._______.-'`  _.--`
//                 /                 \
//                   /.-'`\   .'.   /`'-.\
//                  `      '.'   '.'
//                      KERMIT ANTI BUG!
//
//Mensagens de erro
define ("ERRO_ANO", "Ano não encontrado");

// Esconder warnings e erros
error_reporting(0);

//Permiti abrir url, só pra garantir
ini_set ("allow_url_fopen", 1);

//Biblioteca SPARQL
require_once('sparqllib.php');

// Nome digitado no html form
$search = $_GET['searchName'];

//Letras maiusculas no primeiro char
$search = ucwords($search);

// Pra fazer a busca na dbpedia o nome do diretor
// tem que ta separado por underscore
$search_underscore = preg_replace('/\s+/', '_', $search);



// Lista de filmes do diretor
global $lista_de_filmes, $lista_de_desc;
$lista_de_filmes = array();
$lista_de_desc = array();


//Inicio header
echo "<div class='header-info'>";
echo "<a href='/index.php'><img src='/css/back.png'></a>";
//Mensagem de cabeçalho
echo "<span class='buscando-info'>Buscando por <span class='dir-name'>" . $search . "</span></span>";

//Conecta na dbpedia
$db = sparql_connect('http://dbpedia.org/sparql');


//Query SPARQL - BUSCA POR DIRETOR
$queryDirector = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX dbpedia-owl: <http://dbpedia.org/ontology/>
PREFIX dbpprop: <http://dbpedia.org/property/>
PREFIX dbres: <http://dbpedia.org/resource/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>

select distinct ?filmName ?filmDesc where
{
 ?film dbpedia-owl:director ?dir .
 ?dir rdfs:label ?dirName .
 ?film rdfs:label ?filmName.
 ?film dbo:abstract ?filmDesc.

FILTER (lang(?filmName) = 'en') .
FILTER (lang(?filmDesc) = 'en') .
FILTER (regex(?dirName,'" .$search."','i'))

} ORDER BY ?filmName";



//--------------------------------------------------------------------------------
// TODO!!
//QUERY DA BUSCA POR ATOR
$queryActor = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX dbpedia-owl: <http://dbpedia.org/ontology/>
PREFIX dbpprop: <http://dbpedia.org/property/>
PREFIX dbres: <http://dbpedia.org/resource/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>

select distinct ?filmName ?filmDesc where
{
 ?film dbpedia-owl:starring ?dir .
 ?dir rdfs:label ?dirName .
 ?film rdfs:label ?filmName.
 ?film dbo:abstract ?filmDesc.

FILTER (lang(?filmName) = 'en') .
FILTER (lang(?filmDesc) = 'en') .
FILTER (regex(?dirName,'" .$search."','i'))

} ORDER BY ?filmName";
//--------------------------------------------------------------------------------




// Qual pesquisa quis fazer Ator ou Diretor
$whatToSearch = $_GET['searchParam'];

if($whatToSearch == "Ator")
{
  
 $result = sparql_query($queryActor); // Resultado da query busca por Ator
}else{
  
  $result = sparql_query($queryDirector); // Resultado da query busca por Diretor
}


// Erro na query
if( !$result ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }

//Pega os campos dos resultados
$fields = sparql_field_array( $result );

//Número de resutados encontrados na Dbpedia
echo "<span class='buscando-info'> <span class='result-num'>".sparql_num_rows( $result )."</span> Resultados</span>";
// Fim header
echo "</div>";

// Armazena os resultados
while( $row = sparql_fetch_array( $result ) )
{
	foreach( $fields as $field )
	{
    // Salva na lista de filmes e descrições dos filmes
    array_push($lista_de_filmes, $row['filmName']);
    array_push($lista_de_desc, $row['filmDesc']);
	}
}

//AQUI GERA O CÓDIGO HTML
function generateMovieList()
{
  
  //DEVE PROCURAR NO OMDB?
  $searchOmdb = $_GET['omdb'];
  
  $lista = $GLOBALS['lista_de_filmes'];
  
  //Gambiarra whatever, melhor não mexer nisso
  $last_movie = "";
  
  //Index do foreach, pra pegar as descrições (Obs.: o index é de 2 em 2)
  $index = 0;
  foreach ($lista as $movie) {

    //Garante que não vai printar mais de uma vez o mesmo filme (aka 'Gambiarra')
    if(strcmp($last_movie, $movie) != 0)
    {
      // Remove algumas ocorrencias de " (film)" e "film" que aparece listado na dbpedia
      // Pode dar alguns erros se o filme realmente for "film" ou algo assim.
      $movie_reduc = str_replace("film", "", $movie);
      //Remove qualquer coisa entre parenteses Ex: '(2002 film)'
      $movie_reduc = preg_replace("/\(([^()]*+|(?R))*\)/", "", $movie_reduc);

      $year_error_holder = "";
      
      // Essa função é mais "precisa" tenta pegar exatamento o ano depois
      // de "Movie Name is a"
      $year = getMovieYearFromAbstract($index, $movie_reduc);
      $year_error_holder = $year;
      
      //Deixa só os numeros
      $year = preg_replace("/[^0-9]/","",$year);
      
      //Se não encontrou o ano tenta pegar o primeiro numero que aparece na primeira frase
      if(!is_numeric($year) || strlen($year) != 4)
      {
        $year = forceMovieYear($index, $movie_reduc);
        $year_error_holder = $year;
        //Deixa só os números
        $year = preg_replace("/[^0-9]/","",$year);
        //Pega apenas os 4 primeiros caracteres
        $year = substr($year, 0, 4);

        //Se ainda assim não achou vai procurar OMDb
        if(!is_numeric($year) || strlen($year) != 4 || $year < 1906 || $year > 2020)
        {
          if($searchOmdb == 'true')
            $year = getInfoFromOMDb($movie_reduc);
          else
            $year = "Erro";
        }
      }
      
      //! GERAÇÃO HTML LOGO ABAIXO !
      //Essa var quando quiser exibir ou nao a sinopse do filme
      //mais usada pra debuggar
      $show_desc = true;
      $desc = getMovieDescriptionFromAbstract($index);
      
      //Adiciona slashs pra passar a string certa pra função JS
      $movie_slash = addslashes($movie_reduc);
      
      //Gera a div com o nome e ano do filme
      echo "<div class='item-movie' onClick=\"getSoundtrack('".$movie_slash."','".$year."');\"><span>".$movie_reduc."&nbsp;| </span><span>&nbsp;".$year."</span></div>";
      
      if($show_desc)
      {
        echo "<div class='movie-resumo'>" . $desc . "</div>";
      }
        


    }
    $last_movie = $movie;
    $index++;

  }

}

//Funções para pegar as informações dos filmes
function forceMovieYear($code)
{
  $descs = $GLOBALS['lista_de_desc'];
  $full_desc = $descs[$code];
  $year = "";
  $max = strlen($full_desc) - 1;
  
  // Offset é 100% do tamanho do texto
  // pode ser feito otimizações de acordo com o tamanho do abstract
  $offset = (int) $max *= 1;
  
  for($i=0; $i < $offset; $i++)
  {
    $year .= $full_desc[$i];
  }

  return $year;
}
function getMovieDescriptionFromAbstract($code)
{
  $descs = $GLOBALS['lista_de_desc'];
  $full_desc = $descs[$code];

  return $full_desc;
}
function getMovieYearFromAbstract($code, $movieNameReduced)
{
//   $descs = $GLOBALS['lista_de_desc'];
//   $full_desc = $descs[$code];
//   $initial_pos = strlen($movieNameReduced) + 5;
//   $year = "";
//   for($i=0; $i < 5; $i++)
//   {
//     $year .= $full_desc[$initial_pos + $i];
//   }

  $found_isa = "is a";
  $search_desc = $GLOBALS['lista_de_desc'][$code];
  $year ="";
  
  $pos_isa = strpos($search_desc, $found_isa);
  
  if($pos_isa === false){
      return $year;
  }
  else{
      
      for($i=0; $i < 5; $i++)
      {
         $year .= $search_desc[$pos_isa + 5 + $i];
      }
      
  }
  
  return $year;
}
function getInfoFromOMDb($movieName)
{
  //Ajusta o nome do filme de 'Filme Name' pra 'Filme+Name'
  $omdb_search_string = preg_replace('/\s+/', '+', $movieName);

  $omdb_search_url = 'http://www.omdbapi.com/?t='. $omdb_search_string . '&y=&plot=short&r=json';

    // Tenta fazer a request no OMDb
   try {

     $json_info = file_get_contents($omdb_search_url);

   } catch (Exception $e) {

     $year = "Not found";
     return $year;
   }

   //Decodifica o json e verifica se a propriedade existe
   $obj = json_decode($json_info);
   if(property_exists($obj, 'Year'))
   {
     $id = $obj->imdbID;
     $year = $obj->Year;
   }
   else {
     $year .= " Erro[".ERRO_ANO."]";
     return $year;
   }

   //Caso tudo de certo retrona o ano aqui
   return $year;
 }


 ?>
 
 
 <!DOCTYPE html>
 <html>
   <head>
     <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
     <link rel="stylesheet" href="/css/style.css">
     <script type="text/javascript" src="/js/soundtrackFinder.js"></script>
     <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" charset="utf-8"></script>
     <title>Resultado da Consulta</title>
   </head>
   <body>
    <div class='actions'>
      <button type="button" onclick="showResumo();" class='action-btn'>Mostrar Resumo</button>
    </div>
     <div class="container-fluid main-page">
       <div class="row">
         <div class="col-md-6 resultados">
           <div>
             <ul class="list-group">
               <?php generateMovieList(); ?>
             </ul>
           </div>
         </div>
         <div class="col-md-6 soundtrack-results">
            <span class = 'soundtracks-header'>Soundtracks</span><br>
            <span class = 'showingFor'>Nenhuma soundtrack para mostrar</span>
            <div id="sountrack-list" role="tablist" class="col-xs-12 panel-group">
                
            </div>
         </div>
       </div>
     </div>


   </body>
 </html>
