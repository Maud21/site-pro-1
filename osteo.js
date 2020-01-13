//cacher les li que l'utilisateur découvrira en cliquant dessus
$('.nourisson').hide();
$('.FemmeEnceinte').hide();
$('.LesSportifs').hide();
$('.bloc_pourTous').hide();


$('h5, img').css('cursor', 'pointer');

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
    $('.FemmeEnceinte').show();
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
    $('.LesSportifs').show();
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
    $('.bloc_pourTous').show();
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



$('html, body').animate({
    scrollTop: $(".middle").offset().top
 }, 2000);