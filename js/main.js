jQuery(document).ready(function (){
  multipleHeaderImages.init();
});

var multipleHeaderImages = function() {
  var el = {};
  var post_id = null;
  var ajaxurl = window.ajaxurl; // printed out by default by wp in admin area

  var init = function() {
    el.container = jQuery('#multiple-header-images');

    if(el.container.length < 1){
      return;
    }

    el.available = jQuery('#multiple-header-images-available');
    el.selected = jQuery('#multiple-header-images-selected');
    el.savebtn = jQuery('#mhi-save-images');
    el.feedback = el.savebtn.children('span');
    post_id = el.container.attr('data-post-id');

    loadImages();

    el.container.delegate('.mhi-image', 'click', function() {
      toggleCheckbox(jQuery(this));
    });

    el.savebtn.click( function(){
      saveImages();
      return false;
    });
  };

  var toggleCheckbox = function( $this ){
    var $this = $this.detach();
    $this.toggleClass("chosen");
    $input = $this.find('input');

    if ($input.is(':checked') ){
      $input.prop('checked', false);
      el.available.append( $this );
    } else {
      $input.prop('checked', true);
      el.selected.append( $this );
    }
  }

  var loadImages = function(){
    jQuery.post( ajaxurl,
      { action:"list_header_images", 'cookie': encodeURIComponent(document.cookie), 'post_id': post_id },
      function(response) {
        var template = jQuery('<li/>', {'class': 'mhi-image'}).append(
          jQuery('<img/>'),
          jQuery('<input/>', {
            'type': 'checkbox',
            'class': 'mhi-checkbox',
            'name': 'header-image'
          })
        );

        template.apply = function(value) {
          var item = template.clone();

          item.find('img').attr('src', value);
          item.find('input').val(value);

          return item;
        }

        jQuery.each(response.available, function(index, value) {
          el.available.append(template.apply(value));
        });

        jQuery.each(response.selected, function(index, value) {
          var item = template.apply(value);

          item.find('input').prop('checked', true);

          el.selected.append(item);
        });
      },
      'json'
    );
  }// END: loadImages

  var saveImages = function(){
    el.feedback
      .html(' - saving...')
      .css('opacity', '1');

    var checkedImages = [];

    // get a list of all checked images ( order dependent )
    $selectedImages = el.container.find('.mhi-image input:checked');

    $selectedImages.each(function(){
      checkedImages.push( jQuery(this).val() );
    });

    // save a json string of hearder images to postmeta
    jQuery.post(ajaxurl, {
        'action': "save_header_images",
        'cookie': encodeURIComponent(document.cookie),
        'post_id': post_id,
        'images': checkedImages
      },
      function(response) {
        setTimeout(function(){
          el.feedback
          .html(' - saved')
          .animate({
            opacity: 0
          }, 500);
        }, 400);
      },
      'json'
    );
  }

  return {
    init : init
  };
}();