jQuery(function($){
    var timer;
    $('#twikin-title').on('keyup change input', function(e){
        clearTimeout(timer);
        if($(e.currentTarget).val().length > 2) {
            timer = setTimeout(callApi, 1000);
        }
    });
    function callApi(){
        $.get(ajaxurl, {action: 'twikin-api', search: $('#twikin-title').val()}, function(data){
            if(data.error) {
                $('#twikin-api-result').text('Erreur : '+data.error);
            } else {
                if(data.results && data.results.length){
                    $('#twikin-api-result').html('<ol></ol>');
                    for(r in data.results){
                        var item;
                        item = '<li>';
                        item += '<a target="_blank" href="http://www.twikin.fr/jeux/'+data.results[r].id+'">';
                        item += '   <img src="'+data.results[r].media_url+'"/> ';
                        item +=     data.results[r].name;
                        item += '</a>';
                        item += '<button class="twikin-add-game" data-twikinid="'+data.results[r].id+'">Ajouter</button>';
                        if(data.results[r].wpid) item += ' <a href="/wp-admin/post.php?post='+data.results[r].wpid+'&action=edit">présent dans la médiathèque ('+data.results[r].wpid+')</a>';
                        item += '</li>';
                        
                        $('#twikin-api-result').append(item);
                    }
                } else {
                    $('#twikin-api-result').text('Aucun résultat');
                }
            }
        });
    }
    
    $('#twikin-api-result').on('click', '.twikin-add-game', function(e){
        $.post(ajaxurl, {action: 'twikin-add', gameid: $(e.currentTarget).attr('data-twikinid')});
        return false;
    });
});
