$(window).scroll(function() {
  console.log($(document).scrollTop());
  if ($(document).scrollTop() > 50) {
    $('.header-title').addClass('fade');
    setTimeout(function() {
      $('.header-title').hide();
      $('.sticky-top').addClass('shrink');
    }, 500);
  } else {
    $('.sticky-top').removeClass('shrink');
    setTimeout(function() {
      $('.header-title').show();
      $('.header-title').removeClass('fade');
    }, 500);
  }
});
