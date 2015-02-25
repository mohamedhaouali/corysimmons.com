$(function() {

  $('form').on('submit', function(e) {
    e.preventDefault();

    var data = $(this).serialize();

    $.post('send.php', data, function() {
      $('.result').html('<p>Message sent successfully.</p>');
    });

  });

});
