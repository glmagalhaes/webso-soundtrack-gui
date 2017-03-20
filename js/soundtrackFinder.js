var resumoShow = false;


function getSoundtrack(movieName, movieYear)
{
    $.get("https://webso-tests-henriquelds.c9users.io/request",{ title: movieName, year: movieYear }, function(data){
        //console.log(data);
        //console.log('AQUI DATA');
        $('#sountrack-list').html('');
        $.each(data, function(index, track){
            $('#sountrack-list').append('<div class="track-item" data-toggle="collapse" data-target="#collapse'+index+'" data-parent="#sountrack-list" href="#collapse'+index+'" aria-expanded="true" aria-controls="collapse'+index+'" id="'+track.title+'">'+track.title+'</div>');
        });
    }).done(function(){
        initializeSpotfy();
    });
    
    //Volta para o topo da pagina
    window.scrollTo(0, 0);
    //Atualiza info sobre qual filme é a soundtrack
    $(".showingFor").html("Exibindo soundtracks do filme: <b>" + movieName + "<b>");
    
    
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

function initializeSpotfy(){
    $('.track-item').click(function(){
       searchTrack(this);
    });
}

var fetchTracks = function (trackId, callback) {
    $.ajax({
        url: 'https://api.spotify.com/v1/tracks/' + trackId,
        success: function (response) {
            callback(response);
        }
    });
};

function searchTrack($this) {
    if($($this).hasClass('listed')){
        return false;
    }
    query =  $this.id;
    href  = $($this).attr('href').replace('#','');
    $.ajax({
        url: 'https://api.spotify.com/v1/search',
        data: {
            q: query,
            type: 'track',
            limit: '1'
        },
        success: function (response) {
            var track = response.tracks;
            var img = track.items[0].album.images[0].url;
            var id = track.items[0].id;
            $($this).addClass('listed');
            $('<div style="background-image:url('+img+')" id="'+href+'" data-track-id="'+id+'"   class="cover track collapse" role="tabpanel"></div>').insertAfter($this);
            executar();
           
        }
    });
        
};


function executar(){
    $('.track').click(function(e){
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
                        audioObject.addEventListener('ended', function () {
                            target.removeClass('playing');
                        });
                        audioObject.addEventListener('pause', function () {
                            target.removeClass('playing');
                        });
                    });
                    }
                }
    })
    
}
