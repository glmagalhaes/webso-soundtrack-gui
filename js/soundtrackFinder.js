var resumoShow = false;

$(document).ready(function()
{
    
});

function getSoundtrack(movieName, movieYear)
{
    $.get("https://webso-tests-henriquelds.c9users.io/request",{ title: movieName, year: movieYear }, function(data){
        console.log(data);
        alert("Data: " + data + "\nStatus: " + status);
    });
    //alert("Faz a busca no webservice: title =" + movieName + " year =" + movieYear);
}
function showResumo()
{
    if(resumoShow)
    {
        $(".movie-resumo").hide();
        resumoShow = false;
    }else
    {
        $(".movie-resumo").show();
        resumoShow = true;
    }
}
function hideForm()
{
    $(".myform").fadeOut(100, function()
    {
        $('.procurando-msg').fadeIn(100).show();
    });
    
}