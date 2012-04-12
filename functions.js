jQuery(document).ready(function (){
  
  multipleHeaderImages.init();

});


var multipleHeaderImages = function(){
  
  var el = {};
  var postid = null;
  var ajaxurl = window.ajaxurl; //printed out by default by wp in admin area
  var init = function(){
    el.container = jQuery('#multiple-header-images');
    el.available = jQuery('#multiple-header-images-available');
    el.selected = jQuery('#multiple-header-images-selected');
    el.savebtn = jQuery('#mhi-save-images');
    el.feedback = el.savebtn.children('span');
    el.launchbtn = jQuery('#multiple-header-btn');
    postid = el.container.attr('data-postid');
    
    
    el.launchbtn.click(function(){ loadImages(); });    
    el.container.delegate('.mhi-image', 'click', function() {
      toggleCheckbox( jQuery(this) );    
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
    if( $input.is(':checked') ){
      $input.prop('checked', false);
      el.available.append( $this );
    }else{
      $input.prop('checked', true);
      el.selected.append( $this );
    }
  }
  
  var loadImages = function(){
    jQuery.post( ajaxurl, 
      { action:"list_header_images", 'cookie': encodeURIComponent(document.cookie), 'postid': postid },
      function( response ){
        console.log( response.available );
        jQuery.each( response.available, function( index, value){
          el.available.append( value );
        });
        jQuery.each( response.selected, function( index, value){
          el.selected.append( value );
        });
        // now remove binded click event, this stops this event being fired again if user closes modal and opens it again without a page refresh
        el.launchbtn.unbind('click');
      },
      'json'
    );
  }// END: loadImages
  
  var saveImages = function(){
    el.feedback
      .html('- saving...')
      .css('opacity', '1');
    var checkedImages = [];
    //get a list of all checked images ( order dependent )
    $selectedImages = el.container.find('.mhi-image input:checked');
    
    $selectedImages.each(function(){
      checkedImages.push( jQuery(this).val() );
    });
    
    // save a json string of hreader images to postmeta
    jQuery.post( ajaxurl, 
      { action:"save_header_images", 'cookie': encodeURIComponent(document.cookie), 'postid':postid, 'images': checkedImages  },
      function( response ){
        setTimeout(function(){
          el.feedback
          .html('- saved')
          .animate({
            opacity: 0
          }, 500);
        }, 400);
      },
      'json'
    );
  }
  
  return{
    init : init
  };
  
}();

