var resumoShow = false;
var currentPlaying = null;
$(document).ready(function(){
    
    
});

function getSoundtrack(movieName, movieYear)
{
    $.get("https://webso-tests-henriquelds.c9users.io/request",{ title: movieName, year: movieYear }, function(data){
        console.log(data);
        //console.log('AQUI DATA');
        $('#sountrack-list').html('');
        $.each(data, function(index, track){
            $('#sountrack-list').append('<div class="track-item" data-toggle="collapse" data-target="#collapse'
            +index+'" data-parent="#sountrack-list" href="#collapse'
            +index+'" aria-expanded="true" aria-controls="collapse'
            +index+'" id="'+track.title+'"><span>'
            +capitalize(track.title)
            +'</span><span class="item-artist">'+track.performers+'</span></div>');
        });
        
        
    }).done(function(){
        initializeSpotfy();
    });
    
    //Volta para o topo da pagina
    //window.scrollTo(0, 0);
    //Atualiza info sobre qual filme é a soundtrack
    $(".showingFor").html("Exibindo soundtracks do filme: <b>" + movieName + "<b>");
    
    
}
function capitalize(s){
    return s.toLowerCase().replace( /\b./g, function(a){ return a.toUpperCase(); } );
};
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

function initializeSpotfy(){
    $('.track-item').click(function(e){
       e.stopPropagation();
       searchTrack(this);
    });
}

var fetchTracks = function (trackId, callback) {
    
    //Magia negra pra resolver um bug estranho
    if(currentPlaying === trackId)
    {
        return false;
    }
    currentPlaying = trackId;
    //Fim da magia negra
    
    
    $.ajax({
        url: 'https://api.spotify.com/v1/tracks/' + trackId,
        success: function (response) {
            callback(response);
        }
    });
};

function searchTrack($this) {
    if($($this).hasClass('listed')){
        $($this).children(".cover").toggle(100);
        return false;
    }
    /*id = ($this.id).split('|');
    track =  id[0]; //id é o nome da track
    artist = id[1];
    console.log(track);
    console.log(artist);
    query = 'track:'+track+'artist:'+artist;*/
    query = $this.id;
    href  = $($this).attr('href').replace('#','');
    $.ajax({
        url: 'https://api.spotify.com/v1/search',
        data: {
            q: query,
            type: 'track',
            limit: '1'
        },
        success: function (response) {
            try{
                var track = response.tracks;
                var img = track.items[0].album.images[0].url;
                var id = track.items[0].id;
                var link = track.items[0].external_urls.spotify //acho que assim tu pega o link da musica no spotify, seria interessante deixar esse link disponivel pro usuario junto com o player..
                $($this).addClass('listed');
                $($this).append('<div style="background-image:url('+img+');" id="'+href+'" data-track-id="'+id+'" class="cover track collapse" role="tabpanel"></div><div class="external-link"><a href="'+link+'" target="_blank">ouça no Spotfy</a></div>');
                $('#'+href).append("<div class='play-gfx'></div>");
                executar();
                console.log("Buscando e criando cover: " + id);
            }
            catch(err){
                $($this).append('<div class="not-found">Desculpe, música não encontrada...</div>');
                 $($this).addClass('listed');
            }
           
        }
    });
    
        
};

function executar(){
    $('.track').click(function(e){
        e.stopPropagation();
        var target = $(e.target);
        
            if (target !== null && target.hasClass('cover')) {
                if (target.hasClass('playing')) {
                    audioObject.pause();
                } else {
                    if (typeof audioObject != 'undefined') {
                        audioObject.pause();
                    }
                    fetchTracks(target.attr('data-track-id'), function (data) {
                        console.log(data);
                        audioObject = new Audio(data.preview_url);
                        audioObject.play();
                        
                        target.addClass('playing');
                        target.children(".play-gfx").remove();
                        target.append("<div class='pause-gfx'></div>");
                        
                        
                        audioObject.addEventListener('ended', function () {
                            currentPlaying = null;
                            target.removeClass('playing');
                            target.children(".pause-gfx").remove();
                            target.append("<div class='play-gfx'></div>");
                            
                        });
                        audioObject.addEventListener('pause', function () {
                            currentPlaying = null;
                            target.removeClass('playing');
                            target.children(".pause-gfx").remove();
                            target.append("<div class='play-gfx'></div>");
                            
                        });
                    });
                    }
                }
    });
    
}
