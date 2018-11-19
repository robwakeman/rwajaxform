jQuery( document ).ready( function($) {

  "use strict";

  var elContactForm = $( "#rw-contact-form" );
  var elContactFormFeedback = $( "#rw-contact-form-feedback" );
  var elLoadingIconContainer = $( ".loading-icon-container" );
  var elLoadingIcon = $( ".loading-icon" );


  elContactForm.submit( function(event) {
      
    event.preventDefault(); // Prevent the default form submit.            
    
    // enable BS4 validation before processing the form
		// https://stackoverflow.com/questions/48769374/bootstrap-4-client-validation-before-ajax-submit
    if ( elContactForm[0].checkValidity() === false ) {
        event.stopPropagation();
    } else {

    	// serialize the form data
    	var ajaxFormData = elContactForm.serialize();

    	ajaxFormData += '&nonce_returned=' + settings.nonce;
    	
    	//add our own ajax check as X-Requested-With is not always reliable - I'm not using these as checks at present
    	ajaxFormData += '&ajaxrequest=true&submit=Submit+Form';
    	
      // $.ajax() method returns a $.Deferred() object
    	$.ajax({

  	    url:    settings.ajaxurl, // domain/wp-admin/admin-ajax.php
  	    type:   'post',                
  	    data:   ajaxFormData,
  	    beforeSend: function() {
	        elLoadingIcon.addClass( 'loading-spinner loading-icon-height m-5' );
	      }
    	
      }) // $.ajax()
    	
      // .done promise method returned by $.ajax method, triggered if the request succeeds
      // response is from the PHP action
    	.done( function(response) {
 
  	    // elContactFormFeedback.html( "<h2>Successful submission</h2>" + response.data );
  	    elContactFormFeedback.html( response.data );
        elContactForm.addClass( 'd-none' );
  	    elLoadingIconContainer.addClass( 'd-none' );
  	    elLoadingIcon.removeClass( 'loading-spinner loading-icon-height m-5' );
 
    	}) // $.done()
    	
    	// .fail promise method returned if the request fails
    	.fail( function() {
 
        elContactForm.addClass( 'd-none' );
        elLoadingIconContainer.addClass( 'd-none' );
        elLoadingIcon.removeClass( 'loading-spinner loading-icon-height m-5' );
        elContactFormFeedback.html( '<div class="alert alert-danger" role="alert"><p>Oops! Something went wrong.</p><p>This happens if wp_mail fn is misspelt or if the nonce is invalid and process_contact_form() dies</p></div>' );
 
    	})  // $.fail()
    	
    	// .always promise method occurs every time the call runs whether request succeeds or fails
    	.always( function() {
 
  	    event.target.reset();
 
    	}); // $.always()

    } // if (elContactForm[0].checkValidity() === false)

    elContactForm.addClass( 'was-validated' );

  }); // elContactForm.submit
        
}); // jQuery DOM ready


