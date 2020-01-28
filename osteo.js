//jQuery

//infos ostéopathie
$('#osteo p:eq(1)').hide();
$('#osteo p:eq(2)').hide();

$('#osteo img:first').click(function(){
    $('#osteo p:first').hide();
    $('#osteo p:eq(1)').fadeIn(2000, 'linear');
})

$('#osteo img:eq(1)').click(function(){
    $('#osteo p:eq(1)').hide();
    $('#osteo p:eq(2)').fadeIn(2000, 'linear');
})

$('#osteo img:eq(2)').click(function(){
    $('#osteo p:eq(2)').hide();
    $('#osteo p:first').fadeIn(2000, 'linear');
})


//cacher les li que l'utilisateur découvrira en cliquant dessus
$('.nourisson').hide();
$('.FemmeEnceinte').hide();
$('.LesSportifs').hide();
$('.bloc_pourTous').hide();


$('img a').css('cursor', 'pointer');
$('#pourQui img').css('cursor', 'pointer');
$('#osteo img').css('cursor', 'pointer');
$('.close').css('cursor', 'pointer');

$('h5:first, .nourissons img').click(function(){
    $('.nourisson').fadeIn(1000, 'linear');
    $('.col').hide();
    $('.FemmeEnceinte').hide();
    $('.LesSportifs').hide();
    $('.bloc_pourTous').hide();
    $('.nourissons img').css('border', '4px solid #192437');
    $('.enceintes img').css('border', 'none');
    $('.sportifs img').css('border', 'none');
    $('.pourTous img').css('border', 'none');
    $('#bodyPourQui').css('height', '400px');
});


$('h5:eq(1), .enceintes img').click(function(){
    $('.FemmeEnceinte').fadeIn(1000, 'linear');
    $('.col').hide();
    $('.nourisson').hide();
    $('.LesSportifs').hide();
    $('.bloc_pourTous').hide();
    $('.enceintes img').css('border', '4px solid #192437');
    $('.nourissons img').css('border', 'none');
    $('.sportifs img').css('border', 'none');
    $('.pourTous img').css('border', 'none');
    $('#bodyPourQui').css('height', '400px');
});


$('h5:eq(2), .sportifs img').click(function(){
    $('.LesSportifs').fadeIn(1000, 'linear');
    $('.col').hide();
    $('.nourisson').hide();
    $('.FemmeEnceinte').hide();
    $('.bloc_pourTous').hide();
    $('.sportifs img').css('border', '4px solid #192437');
    $('.nourissons img').css('border', 'none');
    $('.enceintes img').css('border', 'none');
    $('.pourTous img').css('border', 'none');
    $('#bodyPourQui').css('height', '400px');
});

$('h5:eq(3), .pourTous img').click(function(){
    $('.bloc_pourTous').fadeIn(1000, 'linear');
    $('.col').hide();
    $('.nourisson').hide();
    $('.FemmeEnceinte').hide();
    $('.LesSportifs').hide();
    $('.pourTous img').css('border', '4px solid #192437');
    $('.nourissons img').css('border', 'none');
    $('.enceintes img').css('border', 'none');
    $('.sportifs img').css('border', 'none');
    $('#bodyPourQui').css('height', '400px');
});

//ferme infos pourQui
$('.close').click(function(){
    $('.FemmeEnceinte').fadeOut(1000,'linear');
    $('.LesSportifs').fadeOut(1000,'linear');
    $('.nourisson').fadeOut(1000,'linear');
    $('.bloc_pourTous').fadeOut(1000,'linear');
    $('.nourissons img').css('border', 'none');
    $('.enceintes img').css('border', 'none');
    $('.pourTous img').css('border', 'none');
    $('.sportifs img').css('border', 'none');

});

//animation scroll











/*

$( "#osteo p" ).slideUp( 300 ).delay( 800 ).fadeIn( 400 );

$('#osteo p').replaceWith('hello');


$('#osteo p').click(function(){
    $('p').html("Hello <b>world</b>!");
});

$('html, body').animate({
    scrollTop: $(".middle").offset().top
 }, 2000);
 */