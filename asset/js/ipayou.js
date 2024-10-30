jQuery( document ).ready( function( $ ) {
  $( '#ipayou-signature-enabled' ).change( function() {
    var data = {
      'action': 'ipayou_toggle_signature',
      'ipayou_signature_enabled': $( this ).is( ':checked' ) ? 1 : 0,
      'security': ipayou_params.security
    };

    $.post( ipayou_params.ajax_url, data );
  } );
} );
